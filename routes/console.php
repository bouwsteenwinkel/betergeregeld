<?php

use App\Services\Monitor\CronMonitorPinger;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// VPS-monitoring — prune metric-samples ouder dan config('monitor.retention_days').
// 02:00 is een rustig slot vóór de bookkeeping/radar-jobs.
Schedule::command('monitor:prune-metrics')
    ->dailyAt('02:00')
    ->onOneServer()
    ->withoutOverlapping();

// Self-service voorbeeldsites — verlopen, niet-opgeëiste previews opruimen.
Schedule::command('channel:previews-cleanup')
    ->hourly()
    ->onOneServer()
    ->withoutOverlapping();

// Sessiebestanden opruimen. Stond standaard IN het verzoek (session.lottery); dat
// kostte de ongelukkige bezoeker een wachttijd van meer dan een minuut zodra de
// map groot werd. Hier draait het waar niemand op wacht.
Schedule::command('sessions:prune')
    ->hourly()
    ->onOneServer()
    ->withoutOverlapping();

// Opgeslagen voorbeelden — reminder-mails (dag 1 en dag 4) naar klanten die nog
// niets lieten horen; stopt bij opt-out of zodra het team de lead oppakt.
Schedule::command('previews:send-reminders')
    ->dailyAt('09:00')
    ->onOneServer()
    ->withoutOverlapping();

// Afspraak-herinneringen (2 dagen vooraf en de ochtend van de afspraak) tegen no-show
// op het gratis gesprek.
// Bewust NIET dailyAt zoals de previews-reminder hierboven: die stuurt "ergens op
// dag 1", een paar uur speling maakt daar niets uit. Hier hangt het bericht aan een
// tijdstip, en 1x per dag draaien zou een afspraak van 09:00 een "herinnering" van
// 24 tot 48 uur vooraf geven. Elk kwartier houdt de afwijking onder de 15 minuten; het
// command is idempotent (kolommen op de afspraak), dus vaak draaien kost alleen een
// lege query.
//
// withoutOverlapping(10): zonder vervaltijd houdt Laravel de lock 24 uur vast, dus één
// hard afgebroken run (deploy, IIS-recycle) legde alle herinneringen een etmaal stil —
// precies lang genoeg om elke afspraak van die dag te missen. Het command draait in
// seconden, dus 10 minuten is ruim.
//
// De monitor is hier geen luxe: dit command draait ongezien op de achtergrond, en als
// het stilvalt merkt niemand iets. Je ziet het pas aan de no-shows.
CronMonitorPinger::watch(
    Schedule::command('appointments:send-reminders')
        ->everyFifteenMinutes()
        ->onOneServer()
        ->withoutOverlapping(10),
    'appointments:send-reminders'
);

// VPS-monitoring — mail bij offline/volle-schijf-overgangen (alleen bij wijziging).
Schedule::command('monitor:check-alerts')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

// VPS-monitoring — HTTP/TCP-uptimechecks uitvoeren (voedt de SLA per server).
Schedule::command('monitor:run-checks')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

// SocketLabs-mailmonitoring — vangnet-poll van de v2 API (realtime gaat via de
// event-webhooks; alerts lopen via monitor:check-alerts, dimensie 'api').
Schedule::command('monitor:socketlabs-poll')
    ->everyFifteenMinutes()
    ->onOneServer()
    ->withoutOverlapping();

// Cron-monitoring — mail bij overgangen (ok/late/failed) van bewaakte cron-jobs.
Schedule::command('cron:check-monitors')
    ->everyFiveMinutes()
    ->onOneServer()
    ->withoutOverlapping();

// Cron-monitoring — zorg dat de interne jobs als monitor bestaan (idempotent).
Schedule::command('cron:provision-internal')
    ->dailyAt('00:05')
    ->onOneServer()
    ->withoutOverlapping();

// AI-kwaliteitscheck — dispatch scans voor pagina's die toe zijn aan een nieuwe run.
Schedule::command('quality-scan:dispatch-due')
    ->dailyAt('06:30')
    ->onOneServer()
    ->withoutOverlapping();

// De onderstaande dagelijkse jobs bewaken zichzelf: CronMonitorPinger::watch()
// hangt success/failure-hooks aan de taak en pingt de bijbehorende cron-monitor
// (config('monitor.internal_crons')) in-process. Zo zien we het meteen als een
// van deze jobs faalt of stilvalt — zonder externe ping-URL.
CronMonitorPinger::watch(
    Schedule::command('bookkeeping:send-invoice-reminders')
        ->dailyAt('08:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'bookkeeping:send-invoice-reminders'
);

CronMonitorPinger::watch(
    Schedule::command('bookkeeping:generate-recurring-invoices')
        ->dailyAt('06:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'bookkeeping:generate-recurring-invoices'
);

Schedule::command('accessguard:sync-directories')
    ->dailyAt('02:30')
    ->onOneServer()
    ->withoutOverlapping();

CronMonitorPinger::watch(
    Schedule::command('accessguard:scan-risks')
        ->dailyAt('03:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'accessguard:scan-risks'
);

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
CronMonitorPinger::watch(
    Schedule::command('seo:import-gsc')
        ->dailyAt('04:30')
        ->onOneServer()
        ->withoutOverlapping(),
    'seo:import-gsc'
);

// PageSpeed Insights — top-3 URLs per property, mobile + desktop. Loopt
// na GSC-import zodat de URL-selectie de meest verse top-clicks pakt.
CronMonitorPinger::watch(
    Schedule::command('seo:run-psi')
        ->dailyAt('05:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'seo:run-psi'
);

// Rankdata — maandelijkse bureaufacturen (concept) op de 1e van de maand.
// Blijven 'draft' zodat ze nagekeken worden vóór verzending.
CronMonitorPinger::watch(
    Schedule::command('rankdata:invoice-bureaus')
        ->monthlyOn(1, '06:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'rankdata:invoice-bureaus'
);

// Security (fase 1) — malware/blacklist + mixed content/broken links per site.
// Wekelijks; de crawl is de zwaarste stap. Alerts bij blacklist/malware lopen
// via monitor:check-alerts (transitie-gebaseerd).
CronMonitorPinger::watch(
    Schedule::command('security:scan')
        ->weeklyOn(1, '05:30')
        ->onOneServer()
        ->withoutOverlapping(),
    'security:scan'
);

// Security (fase 2) — dagelijkse import van de Wordfence vuln-feed (lokale cache
// waartegen de gepushte site-software gematcht wordt).
CronMonitorPinger::watch(
    Schedule::command('security:import-vulns')
        ->dailyAt('04:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'security:import-vulns'
);

// Security (fase 3) — wekelijkse composer/npm dependency-audit op de eigen
// code-projecten (config security.audit_paths).
CronMonitorPinger::watch(
    Schedule::command('security:audit-deps')
        ->weeklyOn(1, '05:45')
        ->onOneServer()
        ->withoutOverlapping(),
    'security:audit-deps'
);

// Rankdata — maandelijks PDF-klantrapport mailen op de 1e (na de bureaufacturen).
CronMonitorPinger::watch(
    Schedule::command('rankdata:send-reports')
        ->monthlyOn(1, '07:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'rankdata:send-reports'
);

// Dagelijkse blog-generatie via Claude — NL + EN vertaling, direct
// gepubliceerd, met notify-mail naar Dennis voor review. 09:00 zodat
// de mail rond koffietijd binnenkomt.
CronMonitorPinger::watch(
    Schedule::command('blog:generate-daily --skip-if-todays-post')
        ->dailyAt('09:00')
        ->onOneServer()
        ->withoutOverlapping(),
    'blog:generate-daily'
);

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

CronMonitorPinger::watch(
    Schedule::command('radar:scan --all-active')
        ->dailyAt('05:00')
        ->onOneServer()
        ->withoutOverlapping(120),
    'radar:scan'
);

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

// Channel-sites: dagelijks per kanaal een branche-specifiek blog-CONCEPT (Claude).
// Komt binnen als published_at = NULL + ai_generated = true; mens controleert en
// publiceert handmatig in de admin (geen auto-publish).
Schedule::command('channels:blog --all --write')
    ->dailyAt('07:30')
    ->onOneServer()
    ->withoutOverlapping();

// Waakhond — hartslag van de scheduler zelf. Pingt elke 30 min IN-PROCESS (geen
// withoutOverlapping/onOneServer, zodat deze heartbeat nooit op een vastgelopen
// slot kan blijven hangen). Draait de scheduler niet meer of ligt de VPS plat,
// dan stopt de ping en slaat de dead-man's-switch aan (period 60 + grace 30 min).
Schedule::call(fn () => CronMonitorPinger::record('bsw:waakhond', 'success'))
    ->everyThirtyMinutes()
    ->name('waakhond-heartbeat');
