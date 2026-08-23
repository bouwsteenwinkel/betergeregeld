<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Plesk/IIS terminates HTTPS in front of Laravel. Without this,
        // request()->isSecure() returns false → secure cookies won't be
        // set and URL::route() generates http:// links under HTTPS pages.
        $middleware->trustProxies(at: '*');

        // Klik-herkomst vastleggen. Op de hele web-groep en niet alleen op de
        // channel-routes, omdat de afspraken-API (POST /afspraak/boeken) daarbuiten
        // valt terwijl juist daar een lead ontstaat. Append: moet ná EncryptCookies
        // draaien, anders is de cookie bij het lezen nog versleuteld. Doet niets
        // zolang er geen advertentie-parameter in de URL staat.
        // Geen sessiebestand voor crawlers en machine-endpoints. Moet VOOR StartSession,
        // want die leest de driver zodra hij aan de beurt is — vandaar prepend.
        $middleware->web(prepend: [
            \App\Http\Middleware\GeenOnnodigeSessie::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CaptureAdAttribution::class,
        ]);

        // De CMP zet cmp_consent_id zelf vanuit JavaScript (resources/cmp/loader.js),
        // dus onversleuteld. Zonder deze uitzondering probeert EncryptCookies hem te
        // ontsleutelen, faalt dat, en ziet de server de cookie helemaal niet. Daardoor
        // kon server-side niet worden vastgesteld of iemand toestemming gaf, wat
        // CaptureAdAttribution juist nodig heeft om de advertentie-cookie te gaten.
        $middleware->encryptCookies(except: [
            'cmp_consent_id',
        ]);

        $middleware->alias([
            'setlocale' => \App\Http\Middleware\SetLocale::class,
            'tool.limit' => \App\Http\Middleware\EnforceToolRateLimit::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'tools/speedtest/upload',
            'cmp/consent',
            '_ev',            // channel event-beacon (sendBeacon kan geen CSRF-header zetten)
            '_site/*/_ev',    // idem op de preview-route van concept-kanalen
            'monitor/ingest',
            'monitor/ingest-disk',
            'monitor/socketlabs/webhook',
            'security/ingest/*',
            'cron/ping/*',
        ]);

        // Guests redirect to the login of their current locale (falling back
        // to the app default). Without this, unauthenticated requests to any
        // {locale}-prefixed route fail because route('login') can't resolve
        // the missing {locale} parameter.
        $middleware->redirectGuestsTo(function (Request $request) {
            $supported = \App\Http\Middleware\SetLocale::SUPPORTED;
            $seg = $request->segment(1);
            $locale = (is_string($seg) && in_array($seg, $supported, true))
                ? $seg
                : config('app.locale', 'nl');
            return route('login', ['locale' => $locale]);
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Soft-404-kanalen (config/channel_soft_404.php): een niet-bestaande
        // plaats-/blog-slug (abort 404 / firstOrFail) stuurt de bezoeker naar de
        // eigen homepage i.p.v. een foutpagina. Andere kanalen/requests: default.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if (! app()->bound(\App\Support\ChannelSite::class)) {
                return null;
            }
            $site = app(\App\Support\ChannelSite::class);
            if ($site->redirectsNotFoundToHome()) {
                return redirect($site->url(''), 302);
            }
            return null;
        });
    })->create();
