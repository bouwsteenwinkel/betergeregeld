<?php

/**
 * Mapping service-slug → blog-category-slugs voor internal linking
 * (PageRank-flow + visitor-retention). Bij empty match → geen widget.
 * Tag-array per service: post moet minstens één matching tag/category
 * hebben om als "verwant" te tellen.
 */
return [
	'cookie-banner-instellen' => [
		'categories' => ['avg-privacy', 'compliance'],
		'tags'       => ['cookies', 'consent', 'avg'],
	],
	'mail-beveiliging-fix' => [
		'categories' => ['security'],
		'tags'       => ['spf', 'dkim', 'dmarc', 'mail-security'],
	],
	'toegang-check' => [
		'categories' => ['access-reviews', 'toegangsbeheer'],
		'tags'       => ['access-review', 'admin-rollen', 'access-matrix'],
	],
	'website-meertalig-maken' => [
		'categories' => ['compliance'],
		'tags'       => ['meertalig', 'hreflang', 'i18n'],
	],
	'2fa-implementeren' => [
		'categories' => ['security', 'toegangsbeheer'],
		'tags'       => ['2fa', 'mfa', '1password', 'admin'],
	],
	'seo-check' => [
		'categories' => ['security'],
		'tags'       => ['seo', 'sitemap', 'canonical'],
	],
	'website-snelheid-verbeteren' => [
		'categories' => [],
		'tags'       => ['performance', 'cache', 'lighthouse'],
	],
	'website-onderhoud-uitbesteden' => [
		'categories' => ['security'],
		'tags'       => ['onderhoud', 'updates'],
	],
	'website-beveiligen' => [
		'categories' => ['security'],
		'tags'       => ['beveiliging', '2fa', 'audit'],
	],
	'website-backup-en-herstel' => [
		'categories' => ['security'],
		'tags'       => ['backup', 'restore', 'archivering'],
	],
	'wordpress-opschonen' => [
		'categories' => ['security'],
		'tags'       => ['wordpress', 'opschonen', 'performance'],
	],
	'website-migratie-zonder-gedoe' => [
		'categories' => [],
		'tags'       => ['migratie', 'hosting'],
	],
	'website-structuur-check' => [
		'categories' => [],
		'tags'       => ['sitemap', 'structuur', 'seo'],
	],
];
