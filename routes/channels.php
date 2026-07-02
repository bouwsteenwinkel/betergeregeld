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

    Route::get('/over-ons', [ChannelSiteController::class, 'about']);

    Route::get('/plaatsen', [ChannelSiteController::class, 'places']);
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
};

// 1) Live kanalen op hun eigen domein.
foreach (app(ChannelSiteResolver::class)->live() as $site) {
    Route::domain($site->domain())
        ->middleware(ResolveChannelSite::class)
        ->group($channelRoutes);
}

// 2) Preview/concept op het hoofddomein: /_site/{channelKey}/...
Route::prefix('_site/{channelKey}')
    ->middleware(ResolveChannelSite::class)
    ->group($channelRoutes);
