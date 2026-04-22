<?php

namespace App\Providers;

use App\Models\BookkeepingCategory;
use App\Models\BookkeepingRelation;
use App\Models\BookkeepingTransaction;
use App\Models\BookkeepingVatRate;
use App\Observers\BookkeepingAuditObserver;
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

        // Bookkeeping audit log: every create/update/delete on these models
        // writes to bookkeeping_audit_log via the observer.
        BookkeepingTransaction::observe(BookkeepingAuditObserver::class);
        BookkeepingRelation::observe(BookkeepingAuditObserver::class);
        BookkeepingCategory::observe(BookkeepingAuditObserver::class);
        BookkeepingVatRate::observe(BookkeepingAuditObserver::class);
    }
}
