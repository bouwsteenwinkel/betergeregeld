<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CaptureAdAttribution;
use App\Mail\PreviewSavedMail;
use App\Models\Channel\Site;
use App\Models\SavedPreview;
use App\Models\WebsiteLead;
use App\Support\ChannelSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * "Bewaar dit voorbeeld": maakt van de bezoeker een lichtgewicht, wachtwoordloos
 * klant-account (een {@see WebsiteLead}, gededupliceerd op e-mail), koppelt de
 * opgeslagen preview ({@see SavedPreview}), beschermt 'm tegen cleanup (claimed),
 * mailt een persoonlijke revisit-link en plant reminders (bij consent).
 */
class SavePreviewController extends Controller
{
    public function save(Request $request): JsonResponse
    {
        /** @var ChannelSite $site */
        $site = app(ChannelSite::class);

        // Honeypot: bots vullen 'website'. Stil 'ok'.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true]);
        }
        if (! $site->get('meta.preview.is_preview')) {
            return response()->json(['ok' => false, 'error' => 'geen-preview'], 404);
        }

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'email'        => ['required', 'email', 'max:190'],
            'phone'        => ['nullable', 'string', 'max:60'],
            // Geen toestemming-veld: de bezoeker vraagt hier zélf om de mail met zijn
            // persoonlijke link. Dat is gerechtvaardigd belang, geen toestemming, en
            // een vóór-aangevinkt vakje zou sowieso niet geldig zijn. Zelfde lijn als
            // PreviewToolController::start(). De privacy-melding staat onder de knop.
        ], [], ['contact_name' => 'naam', 'email' => 'e-mail']);

        $siteKey       = $site->key;
        $input         = (array) $site->get('meta.preview.input', []);
        $sourceChannel = (string) ($site->get('meta.preview.source_channel') ?: $siteKey);

        // Ontwerp-voorkeuren uit de preview-meta, voor het team.
        $answers = array_merge((array) null, array_filter([
            'goal'         => $input['goal'] ?? null,
            'sfeer'        => $input['sfeer'] ?? null,
            'color'        => $input['color'] ?? null,
            'type_bedrijf' => $input['business_type'] ?? null,
            'place'        => $input['place'] ?? null,
            'usp'          => $input['usp'] ?? null,
        ], fn ($v) => filled($v)));

        // Find-or-create op e-mail = het account.
        $lead = WebsiteLead::firstOrNew(['email' => mb_strtolower(trim($data['email']))]);
        $lead->fill([
            'contact_name'   => $data['contact_name'],
            'phone'          => $data['phone'] ?? $lead->phone,
            'company'        => $lead->company ?: ($input['company'] ?? null),
            'branche'        => $lead->branche ?: $site->branche(),
            'channel'        => $lead->channel ?: $sourceChannel,
            'source'         => $lead->source ?: 'preview_saved',
            // Altijd true: het bewaren van een voorbeeld is een expliciet verzoek om
            // de mail met de persoonlijke link. Stond hier eerder op een vinkje dat na
            // het intake-formulier toch al niets meer deed (consent is sticky).
            'consent'        => true,
            'answers'        => array_merge((array) $lead->answers, $answers) ?: null,
            'preview_status' => 'ready',
        ]);
        $lead->status   = $lead->status ?: 'new';
        $lead->saved_at = $lead->saved_at ?: now();

        // Klik-herkomst uit de cookie die op de landingspagina is gezet: de URL-
        // parameters van de advertentieklik zijn hier allang weg. First touch wint,
        // dus een tweede bewaaractie laat de oorspronkelijke campagne staan.
        $lead->fillAttributionOnce(CaptureAdAttribution::fromRequest($request));
        $lead->save();

        // Opgeslagen preview koppelen; de eerste van de klant wordt de favoriet.
        $isFirst = ! $lead->savedPreviews()->exists();
        SavedPreview::firstOrCreate(
            ['website_lead_id' => $lead->id, 'site_key' => $siteKey],
            ['favorite' => $isFirst],
        );

        // preview_url van de lead wijst naar de favoriet.
        $fav = $lead->savedPreviews()->where('favorite', true)->first();
        $lead->update(['preview_url' => url('/_site/' . ($fav->site_key ?? $siteKey))]);

        // Claim de preview zodat ChannelPreviewsCleanup 'm onbeperkt laat staan.
        Site::where('key', $siteKey)->get()->each(function ($m) {
            $meta = (array) $m->meta;
            $meta['preview']['claimed'] = true;
            $m->update(['meta' => $meta]);
        });

        // Reminders plannen: alleen bij consent, en alleen als er nog niets loopt.
        if ($lead->consent && is_null($lead->next_reminder_at) && $lead->reminder_stage < 1 && is_null($lead->unsubscribed_at)) {
            $lead->update(['reminder_stage' => 0, 'next_reminder_at' => now()->addDay()]);
        }

        $revisitUrl = $lead->revisitUrl();

        // Bevestigingsmail (transactioneel: de klant vroeg zelf de link). Zacht falen.
        try {
            Mail::to($lead->email)->send(new PreviewSavedMail($lead));
        } catch (\Throwable $e) {
            Log::warning('preview_saved_mail: ' . $e->getMessage());
        }

        // Interne ping voor het team (best-effort, plain text).
        try {
            $to = (string) config('mail.from.address');
            if ($to !== '') {
                Mail::raw(
                    "Voorbeeld opgeslagen door {$lead->contact_name} ({$lead->email}).\n"
                    . "Bedrijf: " . ($lead->company ?: '-') . " | Kanaal: {$sourceChannel}\n"
                    . "Preview: " . url('/_site/' . $siteKey),
                    fn ($m) => $m->to($to)->subject('Voorbeeld opgeslagen: ' . ($lead->company ?: $lead->contact_name)),
                );
            }
        } catch (\Throwable $e) {
            Log::warning('preview_saved_internal: ' . $e->getMessage());
        }

        return response()->json(['ok' => true, 'revisitUrl' => $revisitUrl]);
    }
}
