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
            'site'   => $this->site(),
            'goals'  => PreviewSiteGenerator::GOALS,
            'sferen' => PreviewSiteGenerator::SFEREN,
        ]);
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
            // Optionele content-verrijking.
            'key_services'  => ['nullable', 'string', 'max:200'],
            'place'         => ['nullable', 'string', 'max:80'],
            'usp'           => ['nullable', 'string', 'max:160'],
        ], [], [
            'company' => 'bedrijfsnaam', 'business_type' => 'type bedrijf', 'goal' => 'doel', 'sfeer' => 'uitstraling',
            'color' => 'kleur', 'key_services' => 'kerndiensten', 'place' => 'plaats', 'usp' => 'onderscheider',
        ]);

        try {
            $site = $generator->startSite($data + ['source_channel' => $this->site()->key]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok' => false, 'error' => 'start-mislukt'], 502);
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
