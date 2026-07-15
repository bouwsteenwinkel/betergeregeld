<?php

namespace App\Providers;

use App\Models\BookkeepingCategory;
use App\Models\BookkeepingInvoice;
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
        // Filament/Livewire rendert full-page componenten ín een layout en roept
        // daarbij intern file_exists() aan op een template-string. Draait de server
        // met open_basedir aan (Plesk/IIS), dan geeft file_exists() een PHP-warning
        // die Laravel's error-handler tot een fatale ErrorException promoveert → 500
        // op /admin (de publieke channel-sites raken dit pad niet). We vangen puur
        // die ene open_basedir-warning af zodat file_exists() gewoon false teruggeeft
        // en Livewire de layout inline rendert — al het andere gaat ongewijzigd door
        // naar de vorige (Laravel-)handler.
        $previous = set_error_handler(function (int $level, string $message, string $file = '', int $line = 0) use (&$previous) {
            if (str_contains($message, 'open_basedir')) {
                return true;
            }
            return $previous ? ($previous)($level, $message, $file, $line) : false;
        });

        // Quality-scan services: interface → implementatie (mockbaar in tests).
        $this->app->bind(
            \App\Services\QualityScan\Contracts\PageFetcherInterface::class,
            \App\Services\QualityScan\PageFetcher::class,
        );
        $this->app->bind(
            \App\Services\QualityScan\Contracts\ContentExtractorInterface::class,
            \App\Services\QualityScan\ContentExtractor::class,
        );
        $this->app->bind(
            \App\Services\QualityScan\Contracts\ClaudeAnalyzerInterface::class,
            \App\Services\QualityScan\ClaudeAnalyzer::class,
        );

        // Afsprakenplanner: agenda-provider. Echte Google Calendar-gateway zodra die
        // gekoppeld is (refresh-token aanwezig), anders de stub (geen Meet-link).
        $this->app->bind(\App\Services\Scheduling\Contracts\CalendarGateway::class, function ($app) {
            $google = $app->make(\App\Services\Scheduling\GoogleCalendarGateway::class);
            return (config('scheduling.google.enabled') && $google->isConnected())
                ? $google
                : $app->make(\App\Services\Scheduling\StubCalendarGateway::class);
        });
    }

    public function boot(): void
    {
        // Speedtest data pipes: one full test = ~7 requests (5 ping + download + upload).
        // 60/min lets a single IP comfortably run 3–5 tests before throttling.
        RateLimiter::for('speedtest', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        // Voorbeeld-tool: elke NIEUWE preview kost een Claude-call (tekst) plus een
        // tot twee gpt-image-calls (hero, en bij webshop het productraster). Die
        // rekening loopt per submit, dus het aanmaken is gelimiteerd per IP. Een
        // echte bezoeker maakt er een handvol; dit knelt pas bij geautomatiseerd
        // misbruik. Herhaalde calls op een BESTAANDE preview zijn al gratis:
        // fillContent stopt op bestaande blokken en generateRaw op een bestaand
        // beeldbestand.
        $previewBusy = fn () => response()->json([
            'ok'      => false,
            'error'   => 'te-veel',
            'message' => 'Je hebt net al een paar voorbeelden gemaakt. Probeer het over een uur nog eens, of bel ons even, dan maken we hem samen af.',
        ], 429);

        RateLimiter::for('preview-start', fn (Request $request) => [
            Limit::perHour(5)->by($request->ip())->response($previewBusy),
            Limit::perDay(15)->by($request->ip())->response($previewBusy),
        ]);

        // De AI-vervolgstappen (/content, /hero-image, /products-image). Per preview
        // zijn ze idempotent, maar gelijktijdige calls kunnen langs die check racen
        // en alsnog dubbel inkopen. Ruim genoeg voor het laadscherm (3 calls per
        // preview), krap genoeg om hameren te stoppen.
        RateLimiter::for('preview-ai', fn (Request $request) => Limit::perMinute(12)->by($request->ip()));

        // Bookkeeping audit log: every create/update/delete on these models
        // writes to bookkeeping_audit_log via the observer.
        BookkeepingTransaction::observe(BookkeepingAuditObserver::class);
        BookkeepingRelation::observe(BookkeepingAuditObserver::class);
        BookkeepingCategory::observe(BookkeepingAuditObserver::class);
        BookkeepingVatRate::observe(BookkeepingAuditObserver::class);
        BookkeepingInvoice::observe(BookkeepingAuditObserver::class);
    }
}
