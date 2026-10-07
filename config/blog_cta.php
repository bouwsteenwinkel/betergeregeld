<?php

/**
 * Doorverwijzing onder een blogpost naar de gratis tool of dienst die bij het onderwerp hoort.
 *
 * Waarom (GSC 07-10-2026): de blog draagt vrijwel al het zoekverkeer van betergeregeld.com (~590 posts),
 * maar een post linkte alleen naar andere posts. Wie las over een IBAN-controle, kwam de IBAN-check nooit
 * tegen. Regels worden van boven naar beneden gelezen; de eerste die past wint. Een regel past als de slug
 * op 'slug' matcht (regex) of de categorie in 'categories' staat. Geen match = geen blok.
 *
 * Teksten: gewone taal, geen em-dashes, geen marketingkreten (zie de AI-tell-afspraak).
 */
return [
	[
		'slug' => '/iban|rekeningnummer|bankrekening/',
		'title' => 'Klopt dat rekeningnummer bij die naam?',
		'text' => 'Met de gratis IBAN-check zie je of een rekeningnummer bij de opgegeven naam hoort, voordat je betaalt.',
		'url' => '/nl/tools/iban-check',
		'button' => 'IBAN controleren',
	],
	[
		'slug' => '/btw|vies|vat-/',
		'title' => 'BTW-nummer van een klant controleren',
		'text' => 'Zoek een Europees BTW-nummer op in VIES en zie direct of het geldig is en bij welk bedrijf het hoort.',
		'url' => '/nl/tools/vat-check',
		'button' => 'BTW-nummer controleren',
	],
	[
		'slug' => '/spf|dkim|dmarc|mail-?beveilig|spoof|afzender/',
		'title' => 'Hoe staat jouw e-mail ervoor?',
		'text' => 'De gratis checker laat zien of SPF, DKIM en DMARC van je domein goed staan. Klopt er iets niet, dan regelen wij het.',
		'url' => '/nl/tools/mail-auth-check',
		'button' => 'Domein controleren',
	],
	[
		'slug' => '/ssl|https|certificaat/',
		'title' => 'Certificaat van je website controleren',
		'text' => 'Zie in een paar seconden of het certificaat van je site geldig is en wanneer het verloopt.',
		'url' => '/nl/tools/ssl-check',
		'button' => 'Certificaat controleren',
	],
	[
		'slug' => '/backup|back-up|herstel|ransomware|gegevensverlies/',
		'title' => 'Weet je zeker dat je back-up werkt?',
		'text' => 'Beantwoord vijf vragen en je krijgt binnen vijf werkdagen een kort rapport over je back-ups. Gratis.',
		'url' => '/nl/backup-check',
		'button' => 'Gratis backup-check',
	],
	[
		'slug' => '/telefoon|telefonie|bereikbaar|spookoproep|voicemail|gemiste-oproep/',
		'title' => 'Nooit meer een klant die niemand aan de lijn krijgt',
		'text' => 'Onze AI-telefoniste neemt op als jij niet kunt, beantwoordt vragen en zet afspraken in je agenda.',
		'url' => '/nl/ai-telefoniste',
		'button' => 'Zo werkt het',
	],
	[
		'slug' => '/cookie|consent|tracking/',
		'title' => 'Cookiebanner die wel klopt',
		'text' => 'Wij stellen je cookiebanner zo in dat hij voldoet aan de regels en je statistieken blijven werken.',
		'url' => '/nl/diensten/cookie-banner-instellen',
		'button' => 'Bekijk de dienst',
	],
	[
		'categories' => ['pdf-redaction'],
		'slug' => '/pdf|zwart|redact|anonimis|lakken/',
		'title' => 'Gevoelige gegevens echt onleesbaar maken',
		'text' => 'Met onze PDF-tool lak je tekst zo weg dat hij ook met kopiëren of zoeken niet meer terug te halen is.',
		'url' => '/nl/tools/pdf-redact',
		'button' => 'Naar de PDF-tool',
	],
	[
		'categories' => ['access-reviews', 'toegangsbeheer', 'offboarding', 'm365-entra'],
		'slug' => '/toegang|access|offboard|onboard|wachtwoord|account|rechten/',
		'title' => 'Wie heeft er nog toegang?',
		'text' => 'Met de toegang check zien we samen welke accounts en rechten er nog openstaan, ook van mensen die al weg zijn.',
		'url' => '/nl/diensten/toegang-check',
		'button' => 'Bekijk de toegang check',
	],
	[
		'categories' => ['boekhouding'],
		'title' => 'Minder handwerk in je administratie',
		'text' => 'Facturen, herinneringen en koppelingen met je boekhoudpakket: wij automatiseren wat nu met de hand gaat.',
		'url' => '/nl/processen-automatiseren',
		'button' => 'Wat kan er automatisch',
	],
	[
		'categories' => ['security', 'compliance', 'avg-privacy', 'tools-uitleg'],
		'title' => 'Gratis checks voor je eigen domein',
		'text' => 'E-mail, certificaat, beveiligingsheaders en DNS: met onze gratis tools zie je in een paar minuten waar het beter kan.',
		'url' => '/nl/tools',
		'button' => 'Naar de gratis tools',
	],
];
