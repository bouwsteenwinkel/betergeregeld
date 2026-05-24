<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bookkeeping:send-invoice-reminders')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('bookkeeping:generate-recurring-invoices')
    ->dailyAt('06:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:sync-directories')
    ->dailyAt('02:30')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:scan-risks')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:build-reminders')
    ->dailyAt('03:15')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('accessguard:send-digests')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping();

// SEO — Google Search Console daily import naar seo_query_daily.
// GSC's 'final' data heeft 2-3 dagen lag, dus exacte uur is niet kritiek.
// 04:30 is een vrij slot tussen radar:sync-vulns (04:15) en radar:scan (05:00).
Schedule::command('seo:import-gsc')
    ->dailyAt('04:30')
    ->onOneServer()
    ->withoutOverlapping();

// PageSpeed Insights — top-3 URLs per property, mobile + desktop. Loopt
// na GSC-import zodat de URL-selectie de meest verse top-clicks pakt.
Schedule::command('seo:run-psi')
    ->dailyAt('05:00')
    ->onOneServer()
    ->withoutOverlapping();

// Dagelijkse blog-generatie via Claude — NL + EN vertaling, direct
// gepubliceerd, met notify-mail naar Dennis voor review. 09:00 zodat
// de mail rond koffietijd binnenkomt.
Schedule::command('blog:generate-daily --skip-if-todays-post')
    ->dailyAt('09:00')
    ->onOneServer()
    ->withoutOverlapping();

// Wekelijkse retry voor backfill-translations en UI-lang-vertalingen.
// Vult op natuurlijke wijze gaten op die ontstaan door:
//   - Claude API-quota-resets (maandelijks)
//   - sporadische tool_use-failures (chunk-1-misses bij lang:translate)
//   - nieuwe NL-posts/UI-keys die nog geen vertaling hebben
// Beide commands zijn idempotent — bestaande vertalingen worden niet opnieuw
// gedaan, alleen gaten worden gevuld.
Schedule::command('blog:backfill-translations en de fr es --delay=0')
    ->weeklyOn(1, '04:00')   // maandag 04:00
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('lang:translate en de fr es')
    ->weeklyOn(1, '03:00')   // maandag 03:00 (vóór de blog-backfill)
    ->onOneServer()
    ->withoutOverlapping();

// Vulnerability Radar — daily catalog + vuln feed refresh, then scan
// every active asset. Order matters: catalog before vulns (so OSV
// queries iterate the new catalog products), vulns before scan
// (so the matcher has fresh data when a scan emits findings).
Schedule::command('radar:sync-catalog')
    ->dailyAt('04:00')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('radar:sync-vulns')
    ->dailyAt('04:15')
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('radar:scan --all-active')
    ->dailyAt('05:00')
    ->onOneServer()
    ->withoutOverlapping(120);

// Security-headers probe — apart van de fingerprint/vuln-scan zodat een
// trage doelsite de queue-worker niet 2 minuten lang vasthoudt. Queue
// mode dispatcht per asset; diff-detect (new vs resolved keys) gaat
// naar het scan-log + raw_output.
Schedule::command('radar:scan-headers --all-active --queue')
    ->dailyAt('05:30')
    ->onOneServer()
    ->withoutOverlapping(60);

// TLS + cookies + CMP — gebundeld in 1 job per asset (RunWebChecksScanJob)
// zodat de queue niet 3× zo veel tickets krijgt. Eigen slot na headers.
Schedule::command('radar:scan-web --all-active --queue')
    ->dailyAt('05:45')
    ->onOneServer()
    ->withoutOverlapping(90);
