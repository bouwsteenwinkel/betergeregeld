<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CaptureAdAttribution;
use App\Mail\VoorbeeldAanvraagIntern;
use App\Mail\VoorbeeldAanvraagKlant;
use App\Models\Channel\Site;
use App\Models\WebsiteLead;
use App\Services\ChannelSites\PreviewSiteGenerator;
use App\Support\ChannelSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * De "voorbeeld in 60 seconden"-tool. Bezoeker vult bedrijfsnaam, type bedrijf,
 * primaire kleur en doel in; wij zetten synchroon een herkleurde voorbeeldsite
 * neer en sturen de bezoeker door naar de preview op /_site/{key}.
 */
class PreviewToolController extends Controller
{
    private function site(): ChannelSite
    {
        return app(ChannelSite::class);
    }

    /**
     * Draait dit kanaal op aanvraag in plaats van op de generator?
     *
     * ?tool=1 zet de generator alsnog aan. Dat is er voor onszelf: we maken met
     * die tool een eerste opzet vóór we de klant bellen, en zonder achterdeur
     * zouden we daarvoor een config-wijziging moeten deployen.
     */
    private function aanvraagModus(): bool
    {
        if (request()->boolean('tool')) {
            return false;
        }

        return in_array($this->site()->key, (array) config('voorbeeld_aanvraag.kanalen', []), true);
    }

    /** De intake-pagina met het formulier + laadscherm. */
    public function form(): View
    {
        if ($this->aanvraagModus()) {
            return view('channels.voorbeeld-aanvragen', [
                'site'      => $this->site(),
                'levertijd' => (string) config('voorbeeld_aanvraag.levertijd', 'binnen 1 werkdag'),
            ]);
        }

        return view('channels.voorbeeld-maken', [
            'site'   => $this->site(),
            'goals'  => PreviewSiteGenerator::GOALS,
            'sferen' => PreviewSiteGenerator::SFEREN,
        ]);
    }

    /**
     * Aanvraag van een voorbeeldsite: leg de lead vast en beloof een voorbeeld
     * binnen een werkdag.
     *
     * De vragen hieronder zijn geen formaliteit — met bedrijfsnaam, vak, plaats,
     * doel en uitstraling kunnen we echt beginnen. We bellen daarna nog kort om
     * de details op te halen die een formulier nu eenmaal niet vangt, en dat
     * zeggen we er op de bevestiging ook bij: een belofte die je niet waarmaakt
     * kost meer dan hij oplevert.
     */
    public function aanvraagOpslaan(Request $request): RedirectResponse
    {
        // Honeypot, zelfde veldnaam als de tool.
        if (filled($request->input('website'))) {
            return redirect()->to($this->site()->url('voorbeeld-aangevraagd'));
        }

        $data = $request->validate([
            'company'       => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:120'],
            'place'         => ['required', 'string', 'max:80'],
            'contact_name'  => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:190'],
            'phone'         => ['required', 'string', 'max:60'],
            'goal'          => ['nullable', 'string', 'max:60'],
            'sfeer'         => ['nullable', 'string', 'max:60'],
            'current_site'  => ['nullable', 'string', 'max:190'],
            'usp'           => ['nullable', 'string', 'max:400'],
        ], [], [
            'company' => 'bedrijfsnaam', 'business_type' => 'wat je doet', 'place' => 'plaats',
            'contact_name' => 'naam', 'email' => 'e-mailadres', 'phone' => 'telefoonnummer',
        ]);

        try {
            $this->aanvraagLead($request, $data);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->to($this->site()->url('voorbeeld-aangevraagd'));
    }

    /** De bevestiging: wat er nu gebeurt, en wanneer. */
    public function aanvraagBedankt(): View
    {
        return view('channels.voorbeeld-aangevraagd', [
            'site'      => $this->site(),
            'levertijd' => (string) config('voorbeeld_aanvraag.levertijd', 'binnen 1 werkdag'),
        ]);
    }

    /** Vastleggen + intern melden. Zelfde dedup-regel als de tool: één e-mail = één lead. */
    private function aanvraagLead(Request $request, array $data): void
    {
        $site = $this->site();

        $answers = array_filter([
            'type_bedrijf' => $data['business_type'] ?? null,
            'plaats'       => $data['place'] ?? null,
            'doel'         => $data['goal'] ?? null,
            'sfeer'        => $data['sfeer'] ?? null,
            'usp'          => $data['usp'] ?? null,
        ], fn ($v) => filled($v));

        $lead = WebsiteLead::firstOrNew(['email' => mb_strtolower(trim($data['email']))]);
        $lead->fill([
            'contact_name'    => $lead->contact_name ?: $data['contact_name'],
            'phone'           => $lead->phone ?: $data['phone'],
            'company'         => $lead->company ?: ($data['company'] ?? null),
            'city'            => $lead->city ?: ($data['place'] ?? null),
            'branche'         => $lead->branche ?: $site->branche(),
            'channel'         => $lead->channel ?: $site->key,
            'source'          => $lead->source ?: 'voorbeeld_aanvraag',
            'current_website' => $lead->current_website ?: ($data['current_site'] ?? null),
            'answers'         => array_merge((array) $lead->answers, $answers) ?: null,
            // Geen generator, dus geen preview-status: dit wacht op een mens.
            'preview_status'  => $lead->preview_status ?: 'aangevraagd',
        ]);
        $lead->status = $lead->status ?: 'new';
        $lead->fillAttributionOnce(CaptureAdAttribution::fromRequest($request));
        $lead->save();

        $this->mailAanvraag($site, $lead, $answers);
    }

    /**
     * Twee mails: een bevestiging aan de aanvrager en een melding aan onszelf.
     *
     * Allebei best-effort en apart afgevangen. Een lead die wél is vastgelegd maar
     * waarvan de mail strandt, mag geen foutpagina opleveren — en het mislukken van
     * de ene mail mag de andere niet tegenhouden.
     */
    private function mailAanvraag(ChannelSite $site, WebsiteLead $lead, array $antwoorden): void
    {
        try {
            Mail::to($lead->email)->send(new VoorbeeldAanvraagKlant($lead, $site, $antwoorden));
        } catch (\Throwable $e) {
            Log::warning('voorbeeld_aanvraag_klantmail: ' . $e->getMessage());
        }

        $to = (string) config('channels.lead_mail_to', config('mail.from.address'));
        if ($to === '') {
            return;
        }

        try {
            Mail::to($to)->send(new VoorbeeldAanvraagIntern($lead, $site, $antwoorden));
        } catch (\Throwable $e) {
            Log::warning('voorbeeld_aanvraag_internmail: ' . $e->getMessage());
        }
    }

    /**
     * Fase 1 van de tool (snel, geen AI): maak de preview-site en geef de key + de
     * twee vervolg-endpoints terug. De browser roept /content en /hero-image daarna
     * PARALLEL aan, zodat de ~35s tekst-call en de ~25-55s beeld-call tegelijk lopen
     * en de preview compleet (mét beeld) opent i.p.v. het beeld 30-60s later inploft.
     */
    public function start(Request $request, PreviewSiteGenerator $generator): JsonResponse
    {
        // Honeypot: bots vullen het verborgen 'website'-veld.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => false, 'error' => 'ongeldig'], 422);
        }

        $data = $request->validate([
            'company'       => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:120'],
            'goal'          => ['required', 'string', 'in:' . implode(',', array_keys(PreviewSiteGenerator::GOALS))],
            'sfeer'         => ['required', 'string', 'in:' . implode(',', array_keys(PreviewSiteGenerator::SFEREN))],
            // Kleur is nu OPTIONEEL (eigen-kleur-override); leeg = het sfeer-palet.
            'color'         => ['nullable', 'string', 'regex:/^#?[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/'],
            // Contact: verplicht, de tool is een lead-formulier. Geen los toestemming-veld
            // meer: contact over hun eigen aangevraagde voorbeeld is gerechtvaardigd belang,
            // dus een korte privacy-melding onder de knop volstaat. Een vooraf aangevinkt
            // vakje zou sowieso geen geldige AVG-toestemming zijn.
            'contact_name'  => ['required', 'string', 'max:120'],
            'email'         => ['required', 'email', 'max:190'],
            'phone'         => ['required', 'string', 'max:60'],
            // Optionele content-verrijking.
            'key_services'  => ['nullable', 'string', 'max:200'],
            'place'         => ['nullable', 'string', 'max:80'],
            'usp'           => ['nullable', 'string', 'max:160'],
        ], [], [
            'company' => 'bedrijfsnaam', 'business_type' => 'type bedrijf', 'goal' => 'doel', 'sfeer' => 'uitstraling',
            'color' => 'kleur', 'key_services' => 'kerndiensten', 'place' => 'plaats', 'usp' => 'onderscheider',
            'contact_name' => 'naam', 'email' => 'e-mailadres', 'phone' => 'telefoonnummer',
        ]);

        try {
            $site = $generator->startSite($data + ['source_channel' => $this->site()->key]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'start-mislukt'], 502);
        }

        // De lead NU vastleggen, niet pas bij "Bewaar dit voorbeeld": de bezoeker heeft
        // zijn gegevens al gegeven en mag onderweg afhaken zonder dat we hem kwijt zijn.
        // Best-effort: een lead-fout mag de preview waar hij op wacht nooit blokkeren.
        try {
            $this->captureLead($request, $data, $site->key);
        } catch (\Throwable $e) {
            report($e);
        }

        // Preview + vervolg-endpoints op de HUIDIGE host (channel-domein of hoofddomein);
        // ze hangen alle onder dezelfde /_site/{key}-groep.
        $base = url('/_site/' . $site->key);

        $payload = [
            'ok'         => true,
            'key'        => $site->key,
            'url'        => $base,
            'contentUrl' => $base . '/content',
            'heroUrl'    => $base . '/hero-image',
        ];
        // Webshop: ook een 3x2-productraster genereren (parallel, best-effort).
        if (($data['goal'] ?? '') === 'webshop') {
            $payload['productsUrl'] = $base . '/products-image';
        }

        return response()->json($payload);
    }

    /**
     * Legt de bezoeker vast als {@see WebsiteLead} zodra hij een voorbeeld start.
     *
     * Gededupliceerd op e-mail, net als {@see SavePreviewController}: dezelfde persoon
     * die drie voorbeelden maakt is één lead, geen drie. Bestaande gegevens blijven
     * leidend, zodat een nieuw voorbeeld een lead die het team al bewerkt niet overschrijft.
     */
    private function captureLead(Request $request, array $data, string $siteKey): void
    {
        $sourceChannel = $this->site()->key;

        $answers = array_filter([
            'goal'         => $data['goal'] ?? null,
            'sfeer'        => $data['sfeer'] ?? null,
            'color'        => $data['color'] ?? null,
            'type_bedrijf' => $data['business_type'] ?? null,
            'place'        => $data['place'] ?? null,
            'usp'          => $data['usp'] ?? null,
        ], fn ($v) => filled($v));

        $lead = WebsiteLead::firstOrNew(['email' => mb_strtolower(trim($data['email']))]);
        $lead->fill([
            'contact_name'   => $lead->contact_name ?: $data['contact_name'],
            'phone'          => $lead->phone ?: $data['phone'],
            'company'        => $lead->company ?: ($data['company'] ?? null),
            'branche'        => $lead->branche ?: $this->site()->branche(),
            'channel'        => $lead->channel ?: $sourceChannel,
            'source'         => $lead->source ?: 'preview_intake',
            // De checkbox is verplicht, dus hier altijd true. Blijft 'sticky': eenmaal
            // gegeven toestemming halen we niet weg omdat iemand een tweede site probeert.
            'consent'        => $request->boolean('consent') || (bool) $lead->consent,
            'answers'        => array_merge((array) $lead->answers, $answers) ?: null,
            // De preview draait nog; pas /content en /hero-image maken 'm compleet.
            'preview_status' => 'generating',
            'preview_url'    => $lead->preview_url ?: url('/_site/' . $siteKey),
        ]);
        $lead->status = $lead->status ?: 'new';

        $lead->fillAttributionOnce(CaptureAdAttribution::fromRequest($request));
        $lead->save();

        // Alleen de sleutel (geen e-mail/NAW) in de site-meta, zodat /content straks
        // weet welke lead op 'ready' mag zodra de generatie klaar is.
        Site::where('key', $siteKey)->get()->each(function (Site $m) use ($lead) {
            $meta = (array) $m->meta;
            $meta['preview']['lead_id'] = $lead->id;
            $m->update(['meta' => $meta]);
        });
    }

    /**
     * De preview is af: zet de lead van 'generating' naar 'ready'. Zonder dit bleef een
     * geslaagd voorbeeld in de admin voor altijd op "bezig" staan — alleen het bewaren
     * ({@see SavePreviewController}) zette de status ooit door, en dat doet lang niet
     * iedere bezoeker. Best-effort: het antwoord aan de wachtende bezoeker gaat voor.
     */
    private function markLeadReady(Site $model): void
    {
        try {
            $leadId = (int) data_get($model->meta, 'preview.lead_id');
            if ($leadId <= 0) {
                return;
            }

            WebsiteLead::where('id', $leadId)
                ->where('preview_status', 'generating')
                ->update(['preview_status' => 'ready']);
        } catch (\Throwable $e) {
            Log::warning('preview_lead_ready: ' . $e->getMessage());
        }
    }

    /**
     * Fase 2a (parallel met /hero-image): schrijf de content van de preview via de
     * ~35s Claude-call en vul de blokken. Aangeroepen door het laadscherm.
     */
    public function content(PreviewSiteGenerator $generator): JsonResponse
    {
        // Claude-call ~30-45s; standaard PHP-limiet van 30s zou 'm afkappen.
        @set_time_limit(120);
        ignore_user_abort(true);

        $site = $this->site();
        if (! $site->get('meta.preview.is_preview')) {
            return response()->json(['ok' => false, 'error' => 'geen-preview'], 404);
        }

        $model = Site::where('key', $site->key)->first();
        if (! $model) {
            return response()->json(['ok' => false, 'error' => 'niet-gevonden'], 404);
        }

        try {
            $res = $generator->fillContent($model);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'content-mislukt'], 502);
        }

        if (empty($res['ok'])) {
            Log::warning('preview_content: ' . ($res['error'] ?? 'onbekend'));
        } else {
            $this->markLeadReady($model);
        }

        return response()->json($res, empty($res['ok']) ? 502 : 200);
    }

    /**
     * Genereert asynchroon het branche-specifieke, full-width hero-beeld voor een
     * preview (aangeroepen door JS op de preview-pagina). Best-effort: mislukt het,
     * dan blijft de kleur-hero staan.
     */
    public function heroImage(PreviewSiteGenerator $generator): JsonResponse
    {
        // gpt-image-1 duurt ~20-40s; geef de request ruim de tijd.
        @set_time_limit(120);
        ignore_user_abort(true);

        $site = $this->site();
        if (! $site->get('meta.preview.is_preview')) {
            return response()->json(['ok' => false, 'error' => 'geen-preview'], 404);
        }

        $model = Site::where('key', $site->key)->first();
        if (! $model) {
            return response()->json(['ok' => false, 'error' => 'niet-gevonden'], 404);
        }

        try {
            $res = $generator->generateHeroImage($model);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'fout'], 502);
        }

        return response()->json($res, empty($res['ok']) ? 502 : 200);
    }

    /**
     * Genereert (parallel, best-effort) het 3x2-productraster voor een webshop-preview.
     * De productgrid-blade snijdt dit via CSS in 6 tegels. Alleen webshop-previews.
     */
    public function productsImage(PreviewSiteGenerator $generator): JsonResponse
    {
        @set_time_limit(120);
        ignore_user_abort(true);

        $site = $this->site();
        if (! $site->get('meta.preview.is_preview')) {
            return response()->json(['ok' => false, 'error' => 'geen-preview'], 404);
        }

        $model = Site::where('key', $site->key)->first();
        if (! $model) {
            return response()->json(['ok' => false, 'error' => 'niet-gevonden'], 404);
        }

        try {
            $res = $generator->generateProductsImage($model);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'fout'], 502);
        }

        return response()->json($res, empty($res['ok']) ? 502 : 200);
    }
}
