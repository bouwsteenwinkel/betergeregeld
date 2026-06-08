<?php

return [
	/*
	 * Google Safe Browsing Lookup API-key. Zonder key wordt de Safe
	 * Browsing-check overgeslagen (status 'unknown'). Aanmaken in Google Cloud
	 * Console → Safe Browsing API inschakelen → API-key. Via config zodat de
	 * key na config:cache blijft werken.
	 */
	'safe_browsing_key' => env('SAFE_BROWSING_KEY'),

	/*
	 * DNS-blacklists. Domein-gebaseerd (op de host) en IP-gebaseerd (op het
	 * opgeloste A-record). Een hit betekent dat de site/het IP op een spam- of
	 * malware-lijst staat.
	 */
	'dnsbl' => [
		'domain' => ['dbl.spamhaus.org', 'multi.surbl.org'],
		'ip'     => ['zen.spamhaus.org'],
	],

	/*
	 * Crawl-limieten voor de broken-links/mixed-content-check (kostenbeheersing).
	 */
	'crawl' => [
		'max_links' => 40,   // max aantal unieke links waarvan we de status checken
		'timeout'   => 10,   // seconden per HTTP-check
	],

	/*
	 * Projecten (paden) waarop `composer audit` / `npm audit` draait. Alleen
	 * zinvol voor sites/apps met code-toegang (eigen Laravel/Node-projecten op
	 * de server) — niet voor kale WordPress. Komma-gescheiden via env, of pas
	 * de default aan. Standaard: alleen deze app zelf.
	 */
	'audit_paths' => array_values(array_filter(array_map(
		'trim',
		explode(',', (string) env('SECURITY_AUDIT_PATHS', base_path())),
	))),
];
