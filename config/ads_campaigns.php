<?php

/**
 * Campagne-profielen voor de Google Ads-motor (GoogleAdsManager + admin).
 *
 * Eén profiel per bedrijf/aanbod: zoekwoorden, uitsluitingen, RSA-teksten en
 * extensies. Zo maakt de admin ("Nieuwe campagne") én de CLI (ads:create-campaign
 * --profile=…) een complete Search-campagne uit hetzelfde vaste recept.
 *
 * Alle campagnes draaien in hetzelfde Google Ads-account (GOOGLE_ADS_CUSTOMER_ID),
 * dus ze verschijnen sowieso in het admin-overzicht.
 *
 * Grenzen (Google): kop ≤30 tekens, beschrijving ≤90, sitelink-tekst ≤25 +
 * 2 beschrijvingen ≤35, callout ≤25, fragment-waarde ≤25. Fragment-kop moet uit
 * Google's vaste lijst komen ("Types" is universeel geldig).
 */

return [

    'bedrijfswebsite' => [
        'label'      => 'Jouw Bedrijfswebsite',
        'name'       => 'bedrijfswebsite',
        'final_url'  => 'https://jouw-bedrijfswebsite.nl',
        'budget'     => 25,
        'max_cpc'    => 1.5,
        'paths'      => ['voorbeeld', 'gratis'],

        'ad_groups'  => [
            'Website laten maken' => [
                ['website laten maken', 'PHRASE'],
                ['bedrijfswebsite laten maken', 'PHRASE'],
                ['website voor mijn bedrijf', 'PHRASE'],
                ['zzp website laten maken', 'PHRASE'],
                ['website laten maken', 'EXACT'],
            ],
            'Betaalbare website' => [
                ['goedkope website laten maken', 'PHRASE'],
                ['betaalbare website', 'PHRASE'],
                ['simpele website laten maken', 'PHRASE'],
                ['website voor ondernemers', 'PHRASE'],
            ],
        ],

        'negatives' => [
            'gratis website maken', 'zelf website maken', 'wordpress', 'wix', 'squarespace',
            'shopify', 'template', 'cursus', 'opleiding', 'vacature', 'baan', 'stage',
            'betekenis', 'download',
        ],

        'headlines' => [
            'Gratis voorbeeld in 1 minuut', 'Zie nu jouw nieuwe website', 'Website laten maken?',
            'Vaste prijs, geen verrassingen', 'Eén vaste contactpersoon', 'Professioneel & betaalbaar',
            'In heel Nederland geregeld', 'Telefonisch snel geregeld', 'Voor ondernemers & zzp',
            'Website in jouw eigen stijl', 'Eerst zien, dan beslissen', 'Klaar terwijl je kijkt',
            'Geen technische kennis nodig', 'Meer klanten via je website', 'Snel online, zonder gedoe',
        ],

        'descriptions' => [
            'Vul kort je gegevens in en zie in 1 minuut een gratis voorbeeld van jouw website.',
            'Professionele site voor ondernemers. Gratis voorbeeld, daarna pas beslissen.',
            "Geen gedoe: we maken 'm samen af in een korte, vrijblijvende videoafspraak.",
            'Bekijk vrijblijvend hoe jouw bedrijfswebsite eruit kan zien. Start nu gratis.',
        ],

        'sitelinks' => [
            ['Direct een voorbeeld', '/voorbeeld-maken', 'Zie in 1 minuut je site', 'Gratis en vrijblijvend'],
            ['Plan een gesprek', '/afspraak', 'Online of telefonisch', 'Vrijblijvend advies'],
            ['Zo werkt het', '#werkwijze', 'In een paar stappen online', 'Met een vaste contactpersoon'],
            ['Prijzen', '#prijzen', 'Duidelijke vaste prijs', 'Geen verrassingen achteraf'],
        ],

        'callouts' => [
            'Gratis voorbeeld', 'In 1 minuut klaar', 'Vaste prijs', 'Vaste contactpersoon',
            'Voor zzp & mkb', 'Heel Nederland',
        ],

        'snippet' => ['header' => 'Types', 'values' => ['Bedrijfswebsite', 'Webshop', 'Onderhoud', 'Vindbaar in Google']],

        'call_phone' => '088 2545101',
    ],

    'bouwsteenwinkel' => [
        'label'      => 'Bouwsteenwinkel — LEGO huren',
        'name'       => 'bouwsteenwinkel',
        'final_url'  => 'https://bouwsteenwinkel.nl/lego-huren',
        'budget'     => 25,
        // LEGO-verhuur is concurrerend; iets ruimer plafond om mee te doen.
        'max_cpc'    => 2.0,
        'paths'      => ['lego-huren'],

        'ad_groups'  => [
            'LEGO huren' => [
                ['lego huren', 'PHRASE'],
                ['lego sets huren', 'PHRASE'],
                ['lego set huren', 'PHRASE'],
                ['lego huren', 'EXACT'],
            ],
            'LEGO verhuur & abonnement' => [
                ['lego verhuur', 'PHRASE'],
                ['lego abonnement', 'PHRASE'],
                ['lego pakket huren', 'PHRASE'],
            ],
        ],

        'negatives' => [
            'gratis', 'tweedehands', 'marktplaats', 'vacature', 'baan', 'stage',
            'review', 'betekenis', 'bouwtekening', 'minifiguren',
        ],

        'headlines' => [
            'LEGO huren doe je hier', 'Grote sets, kleine prijs', 'LEGO sets huren & bouwen',
            'Altijd wat nieuws te bouwen', 'Huren, bouwen, terugsturen', 'Voor jong en oud',
            'Compleet & schoongemaakt', 'Snel bij je thuis', 'Geen dure aankoop',
            'Duizenden sets op voorraad', 'Bouwplezier zonder rommel', 'Ruim LEGO-assortiment',
            'Makkelijk online huren', 'Klaar? Kies een nieuwe set', 'LEGO-verhuurspecialist',
        ],

        'descriptions' => [
            'Huur complete LEGO-sets, gebouwd en gecontroleerd. Klaar? Retour en kies een nieuwe.',
            'Duizenden sets voor jong en oud. Geen dure aankoop, geen rommel — puur bouwplezier.',
            'Makkelijk online huren en snel in huis. Compleet, schoongemaakt en op voorraad.',
            'Van kleine tot grote sets. Bouwsteenwinkel, dé specialist in LEGO verhuur.',
        ],

        // Echte rd01-slugs (themes/bouwsteenwinkel_rd01/page-*.php); paden zijn
        // t.o.v. het domein-root. Geverifieerd tegen de clean-path-router in index.php.
        'sitelinks' => [
            ['Bekijk alle sets', '/sets', 'Duizenden sets', 'Van klein tot groot'],
            ['Hoe werkt huren?', '/hoe-werkt-huren', 'Huren in 3 stappen', 'Simpel en snel'],
            ['Lidmaatschap', '/lidmaatschap', 'Onbeperkt blijven bouwen', 'Vast bedrag per maand'],
            ['Huren of kopen?', '/huren-vs-kopen', 'Zie wat voordeliger is', 'Reken het zelf uit'],
        ],

        'callouts' => [
            'LEGO huren', 'Compleet & schoon', 'Snel in huis', 'Voor jong en oud',
            'Geen dure aankoop', 'Retour = nieuwe set',
        ],

        'snippet' => ['header' => 'Types', 'values' => ['Kleine sets', 'Grote sets', 'Themasets', 'Voor bedrijven']],

        'call_phone' => '035 201 1720',
    ],

];
