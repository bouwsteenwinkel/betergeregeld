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
		'title' => 'IBAN controleren voordat je betaalt',
		'text' => 'De gratis IBAN-check laat zien of een rekeningnummer geldig is en of naam en nummer op risico wijzen.',
		'url' => '/nl/tools/iban-check',
		'button' => 'IBAN controleren',
	],
	[
		'slug' => '/btw|vies|vat-/',
		'title' => 'BTW-nummer van een klant controleren',
		'text' => 'Controleer een Europees BTW-nummer in VIES en zie direct of het geldig is. Er wordt niets opgeslagen.',
		'url' => '/nl/tools/vat-check',
		'button' => 'BTW-nummer controleren',
	],
	[
		'slug' => '/spf|dkim|dmarc|mail-?beveilig|spoof|afzender/',
		'title' => 'Hoe staat jouw e-mail ervoor?',
		'text' => 'De gratis checker haalt SPF, DKIM en DMARC van je domein op en geeft per record verbeterpunten. Wil je het laten oplossen, dan doen wij dat.',
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
		'text' => 'Vertel in twee minuten wat je nu hebt. Binnen vijf werkdagen krijg je een kort rapport en eerlijk advies. Gratis en zonder verplichting.',
		'url' => '/nl/backup-check',
		'button' => 'Gratis backup-check',
	],
	[
		'categories' => ['bereikbaarheid'],
		'slug' => '/telefoon|telefonie|telefonist|terugbel|bereikbaar|spookoproep|voicemail|gemiste-oproep/',
		'title' => 'Geen gemiste bellers meer',
		'text' => 'Onze AI-telefoniste neemt op als jij niet kunt, beantwoordt vragen uit je eigen kennisbank, verbindt door of noteert een terugbelverzoek, en stuurt na elk gesprek een verslag.',
		'url' => '/nl/ai-telefoniste',
		'button' => 'Zo werkt het',
	],
	[
		'slug' => '/cookie|consent|tracking/',
		'title' => 'Cookiebanner die wel klopt',
		'text' => 'Wij richten CookieGeregeld voor je in, met een logische indeling en hulp bij scripts zoals GA4 en pixels.',
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
		'text' => 'Met een toegang check krijg je overzicht van accounts, rechten en leveranciers, en zie je welke toegang nog openstaat terwijl dat niet hoort.',
		'url' => '/nl/diensten/toegang-check',
		'button' => 'Bekijk de toegang check',
	],
	[
		'categories' => ['boekhouding', 'automatiseren'],
		'title' => 'Minder handwerk in je administratie',
		'text' => 'Betalingsherinneringen, opvolging en vaste rapportages: wat nu met de hand gaat, laten we volgens vaste regels automatisch lopen.',
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
