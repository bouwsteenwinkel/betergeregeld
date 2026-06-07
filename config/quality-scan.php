<?php

return [

	/*
	 * AI-analyse (Anthropic Claude). Standaard een klein/goedkoop model; per
	 * omgeving instelbaar via QUALITY_SCAN_MODEL. De API-key komt via de
	 * gedeelde AnthropicClient (ANTHROPIC_API_KEY) — nooit hier of in de DB.
	 */
	'ai' => [
		'enabled'     => (bool) env('QUALITY_SCAN_AI', true),
		'model'       => env('QUALITY_SCAN_MODEL', 'claude-haiku-4-5-20251001'),
		'max_tokens'  => 2000,
		'temperature' => 0.2,
	],

	/*
	 * Ophalen van de pagina.
	 */
	'fetch' => [
		'timeout'       => 15,
		'max_redirects' => 3,
		'user_agent'    => 'BeterGeregeld-Monitor/1.0',
	],

	/*
	 * Limieten voor het gedestilleerde input-pakket (kostenbeheersing).
	 */
	'extract' => [
		'max_body_chars'    => 8000,
		'max_package_chars' => 12000,
		'max_images'        => 50,
		'max_links'         => 100,
	],

	/*
	 * Drempelwaarden voor de deterministische checks.
	 */
	'thresholds' => [
		'title_min'         => 30,
		'title_max'         => 60,
		'meta_desc_min'     => 120,
		'meta_desc_max'     => 160,
		'alt_empty_ratio'   => 0.20, // fail als >20% afbeeldingen lege alt heeft
		'alt_generic_count' => 3,    // warn bij ≥3 identieke alt-teksten
	],

	/*
	 * Scoreberekening: start op 'start', trek per finding de weging af.
	 */
	'scoring' => [
		'start'   => 100,
		'min'     => 0,
		'fail'    => ['hoog' => 15, 'middel' => 8, 'laag' => 4],
		'warn'    => 2,
	],

	/*
	 * Verwachte paginataal (voor de lang-attribuut-check).
	 */
	'expected_lang' => 'nl',

	/*
	 * Notificaties: waar alerts heen gaan + bij hoeveel punten scoredaling.
	 */
	'notify_email'      => env('QUALITY_SCAN_ALERT_EMAIL', env('MONITOR_ALERT_EMAIL', 'info@bouwsteenwinkel.nl')),
	'score_drop_alert'  => 15,
];
