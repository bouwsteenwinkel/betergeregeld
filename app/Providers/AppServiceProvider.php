<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Speedtest data pipes: one full test = ~7 requests (5 ping + download + upload).
        // 60/min lets a single IP comfortably run 3–5 tests before throttling.
        RateLimiter::for('speedtest', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
    }
}
