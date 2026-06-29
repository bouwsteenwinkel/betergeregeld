<?php

namespace App\Http\Controllers;

use App\Mail\IntakeConfirmation;
use App\Models\WebsiteLead;
use App\Services\ChannelSiteResolver;
use App\Support\Geo\GeoBussum;
use App\Support\Intake\AppointmentSlots;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Publieke intake "website laten maken". Twee ingangen, één centrale afhandeling:
 *   - generiek (/website-laten-maken): klant kiest branche, vragen volgen de branche.
 *   - per kanaal (/p/{kanaal}): eigen landingspagina met eigen ALGEMENE + SPECIFIEKE
 *     vragen (config/promo.php). De lead wordt getagd met channel = kanaal-key, zodat
 *     in de admin duidelijk is via welk kanaal de afspraak binnenkwam.
 */
class WebsiteIntakeController extends Controller
{
    // ── Generiek ────────────────────────────────────────────────────────

    public function show(Request $request, string $locale): View
    {
        return view('pages.intake', [
            'branches'      => WebsiteLead::BRANCHES,
            'common'        => WebsiteLead::intakeCommonQuestions(),
            'branchesDef'   => (array) config('intake.branches', []),
            'slotDays'      => AppointmentSlots::upcoming(2),
            'radiusKm'      => GeoBussum::RADIUS_KM,
            'branchePreset' => (string) $request->query('branche', ''),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $branche = (string) $request->input('branche');
        if (! array_key_exists($branche, WebsiteLead::BRANCHES)) {
            return back()->withErrors(['branche' => 'Kies een branche.'])->withInput();
        }

        return $this->persist($request, $locale, [
            'branche'   => $branche,
            'facet'     => WebsiteLead::normalizeFacet(null),
            'channel'   => 'intake',
            'validQ'    => array_column(WebsiteLead::intakeAllQuestions($branche), 'key'),
            'validF'    => array_keys(WebsiteLead::intakeFeaturesFor($branche)),
            'redirect'  => 'intake.sent',
        ], true);
    }

    // ── Per kanaal ──────────────────────────────────────────────────────

    public function showChannel(Request $request, string $locale, string $channel, ?string $facet = null): View
    {
        $cfg = config('promo.channels.' . $channel);
        abort_unless(is_array($cfg), 404);
        $facet = WebsiteLead::normalizeFacet($facet);

        return view('pages.promo', [
            'channel'    => $cfg,
            'channelKey' => $channel,
            'facetKey'   => $facet,
            'facet'      => WebsiteLead::facets()[$facet] ?? [],
            'demoUrl'    => $this->demoUrl($cfg),
            'slotDays'   => AppointmentSlots::upcoming(2),
            'radiusKm'   => GeoBussum::RADIUS_KM,
        ]);
    }

    public function storeChannel(Request $request, string $locale, string $channel, ?string $facet = null): RedirectResponse
    {
        $cfg = config('promo.channels.' . $channel);
        abort_unless(is_array($cfg), 404);

        $questions = array_merge($cfg['questions']['general'] ?? [], $cfg['questions']['specific'] ?? []);

        return $this->persist($request, $locale, [
            'branche'    => (string) ($cfg['branche'] ?? 'overig'),
            'facet'      => WebsiteLead::normalizeFacet($facet),
            'channel'    => $channel,
            'validQ'     => array_column($questions, 'key'),
            'validF'     => array_keys($cfg['features'] ?? []),
            'previewUrl' => $this->demoUrl($cfg),   // koppelt de voorbeeldsite alvast aan de lead
            'redirect'   => 'intake.sent',
        ], false);
    }

    /** Voorbeeldsite-URL voor een kanaal (channel-site key in 'demo'), of null. */
    private function demoUrl(array $cfg): ?string
    {
        if (empty($cfg['demo'])) {
            return null;
        }
        $site = app(ChannelSiteResolver::class)->byKey((string) $cfg['demo']);
        return $site ? $site->baseUrl() : null;
    }

    // ── Bedankt ─────────────────────────────────────────────────────────

    public function sent(Request $request, string $locale): View
    {
        $done = $request->session()->get('intake_done');
        abort_unless($done, 404);
        return view('pages.intake-sent', ['done' => $done]);
    }

    // ── Gedeelde afhandeling ────────────────────────────────────────────

    /**
     * @param array{branche:string,channel:string,validQ:array,validF:array,redirect:string} $ctx
     */
    private function persist(Request $request, string $locale, array $ctx, bool $generic): RedirectResponse
    {
        // Honeypot: bots vullen 'website' in.
        if (filled($request->input('website'))) {
            return redirect()->route('intake.sent', ['locale' => $locale]);
        }

        $data = $request->validate([
            'company'          => ['required', 'string', 'max:190'],
            'contact_name'     => ['required', 'string', 'max:120'],
            'email'            => ['required', 'email', 'max:190'],
            'phone'            => ['required', 'string', 'max:60'],
            'postcode'         => ['required', 'string', 'max:12'],
            'city'             => ['nullable', 'string', 'max:120'],
            'current_website'  => ['nullable', 'string', 'max:255'],
            'message'          => ['nullable', 'string', 'max:4000'],
            'features'         => ['nullable', 'array'],
            'features.*'       => ['string', 'max:60'],
            'answers'          => ['nullable', 'array'],
            'appointment_slot' => ['required', 'string'],
            'appointment_pref' => ['required', 'in:onsite,meet'],
        ], [], [
            'company' => 'bedrijfsnaam', 'contact_name' => 'naam', 'phone' => 'telefoon',
            'postcode' => 'postcode', 'appointment_slot' => 'tijdslot',
        ]);

        if (! AppointmentSlots::isValid($data['appointment_slot'])) {
            return back()->withErrors(['appointment_slot' => 'Kies een geldig tijdslot.'])->withInput();
        }

        // Antwoorden + functies saneren tot wat bij dit kanaal/branche hoort.
        $answers = [];
        foreach ((array) ($data['answers'] ?? []) as $k => $v) {
            if (in_array($k, $ctx['validQ'], true) && $v !== '' && $v !== null) {
                $answers[$k] = is_array($v) ? array_map('strval', $v) : (string) $v;
            }
        }
        $features = array_values(array_intersect((array) ($data['features'] ?? []), $ctx['validF']));

        // Afstand tot Bussum → bezoek of Meet.
        $distance = GeoBussum::distanceKm($data['postcode']);
        $within   = GeoBussum::withinRadius($distance);
        $type     = ($data['appointment_pref'] === 'onsite' && $within === true) ? 'onsite' : 'meet';

        $lead = WebsiteLead::create([
            'company'            => $data['company'],
            'branche'            => $ctx['branche'],
            'facet'              => $ctx['facet'] ?? 'website',
            'contact_name'       => $data['contact_name'],
            'email'              => $data['email'],
            'phone'              => $data['phone'],
            'postcode'           => strtoupper(preg_replace('/\s+/', '', $data['postcode'])),
            'city'               => $data['city'] ?? null,
            'distance_km'        => $distance,
            'within_radius'      => $within,
            'current_website'    => $data['current_website'] ?? null,
            'message'            => $data['message'] ?? null,
            'answers'            => $answers,
            'features'           => $features,
            'channel'            => $ctx['channel'],
            'source'             => 'intake',
            'status'             => 'appointment',
            'preview_url'        => $ctx['previewUrl'] ?? null,
            'preview_status'     => ! empty($ctx['previewUrl']) ? 'ready' : 'todo',
            'appointment_at'     => $data['appointment_slot'],
            'appointment_type'   => $type,
            'appointment_status' => 'requested',
        ]);

        $this->notify($lead, $type, $data['appointment_slot']);

        $request->session()->put('intake_done', [
            'name'  => $lead->contact_name,
            'when'  => AppointmentSlots::labelFor($data['appointment_slot']),
            'type'  => $type,
            'email' => $lead->email,
        ]);

        return redirect()->route($ctx['redirect'], ['locale' => $locale]);
    }

    private function notify(WebsiteLead $lead, string $type, string $slot): void
    {
        try {
            Mail::to($lead->email)->send(new IntakeConfirmation($lead));
        } catch (\Throwable $e) {
            Log::warning('intake_confirmation_mail: ' . $e->getMessage());
        }
        try {
            $to = config('mail.from.address');
            if ($to) {
                $branche = WebsiteLead::BRANCHES[$lead->branche] ?? $lead->branche;
                Mail::raw(
                    "Nieuwe website-lead.\n\nKanaal: {$lead->channel}\nBedrijf: {$lead->company}\nBranche: {$branche}\n"
                    . "Contact: {$lead->contact_name} · {$lead->email} · {$lead->phone}\n"
                    . 'Afspraak: ' . AppointmentSlots::labelFor($slot) . ' (' . ($type === 'onsite' ? 'bezoek' : 'Google Meet') . ")\n"
                    . 'Afstand Bussum: ' . ($lead->distance_km !== null ? $lead->distance_km . ' km' : 'onbekend')
                    . "\n\nOpvolgen in de admin → Website-leads.",
                    fn ($m) => $m->to($to)->subject('Nieuwe lead (' . $lead->channel . '): ' . $lead->company)
                );
            }
        } catch (\Throwable $e) {
            Log::warning('intake_internal_mail: ' . $e->getMessage());
        }
    }
}
