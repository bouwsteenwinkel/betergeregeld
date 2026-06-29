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

$channelRoutes = function () {
    Route::get('/', [ChannelSiteController::class, 'home']);
    // SEO-instap op een Groeidiamant-fase: dezelfde homepage, maar toegesneden
    // op de fase waar de bezoeker al is (bv. /groeifase/webshop).
    Route::get('/groeifase/{facet}', [ChannelSiteController::class, 'home']);
    Route::get('/over-ons', [ChannelSiteController::class, 'about']);

    Route::get('/plaatsen', [ChannelSiteController::class, 'places']);
    Route::get('/plaatsen/{place}', [ChannelSiteController::class, 'place']);

    Route::get('/blog', [ChannelSiteController::class, 'blogIndex']);
    Route::get('/blog/{slug}', [ChannelSiteController::class, 'blogShow']);

    Route::post('/contact', [ChannelSiteController::class, 'leadStore'])->middleware('throttle:10,1');
    Route::get('/bedankt', [ChannelSiteController::class, 'leadSent']);
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
