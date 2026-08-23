<?php

return [

	/*
	 * The platform tenant whose users are super-admins (full VPS monitoring).
	 * Every other tenant only sees the limited SLA/status view for its own server.
	 * Defaults to the "Beter Geregeld" tenant; override per environment via env.
	 */
	'platform_tenant_id' => env('MONITOR_PLATFORM_TENANT_ID', '019dc47a-f084-70e6-b9d0-6c176421af28'),

	/*
	 * How often the VPS collector pushes a sample (seconds). Drives both the
	 * online/offline status thresholds and the expected-sample math behind the
	 * heartbeat-based uptime/SLA percentage.
	 */
	'interval_seconds' => (int) env('MONITOR_INTERVAL_SECONDS', 60),

	/*
	 * Status thresholds, in seconds since the last agent contact.
	 *   age <= online_within  -> online
	 *   age >= offline_after  -> offline
	 *   in between            -> stale (agent late, not yet declared down)
	 */
	'online_within' => (int) env('MONITOR_ONLINE_WITHIN', 150),
	'offline_after' => (int) env('MONITOR_OFFLINE_AFTER', 600),

	/*
	 * Resource-usage warning thresholds (percent) for table/badge colouring.
	 */
	'cpu_warn'  => (int) env('MONITOR_CPU_WARN', 85),
	'mem_warn'  => (int) env('MONITOR_MEM_WARN', 90),
	'disk_warn' => (int) env('MONITOR_DISK_WARN', 90),

	/*
	 * How long raw metric samples are kept (days). A scheduled prune trims older
	 * rows so the time-series table stays bounded.
	 */
	// Trendwaarschuwingen: niet "drempel bereikt" maar "wanneer bereiken we hem".
	// De schijf liep in een maand van 105 naar 55 GB vrij zonder signaal, omdat
	// disk_warn op 90% staat en het gebruik op 86,5% zat.
	'trend_lookback_days' => (int) env('MONITOR_TREND_LOOKBACK_DAYS', 14),
	'trend_min_days'      => (int) env('MONITOR_TREND_MIN_DAYS', 7),
	'trend_warn_days'     => (int) env('MONITOR_TREND_WARN_DAYS', 45),
	'mem_full_percent'    => (int) env('MONITOR_MEM_FULL_PERCENT', 95),

	'retention_days' => (int) env('MONITOR_RETENTION_DAYS', 30),

	/*
	 * Where offline/disk alert mails go. Defaults to the platform inbox.
	 */
	'alert_email' => env('MONITOR_ALERT_EMAIL', 'info@bouwsteenwinkel.nl'),

	/*
	 * Doorlaatbewijs voor een WAF die de checker blokkeert.
	 *
	 * Aanleiding (31-07-2026): bouwsteenwinkel.nl staat achter Cloudflare en gaf de check
	 * 6.411 keer op rij een 403 terwijl de site gewoon in de lucht was. Nagemeten: dat ligt
	 * niet aan de headers — élke User-Agent krijgt 403, ook een volledige browser-UA, terwijl
	 * curl vanaf dezelfde machine 200 geeft. Cloudflare herkent de TLS-vingerafdruk van de
	 * PHP-client. Uitwijken naar /robots.txt helpt niet: dat serveert Cloudflare uit zijn
	 * edge-cache, dus dan staat de check groen terwijl de server plat ligt.
	 *
	 * De oplossing is een skip-regel aan de Cloudflare-kant die verzoeken mét onderstaande
	 * header doorlaat. Zolang `secret` leeg is stuurt de checker niets extra's mee.
	 *
	 * HOSTS IS GEEN OPTIE MAAR EEN VEILIGHEIDSGRENS. Er worden ook sites van klanten
	 * gemonitord; het geheim mag alleen naar hosts die we zelf beheren, anders deelt de
	 * monitor zijn sleutel met iedereen die hij aanroept. Leeg = naar niemand sturen.
	 * Een entry dekt de host zelf én zijn subdomeinen (bouwsteenwinkel.nl → www.bouwsteenwinkel.nl).
	 */
	'bypass' => [
		'header' => env('MONITOR_BYPASS_HEADER', 'X-BG-Monitor'),
		'secret' => env('MONITOR_BYPASS_SECRET', ''),
		'hosts'  => array_values(array_filter(array_map(
			fn ($h) => strtolower(trim($h)),
			explode(',', (string) env('MONITOR_BYPASS_HOSTS', ''))
		))),
	],

	/*
	 * Interne Laravel-scheduler-jobs die zichzelf bewaken via de cron-monitor
	 * (dead-man's-switch). Elke entry wordt idempotent geprovisioned tot een
	 * cron_monitors-rij (op source_key); de scheduler-hooks in routes/console.php
	 * pingen 'm bij success/failure. period/grace in minuten (1440 = dagelijks).
	 */
	'internal_crons' => [
		['key' => 'seo:import-gsc',                          'name' => 'SEO · GSC-import',                    'period' => 1440, 'grace' => 240, 'website' => 'betergeregeld.com'],
		['key' => 'seo:run-psi',                             'name' => 'SEO · PageSpeed Insights',            'period' => 1440, 'grace' => 240, 'website' => 'betergeregeld.com'],
		['key' => 'bookkeeping:generate-recurring-invoices', 'name' => 'Boekhouding · terugkerende facturen', 'period' => 1440, 'grace' => 120],
		['key' => 'bookkeeping:send-invoice-reminders',      'name' => 'Boekhouding · factuurherinneringen',  'period' => 1440, 'grace' => 120],
		['key' => 'accessguard:scan-risks',                  'name' => 'AccessGuard · risico-scan',           'period' => 1440, 'grace' => 180],
		['key' => 'radar:scan',                              'name' => 'Vulnerability Radar · scan',          'period' => 1440, 'grace' => 180],
		['key' => 'blog:generate-daily',                     'name' => 'Blog · dagelijkse generatie',         'period' => 1440, 'grace' => 180],
		// Draait elk kwartier; 45 min coulance vangt een deploy of een gemiste tik op
		// zonder vals alarm, en slaat aan ruim voordat de eerste dag-van-mail wegvalt.
		['key' => 'appointments:send-reminders',            'name' => 'Afspraken · herinneringen',           'period' => 15,   'grace' => 45],
		// Waakhond — hartslag van de scheduler zélf (pingt elke 30 min). Draait de
		// scheduler niet meer of ligt de VPS plat (bv. slaapstand → "operator refused
		// the request"), dan stopt de ping en slaat 'ie aan: de dead-man's-switch over
		// de cron-machinerie als geheel. Ruime marge zodat een deploy/gemiste tik geen
		// vals alarm geeft. source_key = bsw:waakhond (bestaande, handmatig aangemaakte monitor).
		['key' => 'bsw:waakhond',                            'name' => 'Waakhond · scheduler-hartslag',       'period' => 60,   'grace' => 30],
	],
];
