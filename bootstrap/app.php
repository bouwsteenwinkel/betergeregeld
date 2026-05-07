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

        $middleware->alias([
            'setlocale' => \App\Http\Middleware\SetLocale::class,
            'tool.limit' => \App\Http\Middleware\EnforceToolRateLimit::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'tools/speedtest/upload',
            'cmp/consent',
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
        //
    })->create();
