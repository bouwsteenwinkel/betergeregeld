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
 *
 * RECEPT VOOR EEN NIEUW PROFIEL (goede Advertentiekwaliteit — geleerd 2026-07-20):
 *  - Koppen PER advertentiegroep via 'ad_group_headlines' (map op groepsnaam), zodat
 *    elke groep de eigen zoekwoorden in de koppen heeft. Eén gedeelde set voor meerdere
 *    groepen = "Slecht" voor de groep die het minst matcht. Terugval op 'headlines'.
 *  - Idem 'ad_group_descriptions' als Google om zoekwoorden in de beschrijvingen vraagt.
 *  - Geef ~12 UNIEKE koppen per groep; vermijd bijna-dubbele varianten (Google straft
 *    lage variatie af). Niet pinnen (pin_h1 = 0) tenzij echt nodig.
 *  - Minstens ~8 sitelinks; verifieer elke slug eerst (curl met VOLLEDIGE user-agent —
 *    bouwsteenwinkel.nl 403't korte UA's via Cloudflare, wat als "kapot" oogt).
 *  - Fragment ('snippet'): GEEN oplopende leeftijdsladder (kleuters→tieners→volwassenen);
 *    Google's classifier vlagt dat als seksueel expliciet. Gebruik product-/set-types.
 *  - Merknamen van concurrenten NOOIT in de advertentietekst (wel als zoekwoord).
 *
 * Live campagne bijwerken naar het profiel (status/historie blijven): `ads:sync
 * {campagne} --profile=…` (advertenties + sitelinks + fragment; --validate om te toetsen).
 * Een gepauzeerde campagne met 0 vertoningen mag je gewoon opnieuw opbouwen.
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

        // Koppen per advertentiegroep, zodat elke groep de eigen zoekwoorden dekt
        // (laten-maken vs betaalbaar). Groepen zonder eigen set vallen terug op 'headlines'.
        'ad_group_headlines' => [
            'Website laten maken' => [
                'Website laten maken?', 'Bedrijfswebsite laten maken', 'Professionele bedrijfswebsite',
                'ZZP-website laten maken', 'Website voor jouw bedrijf', 'Gratis voorbeeld in 1 minuut',
                'Eerst zien, dan beslissen', 'Vaste prijs, geen verrassingen', 'Eén vaste contactpersoon',
                'Snel online, zonder gedoe', 'Klaar terwijl je kijkt', 'Meer klanten via je site',
                'In heel Nederland geregeld', 'Website binnen een week', 'Nieuwe website laten maken',
            ],
            'Betaalbare website' => [
                'Betaalbare website', 'Goedkope website laten maken', 'Simpele website laten maken',
                'Website voor ondernemers', 'Betaalbaar en professioneel', 'Vaste prijs, geen verrassingen',
                'Gratis voorbeeld in 1 minuut', 'Website voor zzp en mkb', 'Eerst zien, dan beslissen',
                'Professioneel en betaalbaar', 'Snel online, zonder gedoe', 'Geen technische kennis nodig',
                'Betaalbare bedrijfswebsite', 'Website zonder hoge kosten', 'In heel Nederland geregeld',
            ],
        ],

        'sitelinks' => [
            ['Direct een voorbeeld', '/voorbeeld-maken', 'Zie in 1 minuut je site', 'Gratis en vrijblijvend'],
            ['Plan een gesprek', '/afspraak', 'Online of telefonisch', 'Vrijblijvend advies'],
            ['Zo werkt het', '#werkwijze', 'In een paar stappen online', 'Met een vaste contactpersoon'],
            ['Prijzen', '#prijzen', 'Duidelijke vaste prijs', 'Geen verrassingen achteraf'],
            ['Ook een webshop', '/webshop', 'Verkopen via je site', 'Compleet ingericht'],
            ['Slim automatiseren', '/automatisering', 'Minder handwerk', 'Koppel je systemen'],
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
        'final_url'  => 'https://bouwsteenwinkel.nl/lego-verhuur',
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

        // Niet pinnen (pin_h1 = 0): met 15 sterke, zoekwoord-rijke koppen zet
        // Google zelf de meest relevante kop vooraan (message-match) en test alle
        // combinaties. Pinnen zou de Advertentiekwaliteit naar "Slecht" trekken
        // zonder echte winst. De zoekwoord-koppen staan bewust vooraan in de lijst.
        'pin_h1'    => 0,
        'headlines' => [
            'LEGO huren doe je hier', 'LEGO sets huren & bouwen', 'Dé LEGO-verhuurspecialist',
            'Sets al vanaf €1 per week', 'Abonnement vanaf €10 per jaar', 'Direct online geregeld',
            'Geen dure aankoop', 'Compleet & schoongemaakt', 'Retour = nieuwe set',
            'Voor jong en oud', 'Duizenden sets op voorraad', 'Bouwplezier zonder rommel',
            'Altijd wat nieuws te bouwen', 'Klaar? Kies een nieuwe set', 'Snel bij je thuis',
        ],

        'descriptions' => [
            'Huur LEGO-sets al vanaf €1 per week of een abonnement vanaf €10 per jaar.',
            'Compleet gebouwd en schoongemaakt. Klaar? Stuur retour en kies een nieuwe set.',
            'Duizenden sets voor jong en oud. Geen dure aankoop en geen rommel, puur bouwplezier.',
            'Makkelijk online geregeld. Dé specialist in LEGO huren en verhuur in Nederland.',
        ],

        // Koppen per advertentiegroep (huren vs verhuur/abonnement), zoekwoord-dekkend
        // en diverser. Groepen zonder eigen set vallen terug op 'headlines'.
        'ad_group_headlines' => [
            'LEGO huren' => [
                'LEGO huren doe je hier', 'LEGO sets huren en bouwen', 'Dé LEGO-verhuurspecialist',
                'LEGO huren vanaf €1 per week', 'LEGO set huren, geen kopen', 'Duizenden sets om te huren',
                'Compleet en schoongemaakt', 'Klaar? Kies een nieuwe set', 'Retour is een nieuwe set',
                'Bouwplezier zonder rommel', 'Snel bij je thuis bezorgd', 'Geen dure LEGO-aankoop',
                'LEGO huren in heel Nederland', 'Nieuwe set, elke keer weer', 'Voordelig LEGO huren',
            ],
            'LEGO verhuur & abonnement' => [
                'LEGO verhuur voor iedereen', 'LEGO-abonnement vanaf €10', 'Dé LEGO-verhuurspecialist',
                'LEGO pakket huren', 'Abonnement of los huren', 'LEGO verhuur en abonnement',
                'Sets al vanaf €1 per week', 'Abonnement vanaf €10 per jaar', 'Compleet en schoongemaakt',
                'Duizenden sets op voorraad', 'Bouwplezier zonder rommel', 'Geen dure LEGO-aankoop',
                'LEGO verhuur in Nederland', 'Onbeperkt LEGO met abo', 'Voordelig LEGO verhuur',
            ],
        ],

        // De huur-groep krijgt eigen descriptions met "huren"; de verhuur/abo-groep met
        // "verhuur"/"abonnement". Google vroeg om meer zoekwoorden in de beschrijvingen.
        'ad_group_descriptions' => [
            'LEGO huren' => [
                'Huur LEGO-sets al vanaf €1 per week. Compleet en schoongemaakt, snel bij je thuis.',
                'Dé specialist in LEGO huren in Nederland. Duizenden sets, van klein tot groot.',
                'Klaar met bouwen? Stuur de set retour en huur de volgende. Geen dure aankoop meer.',
                'LEGO sets huren is makkelijk online geregeld. Bouwplezier zonder rommel of gedoe.',
            ],
            'LEGO verhuur & abonnement' => [
                'LEGO verhuur met abonnement vanaf €10 per jaar, of huur los per set vanaf €1 per week.',
                'Kies een LEGO-abonnement en bouw elke keer wat nieuws. Compleet en schoongemaakt.',
                'Dé specialist in LEGO verhuur in Nederland. Duizenden sets op voorraad.',
                'Klaar? Stuur retour en kies een nieuwe set. Geen dure aankoop, puur bouwplezier.',
            ],
        ],

        // Echte rd01-slugs (themes/bouwsteenwinkel_rd01/page-*.php); paden zijn
        // t.o.v. het domein-root. Geverifieerd tegen de clean-path-router in index.php.
        'sitelinks' => [
            ['Bekijk alle sets', '/sets', 'Duizenden sets', 'Van klein tot groot'],
            ['Hoe werkt huren?', '/hoe-werkt-huren', 'Huren in 3 stappen', 'Simpel en snel'],
            ['Lidmaatschap', '/lidmaatschap', 'Al vanaf €10 per jaar', 'Bekijk de voordelen'],
            ['Huren of kopen?', '/huren-vs-kopen', 'Zie wat voordeliger is', 'Reken het zelf uit'],
            ['Klantenservice', '/klantenservice', 'Vragen of hulp nodig?', 'We helpen je snel'],
            ['Cadeaubon kopen', '/cadeaubonnen', 'Verras een bouwfan', 'Direct te besteden'],
            ['Contact', '/contact', 'Bel of mail ons', 'Snel een antwoord'],
            ['In de pers', '/in-de-pers', 'In het nieuws', 'Zo kennen anderen ons'],
        ],

        'callouts' => [
            'Sets vanaf €1 per week', 'Abo vanaf €10 per jaar', 'Compleet & schoongemaakt',
            'Snel in huis', 'Geen dure aankoop', 'Retour = nieuwe set',
        ],

        'snippet' => ['header' => 'Types', 'values' => ['Kleine sets', 'Grote sets', 'Themasets', 'Voor bedrijven']],

        'call_phone' => '035 201 1720',
    ],

    // Directe tegenhanger van Bouwersbende (maandelijkse LEGO-box per leeftijd,
    // €35/mnd). Wij ondercutten met €31,25 en flexibel opzeggen. LET OP: de copy
    // ("Maandelijks opzegbaar") vereist dat het 12-maanden-commitment eruit gaat,
    // én dat de fulfilment (verzending) werkt vóór activatie. Merknaam "Bouwersbende"
    // NOOIT in de advertentietekst (merkrecht) — wel als zoekwoord toegestaan.
    'bouwverrassing' => [
        'label'      => 'Bouwverrassing — LEGO-abonnement',
        'name'       => 'bouwverrassing',
        'final_url'  => 'https://bouwverrassing.nl',
        'budget'     => 20,
        'max_cpc'    => 1.5,
        'paths'      => ['lego-abonnement'],

        'ad_groups'  => [
            'LEGO-abonnement' => [
                ['lego abonnement', 'PHRASE'],
                ['lego maandbox', 'PHRASE'],
                ['lego box abonnement', 'PHRASE'],
                ['lego abonnement kind', 'PHRASE'],
                ['lego elke maand', 'PHRASE'],
                ['lego verrassingsbox', 'PHRASE'],
                ['lego surprise box', 'PHRASE'],
                ['lego cadeau abonnement', 'PHRASE'],
                ['lego abonnement volwassenen', 'PHRASE'],
                ['lego pakket per maand', 'PHRASE'],
                ['maandelijkse lego box', 'PHRASE'],
                ['lego abonnement', 'EXACT'],
            ],
            'LEGO huren per maand' => [
                ['lego huren abonnement', 'PHRASE'],
                ['lego sets huren abonnement', 'PHRASE'],
                ['lego sets huren per maand', 'PHRASE'],
                ['lego huren maandelijks', 'PHRASE'],
                ['lego lenen abonnement', 'PHRASE'],
                ['bouwersbende', 'PHRASE'],
                ['bouwersbende alternatief', 'PHRASE'],
                ['bouwersbende abonnement', 'PHRASE'],
            ],
        ],

        'negatives' => [
            'gratis', 'tweedehands', 'marktplaats', 'kopen', 'vacature', 'baan',
            'stage', 'minifiguren', 'bouwtekening',
        ],

        // pin_h1 = 0: ongepind voor de beste Advertentiekwaliteit.
        'pin_h1'    => 0,
        'headlines' => [
            'Elke maand LEGO op de mat', 'LEGO-abonnement vanaf €31,25', 'Elke maand een nieuwe set',
            'Kies gewoon op leeftijd', 'Verzenden én retour gratis', 'Compleet & schoongemaakt',
            'Geen dure LEGO-aankoop meer', 'Steeds een nieuwe verrassing', 'Setwaarde €200 tot €350',
            'Bouwplezier zonder rommel', 'LEGO voor elke leeftijd', 'Nieuwe set, elke maand weer',
            'Vast bedrag per maand', 'Klaar? Kies je volgende set', 'Dé LEGO-verrassing per maand',
        ],

        'descriptions' => [
            'Elke maand een complete LEGO-set op de mat. Verzenden en retour gratis, vanaf €31,25.',
            'Kies op leeftijd, wij verrassen met een set t.w.v. €200-350. Geen dure aankoop meer.',
            'Klaar met bouwen? Stuur retour en ontvang de volgende set, zo vaak je wilt.',
            'Bouwplezier zonder rommel, voor elke leeftijd. Start je LEGO-abonnement vandaag.',
        ],

        // Koppen per advertentiegroep, zodat elke groep de eigen zoekwoorden dekt
        // (abonnement/maandbox vs huren) — dat tilt de Advertentiekwaliteit naar Goed.
        // Groepen zonder eigen set vallen terug op 'headlines' hierboven.
        'ad_group_headlines' => [
            'LEGO-abonnement' => [
                'LEGO-abonnement vanaf €31,25', 'Elke maand een LEGO-maandbox', 'LEGO-verrassingsbox per maand',
                'LEGO cadeau-abonnement', 'LEGO-maandbox op de mat', 'LEGO surprise box per maand',
                'LEGO-pakket per maand', 'LEGO-abonnement voor kinderen', 'Elke maand een nieuwe set',
                'Setwaarde €200 tot €350', 'Vast bedrag per maand', 'LEGO-abonnement volwassenen',
                'Nieuwe verrassing elke maand', 'Geen dure LEGO-aankoop', 'Klaar? Kies je volgende set',
            ],
            'LEGO huren per maand' => [
                'LEGO huren per maand', 'LEGO sets huren en bouwen', 'Elke maand LEGO huren',
                'LEGO huren met abonnement', 'Huur elke maand een set', 'LEGO huren vanaf €31,25',
                'LEGO-sets huren, geen kopen', 'Klaar? Huur de volgende set', 'LEGO sets huren per maand',
                'LEGO lenen met abonnement', 'Geen dure aankoop, wel huren', 'LEGO-abonnement of los huren',
                'LEGO huren, elke maand nieuw', 'Maandelijks LEGO huren', 'LEGO huren zonder kopen',
            ],
        ],

        // Zoekwoord-rijke descriptions per groep (abonnement/maandbox vs huren) —
        // Google vroeg om meer zoekwoorden in de beschrijvingen van de abo-advertentie.
        'ad_group_descriptions' => [
            'LEGO-abonnement' => [
                'Een LEGO-abonnement met elke maand een verrassingsbox op de mat. Vanaf €31,25 per maand.',
                'Kies je LEGO-maandbox: elke maand een set t.w.v. €200-350. Verzenden en retour gratis.',
                'LEGO-abonnement als cadeau? Elke maand een verrassing, zonder dure aankoop.',
                'Maandelijks een LEGO-box vol bouwplezier. Klaar? De volgende maandbox staat al klaar.',
            ],
            'LEGO huren per maand' => [
                'LEGO huren met abonnement: elke maand een set. Verzenden en retour gratis, vanaf €31,25.',
                'LEGO sets huren per maand: een set t.w.v. €200-350. Geen dure aankoop meer nodig.',
                'Liever LEGO lenen dan kopen? Stuur de set retour en huur maandelijks de volgende.',
                'Compleet gebouwd en schoongemaakt bij je thuis. LEGO huren zonder rommel of gedoe.',
            ],
        ],

        // Echte bouwverrassing-slugs (schone paden, geverifieerd in de theme-nav).
        'sitelinks' => [
            ['Hoe werkt het?', '/hoe-werkt-het', 'In een paar stappen', 'Elke maand een set'],
            ['Bekijk de pakketten', '/pakketten', 'Kies op leeftijd', 'Vast bedrag per maand'],
            ['Veelgestelde vragen', '/veelgestelde-vragen', 'Alles over het abonnement', 'Opzeggen, retour en meer'],
            ['Word lid', '/word-lid', 'Start je abonnement', 'Zo bij je op de mat'],
            ['Bekijk onze sets', '/onze-sets', 'Duizenden LEGO-sets', 'Kies je favoriet'],
            ['Cadeaubon kopen', '/cadeaubon', 'Verras een bouwfan', 'Direct te besteden'],
        ],

        'callouts' => [
            'Elke maand een set', 'Verzending gratis', 'Retour gratis',
            'Steeds een nieuwe set', 'Voor elke leeftijd', 'Geen dure aankoop',
        ],

        'snippet' => ['header' => 'Types', 'values' => ['Kleine sets', 'Grote sets', 'Themasets', 'Verrassingssets']],

        'call_phone' => '035 201 1720',
    ],

];
