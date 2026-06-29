<?php

/**
 * CHANNEL-SITES — losse marketing-websites per branche, elk op een eigen domein.
 *
 * Elk kanaal is een volwaardige mini-website (homepage / blog / over-ons /
 * plaatsen) met een eigen stijl die bij de branche past. Alle sites draaien op
 * deze same Laravel-app en funnelen leads terug naar het centrale WebsiteLead-
 * systeem (getagd met channel = de kanaal-key + branche).
 *
 * ── NIEUW KANAAL VOORBEREIDEN ────────────────────────────────────────────────
 *   1. Kopieer een blok hieronder, geef het een unieke key.
 *   2. Vul thema (kleuren/font), merk, en de teksten in. 'domain' = null laten.
 *   3. Bekijk 'm op  /_site/<key>  (preview op het hoofddomein).
 *
 * ── LIVE ZETTEN (domein bekend + gekoppeld) ──────────────────────────────────
 *   1. Zet 'domain' => 'jouwdomein.nl'  en  'status' => 'live'.
 *   2. Koppel het domein in Plesk aan dezelfde docroot + DNS.
 *   3. `php artisan optimize:clear && php artisan config:cache` (route-cache mee).
 *   Vanaf dat moment serveert de app de site op dat domein. Geen verdere code.
 *
 * Velden:
 *   name        merk-/sitenaam (in nav, footer, <title>-suffix)
 *   branche     koppelt aan WebsiteLead::BRANCHES + config/promo.php
 *   domain      productie-host zonder protocol, of null zolang concept
 *   status      'draft' (alleen via /_site/<key>) of 'live' (op domein)
 *   theme       kleur/font-tokens → als CSS-variabelen in de layout
 *   brand       logo_text (HTML-wordmark) of logo_image (pad in public/ of URL),
 *               telefoon, e-mail, kvk, + optioneel endorsement / endorsement_url
 *               (override van het Groeidiamant-keurmerk onderaan)
 *   meta        <title>/description voor de homepage + default OG
 *   home        hero + usps + features + stappen + social proof
 *   about       over-ons content
 *   places      plaatsen-SEO: kop + per-stad sjabloonteksten (:city placeholder)
 *   view        optioneel: 'channels.<key>.home' voor een bespoke homepage-blade
 */

return [

    // Standaard contactgegevens + merk-endorsement als een kanaal ze niet overschrijft.
    //
    // BRANDING-MODEL (besluit): elke niche-site heeft een EIGEN logo/merk (de
    // bezoeker ziet 'm als zelfstandige specialist). Het moederbedrijf staat
    // alleen discreet onderaan als keurmerk: "Volgens de Groeidiamant van
    // Betergeregeld ICT". Override per kanaal kan via brand.endorsement /
    // brand.endorsement_url.
    'defaults' => [
        'phone' => '085 1303 600',
        'email' => 'hallo@betergeregeld.nl',
        'kvk'   => '',
        'endorsement'     => 'Volgens de Groeidiamant van Betergeregeld ICT',
        'endorsement_url' => 'https://betergeregeld.nl',
    ],

    'channels' => [

        /* ───────────────────────── HORECA (volledig voorbeeld) ───────────────── */
        'horeca' => [
            'name'    => 'HorecaSites',
            'branche' => 'horeca',
            'domain'  => null,                 // bv. 'horecawebsitelatenmaken.nl'
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#b91c1c',        // diep rood
                'accent'  => '#f59e0b',        // amber
                'ink'     => '#1c1917',        // warme zwart
                'muted'   => '#78716c',
                'bg'      => '#fffaf5',         // crème
                'surface' => '#ffffff',
                'font'    => "'Poppins', system-ui, -apple-system, sans-serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap',
                'radius'  => '14px',
            ],

            'brand' => [
                'logo_text' => 'Horeca<span>Sites</span>',
                // 'logo_image' => 'channel-logos/horeca.svg', // eigen logo in public/ → vervangt de wordmark
                'phone'     => '085 1303 600',
                'email'     => 'horeca@betergeregeld.nl',
                // 'endorsement' => '...', // override van het standaard Groeidiamant-keurmerk
            ],

            'meta' => [
                'home_title'       => 'Horecawebsite laten maken — meer reserveringen & gasten',
                'home_description' => 'Strakke websites voor restaurants, cafés en lunchrooms. Online menu, reserveren en topvindbaarheid. Vooraf een gratis voorbeeld.',
            ],

            'home' => [
                'hero_eyebrow' => 'Speciaal voor de horeca',
                'hero_title'   => 'Een website die je tafels vult',
                'hero_sub'     => 'Menu, online reserveren en goede vindbaarheid in Google — in één strakke site die past bij de sfeer van je zaak.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jóuw zaak. Geen verplichtingen.',

                'usps' => [
                    'Online reserveren zonder gedoe',
                    'Menukaart die je zelf bijwerkt',
                    'Gevonden worden in jouw stad',
                ],

                'features' => [
                    ['icon' => '🍽️', 'title' => 'Menu online',        'text' => 'Altijd actueel, leesbaar op elke telefoon.'],
                    ['icon' => '📅', 'title' => 'Online reserveren',   'text' => 'Gasten boeken zelf — eventueel met aanbetaling.'],
                    ['icon' => '⭐', 'title' => 'Reviews & sfeer',     'text' => 'Toon je beste foto\'s en beoordelingen.'],
                    ['icon' => '📍', 'title' => 'Lokaal vindbaar',     'text' => 'Geoptimaliseerd voor "restaurant in [jouw stad]".'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'We zetten vooraf een voorbeeld van jouw zaak klaar.'],
                    ['title' => 'Samen afstemmen',  'text' => 'In één gesprek scherpen we het aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Wij regelen techniek, hosting en vindbaarheid.'],
                ],

                'proof' => '“Sinds de nieuwe site lopen de online reserveringen storm.” — restaurant in Utrecht',
            ],

            'about' => [
                'title' => 'Websites door mensen die de horeca snappen',
                'lead'  => 'Wij bouwen al jaren websites voor restaurants, cafés en lunchrooms. We weten wat een gast zoekt en wat een zaak nodig heeft.',
                'body'  => [
                    'Geen ingewikkelde systemen, geen lange contracten. Je krijgt een strakke site die werkt op elke telefoon, die je zelf kunt bijwerken en die nieuwe gasten naar je toe trekt.',
                    'We beginnen altijd met een gratis voorbeeld van jóuw zaak. Zo zie je precies wat je krijgt voordat je iets beslist.',
                ],
                'stats' => [
                    ['value' => '2 dagen', 'label' => 'tot een voorbeeld'],
                    ['value' => '2 weken', 'label' => 'tot live'],
                    ['value' => 'heel NL', 'label' => 'werkgebied'],
                ],
            ],

            'places' => [
                'h1'         => 'Dé partner voor horecawebsites in heel Nederland',
                'intro'      => 'Waar je zaak ook zit — wij maken horecawebsites in elke plaats van Nederland. Kies je stad of vraag direct een gratis voorbeeld aan.',
                'city_h1'    => 'Horecawebsite laten maken in :city',
                'city_intro' => 'Run je een restaurant, café of lunchroom in :city? Wij maken een strakke website die nieuwe gasten naar je toe trekt — met online menu en reserveren. Vooraf een gratis voorbeeld van jóuw zaak in :city.',
                'service'    => 'horecawebsite',
            ],

            'view' => null,
        ],

        /* ───────────────────────── KAPPER / BEAUTY (tweede voorbeeld) ────────── */
        'kapper' => [
            'name'    => 'SalonSites',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#9333ea',
                'accent'  => '#ec4899',
                'ink'     => '#2e1065',
                'muted'   => '#6b7280',
                'bg'      => '#faf5ff',
                'surface' => '#ffffff',
                'font'    => "'Quicksand', system-ui, sans-serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&display=swap',
                'radius'  => '20px',
            ],

            'brand' => [
                'logo_text' => 'Salon<span>Sites</span>',
                'phone'     => '085 1303 600',
                'email'     => 'salon@betergeregeld.nl',
            ],

            'meta' => [
                'home_title'       => 'Website voor je kapsalon of beautyzaak laten maken',
                'home_description' => 'Online afspraken, prijslijst en een galerij van je werk. Strakke salonwebsites met een gratis voorbeeld vooraf.',
            ],

            'home' => [
                'hero_eyebrow' => 'Voor kappers & beauty',
                'hero_title'   => 'Laat klanten online een afspraak maken',
                'hero_sub'     => 'Een stijlvolle website met online boeken, je prijslijst en een galerij van je mooiste werk.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jóuw salon.',

                'usps' => [
                    'Online afspraken 24/7',
                    'Toon je werk in een galerij',
                    'Gevonden in jouw stad',
                ],

                'features' => [
                    ['icon' => '💇', 'title' => 'Online boeken',   'text' => 'Klanten boeken zelf, dag en nacht.'],
                    ['icon' => '💅', 'title' => 'Prijslijst',      'text' => 'Overzichtelijk en altijd actueel.'],
                    ['icon' => '📸', 'title' => 'Galerij',         'text' => 'Laat je voor/na-werk zien.'],
                    ['icon' => '⭐', 'title' => 'Reviews',         'text' => 'Bouw vertrouwen met beoordelingen.'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'We zetten vooraf een voorbeeld van jouw salon klaar.'],
                    ['title' => 'Samen afstemmen',  'text' => 'In één gesprek scherpen we het aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Wij regelen alles van techniek tot vindbaarheid.'],
                ],

                'proof' => '“Klanten boeken nu zelf online — scheelt me uren bellen.” — kapsalon in Amersfoort',
            ],

            'about' => [
                'title' => 'Salonwebsites die bij jouw stijl passen',
                'lead'  => 'We maken websites voor kappers, nagelstudio\'s en beautyzaken door heel Nederland.',
                'body'  => [
                    'Een strakke site, online afsprakensysteem en een galerij van je werk — zonder gedoe en zonder lange contracten.',
                    'We starten met een gratis voorbeeld van jóuw salon, zodat je precies ziet wat je krijgt.',
                ],
                'stats' => [
                    ['value' => '2 dagen', 'label' => 'tot een voorbeeld'],
                    ['value' => '2 weken', 'label' => 'tot live'],
                    ['value' => 'heel NL', 'label' => 'werkgebied'],
                ],
            ],

            'places' => [
                'h1'         => 'Dé partner voor salonwebsites in heel Nederland',
                'intro'      => 'In elke plaats van Nederland maken wij stijlvolle websites voor kappers en beautyzaken. Kies je stad of vraag een gratis voorbeeld aan.',
                'city_h1'    => 'Salonwebsite laten maken in :city',
                'city_intro' => 'Heb je een kapsalon of beautyzaak in :city? Wij bouwen een stijlvolle website met online boeken — en zetten vooraf een gratis voorbeeld van jóuw salon in :city klaar.',
                'service'    => 'salonwebsite',
            ],

            // Groeidiamant-fase-overrides op de homepage. Geen entry = de 'home'
            // hierboven (fase 'website'). SEO-instap: /groeifase/webshop.
            'facets' => [
                'webshop' => [
                    'hero_eyebrow' => 'Voor kappers die al een website hebben',
                    'hero_title'   => 'Verkoop je producten online — naast je salon',
                    'hero_sub'     => 'Je hebt al een site; tijd voor de volgende stap. Een webshop voor je haarproducten en cadeaubonnen, netjes gekoppeld aan je bestaande site.',
                    'hero_cta'     => 'Gratis voorbeeld van je webshop',
                    'hero_note'    => 'Je huidige website blijft — we bouwen de webshop eraan vast.',
                    'usps' => [
                        'Haarproducten & cadeaubonnen online',
                        'Veilig betalen met iDEAL',
                        'Gekoppeld aan je bestaande site',
                    ],
                    'features' => [
                        ['icon' => '🛍️', 'title' => 'Productshop',   'text' => 'Je producten netjes online, makkelijk bij te werken.'],
                        ['icon' => '🎁', 'title' => 'Cadeaubonnen',  'text' => 'Online verkopen en in de salon inwisselen.'],
                        ['icon' => '💳', 'title' => 'Veilig betalen', 'text' => 'iDEAL en kaart, zonder gedoe.'],
                        ['icon' => '🔗', 'title' => 'Gekoppeld',     'text' => 'Eén geheel met je huidige website.'],
                    ],
                    'proof' => '“De cadeaubonnen lopen online storm — vooral rond de feestdagen.” — kapsalon in Bussum',
                ],
            ],

            'view' => null,
        ],

        /* ───────────────── DAMESKAPPER — ECHTE 1-PAGE VOORBEELDSITE ───────────
         * Geen marketing-site maar een demo van hoe de site van de klant zélf
         * wordt (toon je tijdens de afspraak). Bespoke view: channels.dameskapper.home.
         */
        'dameskapper' => [
            'name'    => 'Salon Lumière',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#b76e79',        // dusty rose
                'accent'  => '#c9a27e',        // zacht goud/taupe
                'ink'     => '#2b2724',        // warme houtskool
                'muted'   => '#8a807a',
                'bg'      => '#faf6f2',         // warme crème
                'surface' => '#ffffff',
                'font'    => "'Jost', system-ui, sans-serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap',
                'radius'  => '16px',
            ],

            'brand' => [
                'logo_text' => 'Salon Lumière',
                'phone'     => '035 123 45 67',
                'email'     => 'hallo@salon-lumiere.nl',
                'address'   => 'Brinklaan 12, Bussum',
            ],

            'meta' => [
                'home_title'       => 'Salon Lumière — dameskapper in Bussum',
                'home_description' => 'Knippen, kleuren, balayage en opsteken bij Salon Lumière in Bussum. Maak eenvoudig online een afspraak.',
            ],

            'view' => 'channels.dameskapper.home',
        ],

        /* ───────────────── HERENKAPPER / BARBERSHOP — 1-PAGE VOORBEELDSITE ─────
         * Contrasterende, stoere stijl t.o.v. de dameskapper. Bespoke view.
         */
        'herenkapper' => [
            'name'    => 'Brink Barbers',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#c79a3a',        // goud
                'accent'  => '#e6c068',
                'ink'     => '#15120d',        // bijna zwart, warm
                'muted'   => '#9a9186',
                'bg'      => '#15120d',
                'surface' => '#1f1b14',
                'font'    => "'Barlow', system-ui, sans-serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Barlow:wght@300;400;500;600&display=swap',
                'radius'  => '8px',
            ],

            'brand' => [
                'logo_text' => 'Brink Barbers',
                'phone'     => '035 765 43 21',
                'email'     => 'info@brinkbarbers.nl',
                'address'   => 'Brinklaan 48, Bussum',
            ],

            'meta' => [
                'home_title'       => 'Brink Barbers — herenkapper & barbershop in Bussum',
                'home_description' => 'Knippen, baard trimmen en scheren met het mes bij Brink Barbers in Bussum. Boek eenvoudig online je afspraak.',
            ],

            'view' => 'channels.herenkapper.home',
        ],

    ],
];
