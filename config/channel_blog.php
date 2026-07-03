<?php

/**
 * Blog-TOPIC-MATRIX voor de trigger-sites, geordend rond de 5 Groeidiamant-
 * producten + fundamentele onderwerpen. Twee gebruiken:
 *
 *  1. AI-batch (`channel:blog:generate`): schrijft per niche één UNIEK artikel per
 *     topic via Claude, écht over die niche + dat product. Draai op prod (key).
 *  2. Fallback-template (`channel:blog:seed` zonder niche-content): trade-token
 *     versie, geen API — snelle generieke vulling.
 *
 * Per niche kun je ook een handgeschreven set leveren in config/blog_{channel}.php
 * (slug => [title, excerpt, body]); die wint dan bij het seeden (zie badkamer).
 *
 * Tokens in title/angle: :trade :trades :niche :niches
 */

return [

    // ── De topic-matrix (voor de AI-batch) ────────────────────────────────
    'topics' => [
        // Fundamenteel
        ['slug' => 'online-gevonden-worden-in-je-regio',   'product' => 'website',        'title' => 'Zo word je als :trade online gevonden in je regio', 'angle' => 'lokale vindbaarheid in Google, plaatsen noemen, zoeken zoals de klant'],
        ['slug' => 'wat-kost-een-website',                 'product' => 'website',        'title' => 'Wat kost een website voor een :trade?',            'angle' => 'kosten/waarde, vast maandbedrag, wat het oplevert, geen verrassingen'],
        ['slug' => 'website-of-social-media',              'product' => 'website',        'title' => 'Website of alleen social media voor je :trade?',   'angle' => 'eigen site vs afhankelijkheid platform, combineren, vindbaarheid'],
        ['slug' => 'reviews-goud-waard',                   'product' => 'website',        'title' => 'Waarom reviews goud waard zijn voor een :trade',   'angle' => 'sociale bewijskracht, reviews verzamelen en tonen, vertrouwen'],
        ['slug' => 'meer-aanvragen-binnenhalen',           'product' => 'website',        'title' => 'Meer offerteaanvragen binnenhalen als :trade',     'angle' => 'drempel verlagen, snel reageren, duidelijke CTA, korte formulieren'],
        // Website
        ['slug' => 'wat-klanten-zoeken-op-je-website',     'product' => 'website',        'title' => '5 dingen die klanten zoeken op de website van een :trade', 'angle' => 'werk in beeld, prijs/aanpak, reviews, contact, regio'],
        ['slug' => 'vakwerk-in-beeld-fotos',               'product' => 'website',        'title' => 'Je vakwerk in beeld: foto\'s die klanten overtuigen', 'angle' => 'fotografie van afgeleverd werk, before/after, portfolio'],
        ['slug' => 'verouderde-website-kost-klussen',      'product' => 'website',        'title' => 'Waarom een verouderde website je klussen kost',    'angle' => 'eerste indruk, mobiel, snelheid, concurrent die er beter uitziet'],
        // Webshop
        ['slug' => 'loont-een-webshop',                    'product' => 'webshop',        'title' => 'Loont een webshop voor je :trade?',                'angle' => 'producten online verkopen naast je werk, 24/7 open, iDEAL'],
        ['slug' => 'online-verkopen-naast-je-werk',        'product' => 'webshop',        'title' => 'Online verkopen naast je :niche-werk',             'angle' => 'extra omzet, showroom online, bezorgen of afhalen'],
        ['slug' => 'montage-bijverkopen',                  'product' => 'webshop',        'title' => 'Montage of plaatsing bijverkopen via je webshop',  'angle' => 'product + dienst combineren, meer marge, upsell'],
        // Klantenportaal
        ['slug' => 'klanten-project-laten-volgen',         'product' => 'klantenportaal', 'title' => 'Laat klanten hun project zelf volgen',             'angle' => 'eigen omgeving, planning en voortgang, minder onzekerheid'],
        ['slug' => 'minder-gebel-over-planning',           'product' => 'klantenportaal', 'title' => 'Minder gebel over de planning met een klantenportaal', 'angle' => 'klant ziet planning zelf, scheelt telefoontjes, tijdwinst'],
        ['slug' => 'afspraken-zelf-laten-inplannen',       'product' => 'klantenportaal', 'title' => 'Afspraken die klanten zelf inplannen',             'angle' => 'online agenda, 24/7 inplannen, minder heen-en-weer'],
        // Automatisering
        ['slug' => 'offertes-sneller-maken',               'product' => 'automatisering', 'title' => 'Offertes in 10 minuten in plaats van een avond',   'angle' => 'standaardposten, sjablonen, sneller versturen, meer scoren'],
        ['slug' => 'facturen-en-herinneringen-automatisch','product' => 'automatisering', 'title' => 'Facturen en herinneringen die zichzelf versturen',  'angle' => 'automatisch factureren, betaalherinneringen, cashflow'],
        ['slug' => 'koppel-agenda-boekhouding-website',    'product' => 'automatisering', 'title' => 'Koppel je website, agenda en boekhouding',          'angle' => 'geen dubbel werk, systemen die samenwerken, minder fouten'],
        // AI
        ['slug' => 'nooit-meer-een-aanvraag-missen',       'product' => 'ai',             'title' => 'Nooit meer een aanvraag missen met een AI-assistent', 'angle' => 'telefoon/chat 24/7 aannemen, buiten kantoortijd, doorvragen'],
        ['slug' => 'ai-bereidt-je-offerte-voor',           'product' => 'ai',             'title' => 'AI die je offerte voorbereidt',                    'angle' => 'uit foto\'s/vragen een eerste inschatting, jij houdt controle'],
        ['slug' => 'automatisch-reviews-verzamelen',       'product' => 'ai',             'title' => 'Automatisch reviews verzamelen na oplevering',     'angle' => 'na de klus automatisch om een Google-review vragen'],
        // Groei
        ['slug' => 'groeien-van-website-tot-ai',           'product' => 'groei',          'title' => 'Groeien met je :trade: van website tot AI',        'angle' => 'de Groeidiamant, stap voor stap uitbreiden, nooit opnieuw beginnen'],
    ],
];
