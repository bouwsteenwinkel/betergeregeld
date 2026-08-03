<?php

/**
 * CHANNEL-SITES routing.
 *
 * Wordt bovenaan routes/web.php ge-require'd zodat domein-routes vóór de
 * domein-loze hoofd-routes komen (Laravel = first-match-wins):
 *   - Live kanaal met domein → eigen Route::domain()-groep op dat domein.
 *   - Concept (zonder domein)  → preview op  /_site/{channelKey}  (hoofddomein).
 *
 * De ResolveChannelSite-middleware zet de juiste ChannelSite in de container.
 * Links binnen de site lopen via $site->url(...) (absoluut), dus de routes
 * hebben bewust geen namen (zou botsen bij meerdere domeinen).
 */

use App\Http\Controllers\ChannelSite\ChannelEventController;
use App\Http\Controllers\ChannelSite\ChannelSiteController;
use App\Http\Controllers\ChannelSite\PreviewToolController;
use App\Http\Controllers\ChannelSite\SavePreviewController;
use App\Http\Middleware\BlockChannelPages;
use App\Http\Middleware\CacheChannelPage;
use App\Http\Middleware\ResolveChannelSite;
use App\Services\ChannelSiteResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// Geldige Groeidiamant-fase-keys, voor de schone fase-URL's (/webshop e.d.).
$facetKeys = implode('|', array_keys((array) config('groeidiamant.facets', [])));

$channelRoutes = function () use ($facetKeys) {
    Route::get('/', [ChannelSiteController::class, 'home']);

    // First-party event-beacon (funnel-triggers → eigen DB, zie ChannelEventController).
    // Ruime throttle: sendBeacon vuurt per funnel-stap, niet per klik.
    Route::post('/_ev', [ChannelEventController::class, 'store'])->middleware('throttle:120,1');

    // Voorbeeld-/demolaag: "zo zou jouw site eruitzien". Aparte pagina zodat de
    // hoofd-URL de verkooppitch aan de ondernemer kan zijn (twee-lagen-model).
    Route::get('/voorbeeld', [ChannelSiteController::class, 'demo']);

    // Self-service "voorbeeld in 60 seconden"-tool: intake + synchrone generatie.
    // De gegenereerde previews leven als eigen site op /_site/preview-...
    Route::get('/voorbeeld-maken', [PreviewToolController::class, 'form']);
    // Fase 1: maak de preview-site (snel). Fase 2 draait PARALLEL vanaf het laadscherm:
    // /content (de tekst-call) + /hero-image (het branche-beeld), zodat beide tegelijk
    // lopen en de preview compleet opent i.p.v. het beeld 30-60s later inploft.
    // Elke nieuwe preview kost 1 Claude-call + 1 à 2 gpt-image-calls, dus het
    // aanmaken is per IP gelimiteerd (zie 'preview-start' in AppServiceProvider).
    Route::post('/voorbeeld-maken', [PreviewToolController::class, 'start'])->middleware('throttle:preview-start');
    Route::post('/content', [PreviewToolController::class, 'content'])->middleware('throttle:preview-ai');
    Route::post('/hero-image', [PreviewToolController::class, 'heroImage'])->middleware('throttle:preview-ai');
    // Webshop: 3x2-productraster (parallel met /content en /hero-image).
    Route::post('/products-image', [PreviewToolController::class, 'productsImage'])->middleware('throttle:preview-ai');
    // "Bewaar dit voorbeeld": maakt een klant-account (WebsiteLead) + koppelt de preview.
    Route::post('/bewaren', [SavePreviewController::class, 'save'])->middleware('throttle:10,1');

    Route::get('/over-ons', [ChannelSiteController::class, 'about']);
    Route::get('/contact', [ChannelSiteController::class, 'contact']);
    // Zelf een kennismaking inplannen (bestaande afsprakenwidget). Eigen pagina
    // omdat /contact op sommige kanalen geblokkeerd is. Let op: dit is het EXACTE
    // pad '/afspraak'; de data-endpoints '/afspraak/beschikbaarheid' en
    // '/afspraak/boeken' staan in web.php en vallen via de catch-all-uitzondering
    // hieronder door.
    Route::get('/afspraak', [ChannelSiteController::class, 'appointment']);
    // Aparte bevestigings-URL na een geslaagde boeking (aparte pageview voor ads-
    // conversiemeting). De booking-widget redirect hierheen; zie partials/booking.
    Route::get('/afspraak-bevestigd', [ChannelSiteController::class, 'appointmentConfirmed']);
    Route::get('/diensten', [ChannelSiteController::class, 'services']);
    Route::get('/groeidiamant', [ChannelSiteController::class, 'groeidiamant']);
    Route::get('/prijzen', [ChannelSiteController::class, 'pricing']);
    Route::get('/werkwijze', [ChannelSiteController::class, 'werkwijze']);
    Route::get('/cases', [ChannelSiteController::class, 'cases']);
    Route::get('/veelgestelde-vragen', [ChannelSiteController::class, 'faq']);
    Route::get('/vergelijken', [ChannelSiteController::class, 'vergelijken']);
    Route::get('/privacybeleid', [ChannelSiteController::class, 'privacy']);
    Route::get('/cookiebeleid', [ChannelSiteController::class, 'cookies']);
    Route::get('/algemene-voorwaarden', [ChannelSiteController::class, 'terms']);

    Route::get('/plaatsen', [ChannelSiteController::class, 'places']);
    Route::get('/plaatsen/provincie/{prov}', [ChannelSiteController::class, 'province']);
    Route::get('/plaatsen/{place}', [ChannelSiteController::class, 'place']);

    Route::get('/blog', [ChannelSiteController::class, 'blogIndex']);
    Route::get('/blog/{slug}', [ChannelSiteController::class, 'blogShow']);

    // SEO: per-kanaal sitemap + robots (zodat Google elke site apart indexeert).
    Route::get('/sitemap.xml', [ChannelSiteController::class, 'sitemap']);
    Route::get('/robots.txt', [ChannelSiteController::class, 'robots']);
    // GEO: curated markdown-samenvatting voor AI-antwoordmachines (llmstxt.org).
    Route::get('/llms.txt', [ChannelSiteController::class, 'llmsTxt']);

    Route::post('/contact', [ChannelSiteController::class, 'leadStore'])->middleware('throttle:10,1');
    Route::get('/bedankt', [ChannelSiteController::class, 'leadSent']);

    // SEO-instap op een Groeidiamant-fase als schone URL (bv. /webshop). Staat
    // bewust onderaan + is begrensd op geldige fase-keys, zodat het de statische
    // routes hierboven niet afvangt. /{facet}/fragment = de live AJAX-switch.
    if ($facetKeys !== '') {
        // Facet-instap op de demolaag (/voorbeeld/webshop) + de live AJAX-switch.
        Route::get('/voorbeeld/{facet}', [ChannelSiteController::class, 'demo'])->where('facet', $facetKeys);
        Route::get('/voorbeeld/{facet}/fragment', [ChannelSiteController::class, 'demoFragmentRoute'])->where('facet', $facetKeys);

        // Legacy: sites zonder aparte verkooppagina hebben de demo op de hoofd-URL.
        Route::get('/{facet}', [ChannelSiteController::class, 'home'])->where('facet', $facetKeys);
        Route::get('/{facet}/fragment', [ChannelSiteController::class, 'homeFragment'])->where('facet', $facetKeys);
    }

    // Vangnet: elk overig pad op een channel-domein blijft BINNEN de channel en
    // toont nooit de hoofd-site (betergeregeld.com). Staat bewust als laatste, zodat
    // alle specifieke routes hierboven eerst matchen. Omdat de channel-domeingroepen
    // in web.php vóór de domein-loze hoofd-routes worden ge-require'd, wint deze
    // greedy catch-all van de hoofd-routes (/nl, /en, /afspraak, …) op een channel-
    // domein. (Route::fallback zou hier NIET werken: de hoofd-/nl-route is een gewone
    // route die eerder matcht dan een fallback-route.)
    //
    // Uitzondering 1: '_site/...'-paden NIET afvangen (negatieve lookahead), zodat een
    // gegenereerde preview op /_site/{key} óók op een live channel-domein doorvalt
    // naar de domein-loze preview-groep hieronder en op het channel-domein zelf
    // opent. Zonder deze uitzondering slokte de catch-all elk /_site/...-pad op → 404.
    //
    // Uitzondering 2: 'afspraak/...' (de data-endpoints /afspraak/beschikbaarheid en
    // /afspraak/boeken uit web.php). De afsprakenwidget roept die same-origin aan, dus
    // op een channel-domein at de catch-all ze op en laadde de widget nooit z'n
    // momenten. Alleen de SUBpaden vallen door; '/afspraak' zelf is hierboven een
    // echte channel-pagina.
    //
    // Uitzondering 3: 'cmp/...' (de cookiebanner uit web.php: loader.js, scripts.js en
    // POST consent). Exact hetzelfde probleem, met zwaardere gevolgen: layout.blade.php
    // laadt <script src="{{ url('/cmp/loader.js') }}"> op de HUIDIGE host, dus op een
    // channel-domein at de catch-all dat op en gaf een 302 naar de homepage. De browser
    // kreeg 70 KB HTML als JavaScript aangeboden, de banner verscheen nooit en POST
    // /cmp/consent kon de toestemming nooit vastleggen. Op betergeregeld.com zelf werkte
    // het wel, dus dit viel bij testen niet op.
    Route::any('{any}', [ChannelSiteController::class, 'notFound'])->where('any', '(?!_site/|afspraak/|cmp/).*');
};

// 1) Live kanalen op hun eigen domein.
// Voor het registreren van de routes is alleen de domeinnaam nodig, maar
// ChannelSiteResolver::live() haalde daarvoor bij ELK verzoek alle live sites op
// mét hun branche en blokken. Die query stond dus voor elke paginaweergave in de
// weg, ook voor een pagina die daarna uit de cache kwam. Vijf minuten een lijstje
// domeinnamen bewaren scheelt dat; een kanaal dat live gaat is dus hooguit een
// paar minuten later bereikbaar. Bewust alleen strings in de cache — Eloquent-
// objecten horen daar niet in.
$liveDomains = Cache::remember('channel:live-domains', 300, function () {
    $uit = [];
    foreach (app(ChannelSiteResolver::class)->live() as $s) {
        $d = (string) $s->domain();
        if ($d !== '') $uit[] = $d;
    }
    return $uit;
});

// CacheChannelPage alleen op de LIVE domeinen, niet op de preview-groep
// hieronder: een concept-kanaal moet direct tonen wat er net gewijzigd is.
foreach ($liveDomains as $liveDomain) {
    Route::domain($liveDomain)
        // CacheChannelPage staat vóór ResolveChannelSite: bij een treffer hoeft
        // het kanaal niet eens uit de database te komen. Dat scheelt de query's
        // die juist de traagste stap waren.
        ->middleware([CacheChannelPage::class, ResolveChannelSite::class, BlockChannelPages::class])
        ->group($channelRoutes);
}

// 1b) www.<kanaal> → het kale domein, met een 301.
//
// Route::domain() hierboven registreert alleen het kale domein. Alles op www.
// viel daardoor door naar de hoofdapplicatie: wie www.jouw-bakkerij-website.nl
// intikte kreeg de homepage van Beter Geregeld ICT te zien, met index,follow en
// een canonical naar zichzelf. Google mocht dus zeventien vrijwel identieke
// kopieën van diezelfde pagina indexeren, elk onder een andere merknaam.
//
// Een 301 en niet een eigen kopie van de site: één adres per kanaal houdt de
// linkwaarde bij elkaar en voorkomt dat dezelfde inhoud twee keer bestaat.
// Pad en querystring gaan mee, zodat een gedeelde diepe link blijft werken.
foreach ($liveDomains as $liveDomain) {
    Route::domain('www.' . $liveDomain)->any('{pad?}', function (string $pad = '') use ($liveDomain) {
        $query = request()->getQueryString();
        return redirect('https://' . $liveDomain . '/' . ltrim($pad, '/') . ($query ? '?' . $query : ''), 301);
    })->where('pad', '.*');
}

// 2) Preview/concept op /_site/{channelKey}/... — domein-loos, dus dit matcht op
//    ELK host (hoofddomein én een live channel-domein). De catch-all in de
//    domeingroep hierboven sluit '_site/'-paden bewust uit (zie where-constraint),
//    zodat een preview óók op het channel-domein zelf opent (voelt als de eigen
//    site van de klant) i.p.v. dat de catch-all 'm naar de 404 stuurt.
Route::prefix('_site/{channelKey}')
    ->middleware([ResolveChannelSite::class, BlockChannelPages::class])
    ->group($channelRoutes);
