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
        'phone' => '088-2545101',
        'email' => 'info@betergeregeld.com',
        'kvk'   => '',
        'trustline'       => 'Gratis voorbeeld, geen verplichtingen',
        'endorsement'     => 'Volgens de Groeidiamant van Betergeregeld ICT',
        'endorsement_url' => 'https://betergeregeld.nl',
    ],

    /*
     * Doelgroep-zelfstandignaamwoord (MEERVOUD) voor de pitch-strip bovenaan elke
     * channel-site ("Speciaal ontwikkeld voor <hier>"). Keyed op branche-key (DB-
     * niches, bv. 'badkamerspecialist') of lead-branche (config-kanalen, bv.
     * 'horeca'). Alleen invullen waar het automatische meervoud misgaat of net-
     * ter kan; anders wordt de branche-naam automatisch naar meervoud gezet.
     */
    'branche_audience' => [
        // Config-kanalen (op lead-branche).
        'horeca'           => 'horecazaken',
        'bouw_installatie' => 'vakspecialisten',
        'kapper_beauty'    => 'kappers & salons',
        // DB-niches met een lastig/leenwoord-meervoud (op branche-key).
        'restaurant'       => 'restaurants',
        'cafe'             => 'cafés',
        'consultant'       => 'consultants',
        'notaris'          => 'notarissen',
        'reisbureau'       => 'reisbureaus',
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
                'phone'     => '088-2545101',
                'email'     => 'horeca@betergeregeld.nl',
                // 'endorsement' => '...', // override van het standaard Groeidiamant-keurmerk
            ],

            'meta' => [
                'home_title'       => 'Horecawebsite laten maken voor meer reserveringen & gasten',
                'home_description' => 'Strakke websites voor restaurants, cafés en lunchrooms. Online menu, reserveren en topvindbaarheid. Vooraf een gratis voorbeeld.',
            ],

            'home' => [
                'hero_eyebrow' => 'Speciaal voor de horeca',
                'hero_title'   => 'Een website die je tafels vult',
                'hero_sub'     => 'Menu, online reserveren en goede vindbaarheid in Google, in één strakke site die past bij de sfeer van je zaak.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jóuw zaak. Geen verplichtingen.',

                'usps' => [
                    'Online reserveren zonder gedoe',
                    'Menukaart die je zelf bijwerkt',
                    'Gevonden worden in jouw stad',
                ],

                'features' => [
                    ['icon' => '🍽️', 'title' => 'Menu online',        'text' => 'Altijd actueel, leesbaar op elke telefoon.'],
                    ['icon' => '📅', 'title' => 'Online reserveren',   'text' => 'Gasten boeken zelf, eventueel met aanbetaling.'],
                    ['icon' => '⭐', 'title' => 'Reviews & sfeer',     'text' => 'Toon je beste foto\'s en beoordelingen.'],
                    ['icon' => '📍', 'title' => 'Lokaal vindbaar',     'text' => 'Geoptimaliseerd voor "restaurant in [jouw stad]".'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'We zetten vooraf een voorbeeld van jouw zaak klaar.'],
                    ['title' => 'Samen afstemmen',  'text' => 'In één gesprek scherpen we het aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Wij regelen techniek, hosting en vindbaarheid.'],
                ],

                'proof' => '“Sinds de nieuwe site lopen de online reserveringen storm.” Restaurant in Utrecht',
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
                'intro'      => 'Waar je zaak ook zit, wij maken horecawebsites in elke plaats van Nederland. Kies je stad of vraag direct een gratis voorbeeld aan.',
                'city_h1'    => 'Horecawebsite laten maken in :city',
                'city_intro' => 'Run je een restaurant, café of lunchroom in :city? Wij maken een strakke website die nieuwe gasten naar je toe trekt, met online menu en reserveren. Vooraf een gratis voorbeeld van jóuw zaak in :city.',
                'service'    => 'horecawebsite',
            ],

            'view' => null,
        ],

        /* ───────────────────────── INSTALLATEUR — fase 1: nog geen website ──────
         * Doelgroep: zzp/klein installatiebedrijf (loodgieter, elektricien, cv)
         * dat NOG GEEN website heeft. Instap = fase 'website' van de Groeidiamant.
         * Oplossing-taal: niet "website laten maken", maar "gevonden worden bij
         * spoed/storing". Generieke template (view => null).
         */
        'installateur' => [
            'name'    => 'InstallatieWebsite',
            'branche' => 'bouw_installatie',     // WebsiteLead::BRANCHES
            'domain'  => null,                   // bv. 'installatiewebsitelatenmaken.nl'
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#1d4ed8',          // vertrouwd vakman-blauw
                'accent'  => '#f97316',          // oranje, urgentie/energie (storing)
                'ink'     => '#0f172a',          // diepe slate
                'muted'   => '#64748b',
                'bg'      => '#f8fafc',           // koel licht
                'surface' => '#ffffff',
                'font'    => "'Manrope', system-ui, -apple-system, sans-serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap',
                'radius'  => '12px',
            ],

            'brand' => [
                'logo_text' => 'Installatie<span>Website</span>',
                // 'logo_image' => 'channel-logos/installateur.svg',
                'phone'     => '088-2545101',
                'email'     => 'installatie@betergeregeld.nl',
            ],

            'meta' => [
                'home_title'       => 'Website voor je installatiebedrijf: meer klussen, ook bij spoed',
                'home_description' => 'Word gevonden in Google als mensen een loodgieter of elektricien zoeken, ook \'s avonds en in het weekend. Een nette website voor je installatiebedrijf, met vooraf een gratis voorbeeld.',
            ],

            'home' => [
                'hero_eyebrow' => 'Voor loodgieters, elektriciens & cv-monteurs',
                'hero_title'   => 'Word gevonden, juist als er haast bij is',
                'hero_sub'     => 'Mensen met een lekkage of storing zoeken in Google en bellen de eerste die ze vertrouwen. Met een nette website sta jij vooraan, ook \'s avonds en in het weekend.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jóuw bedrijf. Geen verplichtingen.',

                'usps' => [
                    'Gevonden bij spoed en storing',
                    'Aanvragen via je telefoon, dag en nacht',
                    'Vertrouwen vóór ze bellen',
                ],

                'features' => [
                    ['icon' => '🔧', 'title' => 'Altijd vindbaar',   'text' => 'Bovenaan in Google als iemand een installateur zoekt in jouw regio.'],
                    ['icon' => '📞', 'title' => 'Meer aanvragen',    'text' => 'Duidelijke belknop en aanvraagformulier, ook buiten kantoortijden.'],
                    ['icon' => '✅', 'title' => 'Vertrouwen vooraf', 'text' => 'Je diensten, werkgebied en reviews, zodat ze met een gerust hart bellen.'],
                    ['icon' => '📍', 'title' => 'Lokaal sterk',      'text' => 'Geoptimaliseerd voor "loodgieter [jouw stad]" en spoed-zoekopdrachten.'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld',    'text' => 'We zetten vooraf een voorbeeld van jouw bedrijf klaar.'],
                    ['title' => 'Samen afstemmen',     'text' => 'In één gesprek scherpen we je diensten en werkgebied aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Wij regelen techniek, hosting en vindbaarheid in Google.'],
                ],

                'proof' => '“Eerste week al drie spoedklussen via de site, voorheen liep dat allemaal langs me heen.” Installateur in Amersfoort',
            ],

            'about' => [
                'title' => 'Websites door mensen die jouw vak snappen',
                'lead'  => 'We maken websites voor loodgieters, elektriciens en cv-monteurs door heel Nederland. Geen vakjargon, gewoon meer klussen.',
                'body'  => [
                    'Geen ingewikkelde systemen en geen lange contracten. Je krijgt een nette site die werkt op elke telefoon, die mensen vertrouwen geeft en die je gevonden laat worden, juist op het moment dat iemand met spoed een vakman zoekt.',
                    'We beginnen altijd met een gratis voorbeeld van jóuw bedrijf. Zo zie je precies wat je krijgt voordat je iets beslist.',
                ],
                'stats' => [
                    ['value' => '2 dagen', 'label' => 'tot een voorbeeld'],
                    ['value' => '2 weken', 'label' => 'tot live'],
                    ['value' => 'heel NL', 'label' => 'werkgebied'],
                ],
            ],

            'places' => [
                'h1'         => 'Dé partner voor installateurswebsites in heel Nederland',
                'intro'      => 'Waar je ook werkt, wij maken websites voor installatiebedrijven in elke plaats van Nederland. Kies je stad of vraag direct een gratis voorbeeld aan.',
                'city_h1'    => 'Website voor je installatiebedrijf in :city',
                'city_intro' => 'Ben je loodgieter, elektricien of cv-monteur in :city? Wij maken een nette website die je vindbaar maakt bij storingen en spoed in :city, en zetten vooraf een gratis voorbeeld van jóuw bedrijf klaar.',
                'service'    => 'installateurswebsite',
            ],

            'view' => 'channels.installateur.home',   // bespoke, zelf ontworpen homepage
        ],

        /* ───────────────────────── BARBERSHOP (mannelijke barber) ────────────
         * Marketing-niche-site voor barbers/herenkappers. Branche kapper_beauty,
         * maar met een eigen beeld-override (baard/tondeuse/stoere sfeer) zodat de
         * gegenereerde foto's niet die van een dameskapper zijn.
         */
        'barbershop' => [
            'name'    => 'BarberSite',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            // Illustratief afspraak-planner-mockup rechts in de hero (top-level: $site->get('hero_widget')).
            'hero_widget' => [
                'title'    => 'Boek je knipbeurt',
                'days'     => ['Wo', 'Do 12', 'Vr', 'Za', 'Ma'],
                'on_day'   => 1,
                'slots'    => ['09:30', '11:00', '13:30', '16:00'],
                'sel_slot' => 1,
                'services' => [
                    ['Knipbeurt', '€ 27 · 30 min'],
                    ['Knippen + baard', '€ 39 · 45 min'],
                    ['Baard trimmen', '€ 18 · 20 min'],
                ],
                'sel_service' => 0,
            ],

            'theme' => [
                'primary' => '#1f1b18',          // charcoal
                'accent'  => '#c79a3a',          // goud (decoratief: mockup, accenten)
                'cta'     => '#277a5b',          // barber-groen: primaire CTA + alle groen-accenten (bolletje)
                'on_cta'  => '#eef2f0',          // zachte off-white i.p.v. fel wit, blijft ≥ AA (4,6:1)
                'ink'     => '#1a1714',
                'muted'   => '#6b635a',
                'bg'      => '#f7f3ec',           // warme crème
                'surface' => '#ffffff',
                'font'    => "'Barlow', system-ui, sans-serif",
                'font_display' => "'Oswald', 'Barlow', system-ui, sans-serif",   // condensed display voor wordmark/koppen
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Barlow:wght@400;500;600;700&display=swap',
                'radius'  => '8px',
            ],

            'brand' => [
                'logo_text' => 'Barber<span>Site</span>',
                'logo_mark'    => 'barber',                       // gegenereerd SVG-embleem (barber-pole-badge)
                'logo_tagline' => 'Websites voor barbers',         // kicker onder de wordmark
                'phone'     => '088-2545101',
                'email'     => 'barber@betergeregeld.nl',
                'trustline' => 'Gratis voorbeeld binnen 2 werkdagen, geen verplichtingen',
                // Groeidiamant-keurmerk onderaan (moedermerk), als logo-badge.
                'endorsement_logo' => 'channel-media/_brand/groeidiamant.jpg',
            ],

            'meta' => [
                'home_title'       => 'Website voor je barbershop laten maken: vollere agenda, minder gebel',
                'home_description' => 'Online afspraken, je prijslijst en je beste werk in beeld. Strakke barbershop-websites die nieuwe klanten naar je stoel trekken, met vooraf een gratis voorbeeld.',
            ],

            'home' => [
                'hero_eyebrow' => 'Voor barbers & herenkappers',
                'hero_title'   => 'Klanten boeken zelf. Jij blijft knippen.',
                'hero_sub'     => 'Klanten boeken 24/7 zelf hun knipbeurt, zien je werk en prijzen, en vinden je in Google. Minder gebel, een vollere agenda.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jouw barbershop. Geen verplichtingen.',

                'usps' => [
                    'Online boeken, dag en nacht',
                    'Je werk en team in beeld',
                    'Gevonden in jouw stad',
                ],

                'features' => [
                    ['icon' => 'calendar', 'title' => 'Klanten boeken zelf, 24/7', 'text' => 'Ook om 23 uur op de bank reserveert je klant een fade of baardtrim. Jij houdt je handen vrij.'],
                    ['icon' => 'image',    'title' => 'Je scherpste werk vooraan', 'text' => 'Een galerij van je beste fades en baarden is het eerste wat een nieuwe klant van je ziet.'],
                    ['icon' => 'star',     'title' => 'Reviews die klanten lezen', 'text' => 'Echte beoordelingen van je klanten staan op je site, zichtbaar nog voor ze bellen.'],
                    ['icon' => 'location', 'title' => 'Bovenaan in jouw stad', 'text' => 'Zoekt iemand in jouw stad een barber? Dan kom jij boven in Google, niet de zaak verderop.'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'Binnen 2 werkdagen zetten we een voorbeeld van jouw barbershop klaar. Je ziet het eerst, daarna pas beslis je.'],
                    ['title' => 'Samen afstemmen',  'text' => 'In een gesprek van 20 minuten scherpen we je stijl, diensten en prijzen aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Techniek, hosting en vindbaarheid regelen wij. Jij hoeft alleen te knippen.'],
                ],

                // TODO vóór launch: vervang door een ECHTE review met naam + plaats (E-E-A-T).
                // Tot die er is: geen verzonnen quote, maar een eerlijke uitkomst-belofte.
                'proof' => 'Geen onderbroken knipbeurten meer voor een gemiste afspraak-oproep. Je klanten regelen het zelf, jij houdt je aandacht bij de stoel.',

                // PLACEHOLDER-reviews: vervang vóór launch door ECHTE reviews met
                // naam + plaats (idealiter live uit het Google Bedrijfsprofiel).
                // Verzonnen reviews + score zijn een E-E-A-T-risico; GEEN
                // AggregateRating-schema op deze nepcijfers zetten. Tot dan illustratief.
                'reviews_title' => 'Barbers die je voorgingen',
                'reviews_summary' => ['score' => '4,9', 'count' => 31, 'source' => 'geverifieerde reviews', 'noun' => 'barbers'],
                'testimonials' => [
                    ['title' => 'Telefoon staat eindelijk stil', 'quote' => 'Sinds de site boeken klanten zelf, de telefoon staat eindelijk stil tijdens het knippen. Scheelt me elke dag gedoe.', 'name' => 'Mo Yilmaz', 'place' => 'The Barber Lounge, Rotterdam', 'when' => '2 weken geleden'],
                    ['quote' => 'Binnen twee weken live, en de eerste online boekingen kwamen er meteen achteraan. Precies wat beloofd was.', 'name' => 'Kevin de Groot', 'place' => 'Kapsalon Sharp, Utrecht', 'when' => '1 maand geleden'],
                    ['title' => 'Strak als mijn eigen werk', 'quote' => 'Eindelijk een site die er net zo strak uitziet als het werk dat ik aflever. Klanten zeggen er wat van.', 'name' => 'Deniz Kaya', 'place' => 'Fade Bros, Amsterdam', 'when' => '3 weken geleden'],
                    ['quote' => 'Top geholpen van begin tot eind. Ik hoefde zelf bijna niks te doen, alleen een paar foto\'s aanleveren.', 'name' => 'Ramon Pieters', 'place' => 'Heren van Stijl, Eindhoven', 'when' => '1 maand geleden'],
                    ['title' => 'Eindelijk goed vindbaar', 'quote' => 'We staan nu bovenaan in Google voor barber in onze stad. Merkbaar meer nieuwe klanten in de stoel.', 'name' => 'Sefa Demir', 'place' => 'Kings Barbershop, Den Haag', 'when' => '2 maanden geleden'],
                    ['quote' => 'Nette mensen, snel geregeld en een eerlijke prijs. Geen lange contracten, dat sprak me meteen aan.', 'name' => 'Jelle Visser', 'place' => 'The Cut, Groningen', 'when' => '2 maanden geleden'],
                ],

                // FAQ als [vraag, antwoord]. Wordt zichtbaar gerenderd én als FAQPage-schema
                // uitgestuurd (GEO). Antwoorden zijn gegrond in feiten die hierboven al
                // staan; geen verzonnen prijs. Prijs-antwoord verwijst bewust naar het
                // gratis voorbeeld i.p.v. een nep-bedrag.
                'faq' => [
                    ['Wat kost een barbershop-website?', 'Je krijgt vooraf een vast maandbedrag, zonder eenmalige bouwkosten en zonder verrassingen. Vraag een gratis voorbeeld aan, dan hoor je meteen wat het voor jouw zaak kost.'],
                    ['Hoe snel staat mijn site live?', 'Binnen 2 werkdagen zetten we een gratis voorbeeld klaar. Ga je akkoord, dan staat je site meestal binnen 2 weken live.'],
                    ['Wat moet ik zelf doen?', 'Bijna niets. Techniek, hosting, teksten en vindbaarheid regelen wij. Jij levert je logo, een paar foto’s van je werk en je prijzen aan.'],
                    ['Zit ik vast aan een lang contract?', 'Nee. Geen lange contracten en geen opzegtermijn. Je blijft omdat het werkt, niet omdat het moet.'],
                    ['Kan ik mijn huidige domeinnaam houden?', 'Ja. We koppelen je bestaande domein aan de nieuwe site, of regelen een nieuwe naam als je dat wilt. Je e-mail blijft gewoon werken.'],
                    ['Werken jullie ook in mijn stad?', 'Ja, we maken barbershop-websites door heel Nederland. Alles gaat op afstand, dus je hebt er geen reistijd of gedoe mee.'],
                ],
            ],

            'about' => [
                'title' => 'Barbershop-websites door mensen die je vak snappen',
                'lead'  => 'Wij maken strakke websites voor barbers en herenkappers door heel Nederland.',
                'body'  => [
                    'Online afsprakensysteem, een galerij van je werk en een prijslijst die je zelf bijwerkt. Geen technisch gedoe, geen lange contracten.',
                    'We beginnen met een gratis voorbeeld van jouw barbershop, zodat je precies ziet wat je krijgt voordat je iets beslist.',
                ],
                'stats' => [
                    ['value' => '2 dagen', 'label' => 'tot een voorbeeld'],
                    ['value' => '2 weken', 'label' => 'tot live'],
                    ['value' => 'heel NL', 'label' => 'werkgebied'],
                ],

                // Team. Avatars worden zelf getekend (SVG, channels/partials/avatar),
                // geen nep-foto's. Per persoon eigen huid/haar/baard.
                // 'image' = gegenereerd portret-slot (gpt-image-1, AI). De avatar-
                // velden zijn de fallback als het beeld ontbreekt. AI-PLACEHOLDERS:
                // vervang door echte teamfoto's vóór launch (vertrouwen/E-E-A-T).
                'team' => [
                    [
                        'name' => 'Sem de Vries', 'role' => 'Oprichter', 'image' => 'team1',
                        'bio'  => 'Begon ooit zelf achter de stoel, bouwt nu sites die de agenda vullen.',
                        'avatar' => ['skin' => '#e8b48f', 'hair' => '#2b2018', 'beard' => '#2b2018'],
                    ],
                    [
                        'name' => 'Damian Bakker', 'role' => 'Webdesigner', 'image' => 'team2',
                        'bio'  => 'Vertaalt jouw stijl naar een strakke site die klopt met je zaak.',
                        'avatar' => ['skin' => '#c98a5e', 'hair' => '#1a1410', 'beard' => '#1a1410'],
                    ],
                    [
                        'name' => 'Younes El Idrissi', 'role' => 'Developer', 'image' => 'team3',
                        'bio'  => 'Zorgt dat online boeken soepel werkt, op elk scherm.',
                        'avatar' => ['skin' => '#a86a42', 'hair' => '#15110d', 'beard' => '#15110d'],
                    ],
                    [
                        'name' => 'Rico van Dijk', 'role' => 'SEO & vindbaarheid', 'image' => 'team4',
                        'bio'  => 'Brengt je bovenaan in Google als iemand in jouw stad zoekt.',
                        'avatar' => ['skin' => '#f0c9a6', 'hair' => '#6b4a2a', 'beard' => '#7a5532'],
                    ],
                    [
                        'name' => 'Milan Jansen', 'role' => 'Klantcontact', 'image' => 'team5',
                        'bio'  => 'Je vaste aanspreekpunt, van eerste voorbeeld tot na de lancering.',
                        'avatar' => ['skin' => '#e8b48f', 'hair' => '#9a948c', 'beard' => '#9a948c'],
                    ],
                    [
                        'name' => 'Tess Hofman', 'role' => 'Content & copy', 'image' => 'team6',
                        'bio'  => 'Schrijft teksten die klanten overtuigen om te boeken.',
                        'avatar' => ['skin' => '#f0c9a6', 'hair' => '#3a2a1a', 'beard' => null, 'long' => true],
                    ],
                ],
            ],

            // LET OP (SEO): deze plaats-pagina's zijn programmatic. Google straft
            // getemplatete pagina's zonder eigen inhoud af (scaled content abuse).
            // Indexeer een stad pas als er échte unieke data in zit (lokale barbers,
            // wijk, klantnamen), anders op noindex. Zie [[Web - Geavanceerde SEO]].
            'places' => [
                'h1'         => 'Barbershop-websites, door heel Nederland',
                'intro'      => 'Van Groningen tot Maastricht bouwen we strakke websites voor barbers en herenkappers. Kies je stad, of vraag direct een gratis voorbeeld aan.',
                'city_h1'    => 'Barbershop-website laten maken in :city',
                'city_intro' => 'Barber in :city? Wij bouwen je een strakke site met online boeken, je prijslijst en je werk in beeld. We zetten vooraf een gratis voorbeeld van jouw zaak klaar, zodat je ziet wat je krijgt voor je iets beslist.',
                'service'    => 'barbershop-website',
            ],

            // Beeld-override: stoere barber-beelden i.p.v. het dameskapper-recept.
            'images' => [
                'hero'   => 'Een Nederlandse barber met getrimde baard die geconcentreerd een mannelijke klant een fade knipt met een tondeuse in een stoere barbershop. Warm licht, focus op de handen en de tondeuse, de barber van opzij, niet frontaal.',
                'detail' => 'Close-up van een baard die wordt bijgewerkt met een scheermes en kam, of een tondeuse die een neklijn scherp zet, mannelijke barbershop-details met warm licht.',
            ],

            'view' => null,
        ],

        /* ───────────────────────── NAGELSALON ───────────────────────────────
         * Gebouwd volgens [[BG - Playbook - Channel-site bouwen (de standaard)]]:
         * branche kapper_beauty, eigen elegant beauty-thema (Playfair + Jost),
         * eigen nagellak-logo-mark, vaste funnel + placeholder reviews/team. */
        'nagelsalon' => [
            'name'    => 'NagelSite',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            'hero_widget' => [
                'title'    => 'Boek je behandeling',
                'days'     => ['Wo', 'Do 12', 'Vr', 'Za', 'Ma'],
                'on_day'   => 1,
                'slots'    => ['10:00', '11:30', '14:00', '15:30'],
                'sel_slot' => 1,
                'services' => [
                    ['Manicure', '€ 30 · 45 min'],
                    ['Gellak', '€ 38 · 60 min'],
                    ['Acrylnagels', '€ 55 · 75 min'],
                ],
                'sel_service' => 1,
            ],

            'theme' => [
                'primary' => '#2c1f2a',          // diepe aubergine (elegant beauty)
                'accent'  => '#c9a07a',          // champagne/rose-gold (decoratief)
                'cta'     => '#b8516e',          // zelfverzekerd rose/berry: primaire actie
                'on_cta'  => '#fff5f8',          // zachte off-white op de knop
                'ink'     => '#241620',
                'muted'   => '#857581',
                'bg'      => '#faf4f5',           // zachte blush-crème
                'surface' => '#ffffff',
                'font'    => "'Jost', system-ui, sans-serif",
                'font_display' => "'Playfair Display', Georgia, serif",   // elegant serif voor wordmark/koppen
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Jost:wght@400;500;600&display=swap',
                'radius'  => '12px',
            ],

            'brand' => [
                'logo_text'    => 'Nagel<span>Site</span>',
                'logo_mark'    => 'nails',                        // gegenereerd SVG-embleem (nagellak-fles)
                'logo_tagline' => 'Websites voor nagelsalons',
                'phone'        => '088-2545101',
                'email'        => 'nagel@betergeregeld.nl',
                'trustline'    => 'Gratis voorbeeld binnen 2 werkdagen, geen verplichtingen',
                'endorsement_logo' => 'channel-media/_brand/groeidiamant.jpg',
            ],

            'meta' => [
                'home_title'       => 'Website voor je nagelsalon laten maken, vollere agenda, minder appjes',
                'home_description' => 'Online afspraken, je behandelingen en je mooiste nagels in beeld. Strakke nagelsalon-websites die nieuwe klanten naar je stoel trekken, met vooraf een gratis voorbeeld.',
            ],

            'home' => [
                'hero_eyebrow' => 'Voor nagelsalons & nagelstylisten',
                'hero_title'   => 'Klanten boeken zelf. Jij blijft stylen.',
                'hero_sub'     => 'Klanten boeken 24/7 zelf hun manicure of gellak, zien je werk en prijzen, en vinden je in Google. Minder appjes, een vollere agenda.',
                'hero_cta'     => 'Gratis voorbeeld aanvragen',
                'hero_note'    => 'Binnen 2 werkdagen een voorbeeld van jouw nagelsalon. Geen verplichtingen.',

                'usps' => [
                    'Online boeken, dag en nacht',
                    'Je mooiste nagels in beeld',
                    'Gevonden in jouw stad',
                ],

                'features' => [
                    ['icon' => 'calendar', 'title' => 'Klanten boeken zelf, 24/7', 'text' => 'Ook om 23 uur op de bank reserveert je klant een gellak of manicure. Jij houdt je handen vrij.'],
                    ['icon' => 'image',    'title' => 'Je mooiste werk vooraan', 'text' => 'Een galerij van je strakste nail-art en kleuren is het eerste wat een nieuwe klant van je ziet.'],
                    ['icon' => 'star',     'title' => 'Reviews die klanten lezen', 'text' => 'Echte beoordelingen van je klanten staan op je site, zichtbaar nog voor ze appen.'],
                    ['icon' => 'location', 'title' => 'Bovenaan in jouw stad', 'text' => 'Zoekt iemand in jouw stad een nagelsalon? Dan kom jij boven in Google, niet de salon verderop.'],
                ],

                'steps' => [
                    ['title' => 'Gratis voorbeeld', 'text' => 'Binnen 2 werkdagen zetten we een voorbeeld van jouw nagelsalon klaar. Je ziet het eerst, daarna pas beslis je.'],
                    ['title' => 'Samen afstemmen',  'text' => 'In een gesprek van 20 minuten scherpen we je stijl, behandelingen en prijzen aan.'],
                    ['title' => 'Live binnen 2 weken', 'text' => 'Techniek, hosting en vindbaarheid regelen wij. Jij hoeft alleen te stylen.'],
                ],

                'proof' => 'Geen losse appjes meer over afspraken. Je klanten regelen het zelf, jij houdt je aandacht bij de nagels.',

                'reviews_title' => 'Nagelsalons die je voorgingen',
                'reviews_summary' => ['score' => '4,9', 'count' => 28, 'source' => 'geverifieerde reviews', 'noun' => 'nagelsalons'],
                'testimonials' => [
                    ['title' => 'Geen appjes meer over afspraken', 'quote' => 'Sinds de site boeken klanten zelf, ik hoef niet meer de hele dag appjes te beantwoorden. Heerlijk rustig.', 'name' => 'Lisa Bakker', 'place' => 'Nagelstudio Lumi, Rotterdam', 'when' => '2 weken geleden'],
                    ['quote' => 'Binnen twee weken stond mijn site live en de eerste online boekingen kwamen er meteen achteraan.', 'name' => 'Soraya El Amrani', 'place' => 'Nails by Soraya, Utrecht', 'when' => '1 maand geleden'],
                    ['title' => 'Mijn nail-art komt eindelijk tot z\'n recht', 'quote' => 'Eindelijk een site waar mijn werk net zo mooi op staat als in het echt. Klanten kiezen nu vaker de luxere sets.', 'name' => 'Demi van Leeuwen', 'place' => 'Studio Glam, Amsterdam', 'when' => '3 weken geleden'],
                    ['quote' => 'Top geholpen van begin tot eind. Ik hoefde alleen wat foto\'s van mijn werk aan te leveren.', 'name' => 'Priya Sharma', 'place' => 'Pink Polish, Eindhoven', 'when' => '1 maand geleden'],
                    ['title' => 'Eindelijk goed vindbaar', 'quote' => 'We staan nu bovenaan in Google voor nagelsalon in onze stad. Merkbaar meer nieuwe klanten.', 'name' => 'Esmee de Wit', 'place' => 'Beauty & Nails, Den Haag', 'when' => '2 maanden geleden'],
                    ['quote' => 'Nette mensen, snel geregeld en een eerlijke prijs. Geen lange contracten, dat sprak me aan.', 'name' => 'Chantal Visser', 'place' => 'Nagelsalon Chic, Groningen', 'when' => '2 maanden geleden'],
                ],

                'faq' => [
                    ['Wat kost een nagelsalon-website?', 'Je krijgt vooraf een vast maandbedrag, zonder eenmalige bouwkosten en zonder verrassingen. Vraag een gratis voorbeeld aan, dan hoor je meteen wat het voor jouw salon kost.'],
                    ['Hoe snel staat mijn site live?', 'Binnen 2 werkdagen zetten we een gratis voorbeeld klaar. Ga je akkoord, dan staat je site meestal binnen 2 weken live.'],
                    ['Wat moet ik zelf doen?', 'Bijna niets. Techniek, hosting, teksten en vindbaarheid regelen wij. Jij levert je logo, een paar foto’s van je werk en je prijzen aan.'],
                    ['Zit ik vast aan een lang contract?', 'Nee. Geen lange contracten en geen opzegtermijn. Je blijft omdat het werkt, niet omdat het moet.'],
                    ['Kan ik mijn huidige domeinnaam houden?', 'Ja. We koppelen je bestaande domein aan de nieuwe site, of regelen een nieuwe naam als je dat wilt. Je e-mail blijft gewoon werken.'],
                    ['Werken jullie ook in mijn stad?', 'Ja, we maken nagelsalon-websites door heel Nederland. Alles gaat op afstand, dus je hebt er geen reistijd of gedoe mee.'],
                ],
            ],

            'about' => [
                'title' => 'Nagelsalon-websites door mensen die je vak snappen',
                'lead'  => 'Wij maken strakke websites voor nagelsalons en nagelstylisten door heel Nederland.',
                'body'  => [
                    'Online afsprakensysteem, een galerij van je werk en een prijslijst die je zelf bijwerkt. Geen technisch gedoe, geen lange contracten.',
                    'We beginnen met een gratis voorbeeld van jouw nagelsalon, zodat je precies ziet wat je krijgt voordat je iets beslist.',
                ],
                'stats' => [
                    ['value' => '2 dagen', 'label' => 'tot een voorbeeld'],
                    ['value' => '2 weken', 'label' => 'tot live'],
                    ['value' => 'heel NL', 'label' => 'werkgebied'],
                ],

                // Zelfde bureau-team als de andere channels. Foto's hergebruikt uit
                // barbershop (zelfde mensen); SVG-avatar als fallback.
                'team' => [
                    [
                        'name' => 'Sem de Vries', 'role' => 'Oprichter', 'image' => 'team1',
                        'bio'  => 'Bouwt sites die de agenda vullen, van eerste idee tot live.',
                        'avatar' => ['skin' => '#e8b48f', 'hair' => '#2b2018', 'beard' => '#2b2018'],
                    ],
                    [
                        'name' => 'Damian Bakker', 'role' => 'Webdesigner', 'image' => 'team2',
                        'bio'  => 'Vertaalt jouw stijl naar een strakke site die klopt met je salon.',
                        'avatar' => ['skin' => '#c98a5e', 'hair' => '#1a1410', 'beard' => '#1a1410'],
                    ],
                    [
                        'name' => 'Younes El Idrissi', 'role' => 'Developer', 'image' => 'team3',
                        'bio'  => 'Zorgt dat online boeken soepel werkt, op elk scherm.',
                        'avatar' => ['skin' => '#a86a42', 'hair' => '#15110d', 'beard' => '#15110d'],
                    ],
                    [
                        'name' => 'Rico van Dijk', 'role' => 'SEO & vindbaarheid', 'image' => 'team4',
                        'bio'  => 'Brengt je bovenaan in Google als iemand in jouw stad zoekt.',
                        'avatar' => ['skin' => '#f0c9a6', 'hair' => '#6b4a2a', 'beard' => '#7a5532'],
                    ],
                    [
                        'name' => 'Milan Jansen', 'role' => 'Klantcontact', 'image' => 'team5',
                        'bio'  => 'Je vaste aanspreekpunt, van eerste voorbeeld tot na de lancering.',
                        'avatar' => ['skin' => '#e8b48f', 'hair' => '#9a948c', 'beard' => '#9a948c'],
                    ],
                    [
                        'name' => 'Tess Hofman', 'role' => 'Content & copy', 'image' => 'team6',
                        'bio'  => 'Schrijft teksten die klanten overtuigen om te boeken.',
                        'avatar' => ['skin' => '#f0c9a6', 'hair' => '#3a2a1a', 'beard' => null, 'long' => true],
                    ],
                ],
            ],

            'places' => [
                'h1'         => 'Nagelsalon-websites, door heel Nederland',
                'intro'      => 'Van Groningen tot Maastricht bouwen we strakke websites voor nagelsalons en nagelstylisten. Kies je stad, of vraag direct een gratis voorbeeld aan.',
                'city_h1'    => 'Nagelsalon-website laten maken in :city',
                'city_intro' => 'Nagelsalon in :city? Wij bouwen je een strakke site met online boeken, je behandelingen en je werk in beeld. We zetten vooraf een gratis voorbeeld van jouw salon klaar, zodat je ziet wat je krijgt voor je iets beslist.',
                'service'    => 'nagelsalon-website',
            ],

            'images' => [
                'hero'   => 'Een Nederlandse nagelstyliste die geconcentreerd een gellak-behandeling doet bij een klant in een lichte, moderne nagelsalon. Warm natuurlijk licht, focus op de handen en de nagels, van opzij, niet frontaal.',
                'detail' => 'Close-up van mooi verzorgde handen met strakke gellak-nagels in een zachte kleur, of een penseel dat nail-art aanbrengt, elegante nagelsalon-details met natuurlijk licht.',
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
                'phone'     => '088-2545101',
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

                'proof' => '“Klanten boeken nu zelf online, scheelt me uren bellen.” Kapsalon in Amersfoort',
            ],

            'about' => [
                'title' => 'Salonwebsites die bij jouw stijl passen',
                'lead'  => 'We maken websites voor kappers, nagelstudio\'s en beautyzaken door heel Nederland.',
                'body'  => [
                    'Een strakke site, online afsprakensysteem en een galerij van je werk, zonder gedoe en zonder lange contracten.',
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
                'city_intro' => 'Heb je een kapsalon of beautyzaak in :city? Wij bouwen een stijlvolle website met online boeken, en zetten vooraf een gratis voorbeeld van jóuw salon in :city klaar.',
                'service'    => 'salonwebsite',
            ],

            // Groeidiamant-fase-overrides op de homepage. Geen entry = de 'home'
            // hierboven (fase 'website'). SEO-instap: /groeifase/webshop.
            'facets' => [
                'webshop' => [
                    'hero_eyebrow' => 'Voor kappers die al een website hebben',
                    'hero_title'   => 'Verkoop je producten online, naast je salon',
                    'hero_sub'     => 'Je hebt al een site; tijd voor de volgende stap. Een webshop voor je haarproducten en cadeaubonnen, netjes gekoppeld aan je bestaande site.',
                    'hero_cta'     => 'Gratis voorbeeld van je webshop',
                    'hero_note'    => 'Je huidige website blijft, we bouwen de webshop eraan vast.',
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
                    'proof' => '“De cadeaubonnen lopen online storm, vooral rond de feestdagen.” Kapsalon in Bussum',
                ],
            ],

            'view' => null,
        ],

        /* ───────────────── DAMESKAPPER — ECHTE 1-PAGE VOORBEELDSITE ───────────
         * Geen marketing-site maar een demo van hoe de site van de klant zélf
         * wordt (toon je tijdens de afspraak). Bespoke view: channels.dameskapper.home.
         */
        /* ──────────── ECHTE SALON-SITE (geen lead-gen, eigen klanten) ──────────
         * Een echte schoonheidssalon-website voor de klanten van de salon zelf:
         * behandelingen tonen + afspraak maken. Geen "verkoop een website"-flow,
         * geen demo-flag. Bespoke standalone view. Content (behandelingen/uren/
         * reviews) staat in de view; thema + NAW hier. */
        'schoonheidssalon' => [
            'name'    => 'Belle Peau',
            'branche' => 'kapper_beauty',
            'domain'  => null,
            'status'  => 'draft',
            'locale'  => 'nl',

            'theme' => [
                'primary' => '#d4628a',          // vrolijk rose (beauty)
                'accent'  => '#f6b8cf',          // zachte blush
                'cta'     => '#e0457e',          // levendig hot-rose: knoppen (conversie-pop)
                'on_cta'  => '#ffffff',
                'ink'     => '#3d2530',           // warme diep-plum
                'muted'   => '#796470',           // donkerder mauve, beter leesbaar
                'bg'      => '#fff5f8',           // zacht roze-wit
                'surface' => '#ffffff',
                'font'    => "'Jost', system-ui, sans-serif",
                'font_display' => "'Cormorant Garamond', Georgia, serif",
                'font_url'=> 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Jost:wght@300;400;500;600&display=swap',
                'radius'  => '12px',
            ],

            'brand' => [
                'phone'       => '088-2545101',
                'email'       => 'hallo@bellepeau.nl',
                'address'     => 'Brinklaan 24, Bussum',
                'booking_url' => '',             // vul de boekingstool-URL in (Salonized/Treatwell); leeg = telefonisch
            ],

            'meta' => [
                'home_title'       => 'Belle Peau Huidstudio Bussum, gezichtsbehandelingen & huidverbetering',
                'home_description' => 'Huidstudio Belle Peau in Bussum: gezichtsbehandelingen, huidverbetering, wenkbrauwen, wimpers en ontspanning. Maak eenvoudig online of telefonisch een afspraak.',
            ],

            'images' => [
                'hero'   => 'Een Nederlandse schoonheidsspecialiste die rustig een gezichtsbehandeling geeft aan een ontspannen vrouwelijke klant in een lichte, serene huidstudio. Zacht natuurlijk licht, focus op de handen en de huid, kalme spa-sfeer.',
                'detail' => 'Close-up van een ontspannen gezichtsbehandeling, handen die zachtjes een masker of crème aanbrengen bij een vrouw, of mooi verzorgde huid, serene huidstudio met natuurlijk licht.',
                // Galerij-beelden (staand). Slot-namen gallery1..gallery6.
                'gallery1' => 'Een ontspannen vrouw met gesloten ogen die een gezichtsbehandeling krijgt met een verzorgend masker, in een serene huidstudio met zacht natuurlijk licht.',
                'gallery2' => 'Een schoonheidsspecialiste die geconcentreerd en zorgvuldig de gezichtshuid van een vrouwelijke klant behandelt, professioneel en kalm, natuurlijk licht.',
                'gallery3' => 'Close-up van wenkbrauwen die strak in model worden gebracht bij een vrouw, precisiewerk met een pincet, zachte studiobelichting.',
                'gallery4' => 'Close-up van een vrouw met gesloten ogen en mooie, natuurlijke wimpers tijdens een wimperbehandeling, rustige spa-sfeer.',
                'gallery5' => 'Een ontspannende nek- en schoudermassage bij een vrouw in een rustige, warme behandelkamer met zacht licht.',
                'gallery6' => 'Sfeerbeeld van een licht, serene huidstudio-interieur met planten, zachte handdoeken en nette skincare-producten, rustig en uitnodigend.',
            ],

            'view' => 'channels.schoonheidssalon.home',
        ],

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
                'phone'     => '088-2545101',
                'email'     => 'hallo@salon-lumiere.nl',
                'address'   => 'Brinklaan 12, Bussum',
            ],

            'meta' => [
                'home_title'       => 'Salon Lumière, dameskapper in Bussum',
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
                'phone'     => '088-2545101',
                'email'     => 'info@brinkbarbers.nl',
                'address'   => 'Brinklaan 48, Bussum',
            ],

            'meta' => [
                'home_title'       => 'Brink Barbers, herenkapper & barbershop in Bussum',
                'home_description' => 'Knippen, baard trimmen en scheren met het mes bij Brink Barbers in Bussum. Boek eenvoudig online je afspraak.',
            ],

            'view' => 'channels.herenkapper.home',
        ],

    ],
];
