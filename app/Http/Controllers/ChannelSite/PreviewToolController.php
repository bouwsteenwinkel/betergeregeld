<?php

namespace App\Http\Controllers\ChannelSite;

use App\Http\Controllers\Controller;
use App\Models\Channel\Site;
use App\Services\ChannelSites\PreviewSiteGenerator;
use App\Support\ChannelSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

    /** De intake-pagina met het formulier + laadscherm. */
    public function form(): View
    {
        return view('channels.voorbeeld-maken', [
            'site'  => $this->site(),
            'goals' => PreviewSiteGenerator::GOALS,
        ]);
    }

    /** Genereert de voorbeeldsite en geeft de preview-URL terug (JSON, voor de fetch). */
    public function generate(Request $request, PreviewSiteGenerator $generator): JsonResponse
    {
        // De synchrone Claude-call duurt ~30-45s; de standaard PHP-limiet van 30s
        // zou 'm afkappen. Geef deze ene request ruim de tijd (client wacht met een
        // laadscherm). Prod (IIS/FastCGI) heeft hiervoor een ruime request-timeout.
        @set_time_limit(120);
        ignore_user_abort(true);

        // Honeypot: bots vullen het verborgen 'website'-veld.
        if (filled($request->input('website'))) {
            return response()->json(['ok' => false, 'error' => 'ongeldig'], 422);
        }

        $data = $request->validate([
            'company'       => ['required', 'string', 'max:120'],
            'business_type' => ['required', 'string', 'max:120'],
            'color'         => ['required', 'string', 'regex:/^#?[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/'],
            'goal'          => ['required', 'string', 'in:' . implode(',', array_keys(PreviewSiteGenerator::GOALS))],
        ], [], [
            'company' => 'bedrijfsnaam', 'business_type' => 'type bedrijf', 'color' => 'kleur', 'goal' => 'doel',
        ]);

        try {
            $result = $generator->generate($data + ['source_channel' => $this->site()->key]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'generatie-mislukt'], 502);
        }

        if (empty($result['ok'])) {
            Log::warning('preview_generate: ' . ($result['error'] ?? 'onbekend'));
            return response()->json(['ok' => false, 'error' => $result['error'] ?? 'onbekend'], 502);
        }

        // Previews leven op /_site/{key}, en die route bestaat ALLEEN op het
        // hoofddomein (betergeregeld.com). Op een live channel-domein
        // (bv. jouw-bedrijfswebsite.nl) vangt de greedy catch-all elk /_site/...-pad
        // af naar de 404. Draait de tool op zo'n live domein, bouw de preview-URL
        // dan expliciet op het hoofddomein; lokaal (draft, geen domein) blijft de
        // huidige host correct.
        $path = '/_site/' . $result['key'];
        $url  = $this->site()->isLive()
            ? rtrim(config('app.url'), '/') . $path
            : url($path);

        return response()->json([
            'ok'  => true,
            'url' => $url,
        ]);
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
}
