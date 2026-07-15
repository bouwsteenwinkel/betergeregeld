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

use App\Http\Controllers\ChannelSite\ChannelSiteController;
use App\Http\Controllers\ChannelSite\PreviewToolController;
use App\Http\Controllers\ChannelSite\SavePreviewController;
use App\Http\Middleware\BlockChannelPages;
use App\Http\Middleware\ResolveChannelSite;
use App\Services\ChannelSiteResolver;
use Illuminate\Support\Facades\Route;

// Geldige Groeidiamant-fase-keys, voor de schone fase-URL's (/webshop e.d.).
$facetKeys = implode('|', array_keys((array) config('groeidiamant.facets', [])));

$channelRoutes = function () use ($facetKeys) {
    Route::get('/', [ChannelSiteController::class, 'home']);

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
    Route::any('{any}', [ChannelSiteController::class, 'notFound'])->where('any', '(?!_site/|afspraak/).*');
};

// 1) Live kanalen op hun eigen domein.
foreach (app(ChannelSiteResolver::class)->live() as $site) {
    Route::domain($site->domain())
        ->middleware([ResolveChannelSite::class, BlockChannelPages::class])
        ->group($channelRoutes);
}

// 2) Preview/concept op /_site/{channelKey}/... — domein-loos, dus dit matcht op
//    ELK host (hoofddomein én een live channel-domein). De catch-all in de
//    domeingroep hierboven sluit '_site/'-paden bewust uit (zie where-constraint),
//    zodat een preview óók op het channel-domein zelf opent (voelt als de eigen
//    site van de klant) i.p.v. dat de catch-all 'm naar de 404 stuurt.
Route::prefix('_site/{channelKey}')
    ->middleware([ResolveChannelSite::class, BlockChannelPages::class])
    ->group($channelRoutes);
