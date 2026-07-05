<?php

/**
 * Productverhalen voor de badkamerspecialist-triggersite. Eén bron voor:
 *  - de overzichts-home (/)            → toont per product het "Zo kan het worden"-blok
 *  - de productlandingspagina's (/{facet}) → toont één product volledig (hero + pijn +
 *    "Zo kan het worden" + funnel), waar ads/SEO op landen.
 *
 * De demo ("zo zou het eruitzien") staat los onder /voorbeeld/{facet}.
 *
 * 'gallery' bevat SLOT-namen (channel-image-slots), niet losse URL's, omdat een
 * config statisch is; de view resolvet ze via $site->image($slot).
 */
return [

    'website' => [
        'hero' => [
            'eyebrow' => 'Website laten maken',
            'title'   => 'Een website voor je badkamerbedrijf die klussen oplevert',
            'sub'     => 'Word gevonden als iemand een badkamer zoekt in jouw regio, en laat je vakwerk zien met een strakke site die aanvragen binnenhaalt.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google in je eigen regio',
                'Je mooiste badkamers in beeld',
                'Offerteaanvragen rechtstreeks in je mailbox',
            ],
        ],
        'pains' => [
            ['title' => 'Je site is verouderd of je hebt er geen', 'text' => 'Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een klant sneller voor een concurrent die er strak uitziet.'],
            ['title' => 'Je bent niet vindbaar in Google', 'text' => 'Wie een badkamerbedrijf zoekt in jouw plaats moet jóu vinden, niet de concurrent.'],
            ['title' => 'Aanvragen blijven uit', 'text' => 'Zonder duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klus.'],
        ],
        'zkhw' => [
            'label'        => 'Website',
            'title'        => 'Zo kan het worden: je vakwerk én je mooiste badkamers online',
            'intro'        => 'Een badkamer is techniek én stijl. Je site laat allebei zien: de vakman achter het werk en de afgewerkte badkamers waar klanten blij van worden. We hebben een compleet voorbeeld klaargezet. Klik erdoorheen en stel je voor dat het je eigen bedrijf is.',
            'brand'        => 'BadkamerBloem',
            'heroTitle'    => 'Van oude badkamer naar afgewerkte ruimte in 10 werkdagen',
            'urlLabel'     => 'jouw-badkamerbedrijf.nl',
            'ctaLabel'     => 'Bekijk het volledige voorbeeld',
            'galleryLabel' => 'Recent afgeleverde badkamers',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Je mooiste badkamers in beeld', 'text' => 'Een galerij van afgewerkte projecten die klanten over de streep trekt.'],
                ['title' => 'Vertrouwen door vakmanschap', 'text' => 'Garanties, een vast team en een heldere werkwijze. Daar durven klanten op te bouwen.'],
                ['title' => 'Gevonden in je regio', 'text' => 'Vindbaar als iemand een badkamer zoekt bij jou in de buurt.'],
                ['title' => 'Dag en nacht aanvragen', 'text' => 'Een duidelijke knop die ook \'s avonds nieuwe aanvragen oplevert, op mobiel en desktop.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Webshop laten maken',
            'title'   => 'Een webshop voor je badkamerbedrijf, naast je klussen',
            'sub'     => 'Verkoop sanitair, tegels en complete pakketten online. Je showroom is 24/7 open, met iDEAL en bezorgen of afhalen.',
            'note'    => 'Gekoppeld aan je site en voorraad, dus geen dubbele administratie',
            'usps'    => [
                'Verkoop dag en nacht door',
                'Veilig betalen met iDEAL of op rekening',
                'Bezorgen door heel Nederland of afhalen',
            ],
        ],
        'pains' => [
            ['title' => 'Je showroom is alleen overdag open', 'text' => 'Buiten openingstijden loop je omzet mis van klanten die juist \'s avonds oriënteren.'],
            ['title' => 'Losse verkoop kost veel tijd', 'text' => 'Mailtjes, prijsopgaven en pinnen aan de balie. Online rekent de klant zelf af.'],
            ['title' => 'Aannemers kopen bij de groothandel', 'text' => 'Met een webshop op rekening bestellen collega\'s en aannemers voortaan bij jou.'],
        ],
        'zkhw' => [
            'label'        => 'Webshop',
            'title'        => 'Zo kan het worden: je showroom 24/7 online',
            'intro'        => 'Naast klussen ook producten verkopen? Klanten bestellen zelf kranen, tegels en complete pakketten, betalen met iDEAL en kiezen bezorgen of afhalen. Bekijk het webshop-voorbeeld en zie hoe jouw assortiment online staat.',
            'brand'        => 'BadkamerBloem',
            'heroTitle'    => 'Bestel sanitair en tegels online, bezorgd of afgehaald',
            'urlLabel'     => 'shop.jouw-badkamerbedrijf.nl',
            'navCta'       => 'Winkelmand',
            'heroBtn'      => 'In winkelmand',
            'ctaLabel'     => 'Bekijk het webshop-voorbeeld',
            // Kant-en-klare webshop-mockup (eigen browser-frame) i.p.v. de CSS-mockup.
            'imageSlot'    => 'webshop-preview',
            'galleryLabel' => 'Populair in de shop',
            'gallery'      => [['slot' => 'gallery4', 'price' => '€ 179'], ['slot' => 'gallery5', 'price' => '€ 385'], ['slot' => 'gallery6', 'price' => '€ 349']],
            'bullets'      => [
                ['title' => 'Verkoop dag en nacht door', 'text' => 'Klanten bestellen ook \'s avonds en in het weekend, zonder dat jij er iets voor hoeft te doen.'],
                ['title' => 'Veilig betalen met iDEAL', 'text' => 'Direct afgerekend. Vaste klanten en aannemers kunnen op rekening bestellen.'],
                ['title' => 'Bezorgen of afhalen', 'text' => 'Wat op voorraad ligt, is binnen twee werkdagen bezorgd of ligt klaar in je loods.'],
                ['title' => 'Montage bij te boeken', 'text' => 'Klant koopt online en vinkt montage aan. Zo verdien je aan het product én de plaatsing.'],
            ],
        ],
    ],

    'klantenportaal' => [
        'hero' => [
            'eyebrow' => 'Klantenportaal laten maken',
            'title'   => 'Laat klanten hun badkamerproject zelf volgen',
            'sub'     => 'Klanten plannen zelf hun afspraken, volgen de planning en het 3D-ontwerp en vinden alle documenten op één plek. Jij belt en mailt minder.',
            'note'    => 'Eigen inlog voor elke klant, gekoppeld aan hun project',
            'usps'    => [
                'Afspraken zelf inplannen, ook buiten kantooruren',
                'Altijd inzicht in planning en voortgang',
                'Minder telefoontjes en heen-en-weer gemail',
            ],
        ],
        'pains' => [
            ['title' => 'Veel gebel over de planning', 'text' => 'Klanten willen weten wanneer je komt en hoe ver het is. Dat kost je elke week uren.'],
            ['title' => 'Documenten raken kwijt', 'text' => 'Offerte, facturen en garantie zwerven door de mailbox van je klant. Zet ze op één plek.'],
            ['title' => 'Afspraken maken kost heen-en-weer', 'text' => 'Laat de klant zelf een moment kiezen dat ook in jouw agenda past.'],
        ],
        'zkhw' => [
            'label'        => 'Portaal & afspraken',
            'title'        => 'Zo kan het worden: klanten regelen het zelf',
            'intro'        => 'Klanten plannen zelf hun inmeting in, volgen de badkamer en vinden alle documenten in een eigen omgeving. Dat scheelt jou telefoontjes en heen-en-weer gemail. Bekijk het voorbeeld van zo\'n klantenportaal.',
            'brand'        => 'BadkamerBloem',
            'heroTitle'    => 'Plan je afspraak en volg je badkamer in je eigen omgeving',
            'urlLabel'     => 'mijn.jouw-badkamerbedrijf.nl',
            'navCta'       => 'Inloggen',
            'heroBtn'      => 'Mijn omgeving',
            'ctaLabel'     => 'Bekijk het portaal-voorbeeld',
            // Kant-en-klare klantportaal-mockup (eigen browser-frame) i.p.v. de CSS-mockup.
            'imageSlot'    => 'klantenportaal-preview',
            'galleryLabel' => 'Je project in beeld',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Alles overzichtelijk op één plek', 'text' => 'Projecten, afspraken, documenten en berichten centraal en altijd beschikbaar.'],
                ['title' => 'Altijd up-to-date', 'text' => 'Je ziet in real-time de voortgang, planning en belangrijke updates.'],
                ['title' => 'Snelle en duidelijke communicatie', 'text' => 'Direct contact met je projectadviseur via het portaal, zonder ruis of vertraging.'],
                ['title' => 'Alle documenten binnen handbereik', 'text' => 'Offertes, tekeningen, facturen en andere bestanden eenvoudig terugvinden.'],
                ['title' => 'Acties en taken helder', 'text' => 'Je weet precies wat er van je verwacht wordt en wat de volgende stap is.'],
                ['title' => 'Meer controle en grip', 'text' => 'Inzicht in kosten, planningen en beslissingen, voor een soepel en zorgeloos proces.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Administratie automatiseren',
            'title'   => 'Minder papierwerk in je badkamerbedrijf',
            'sub'     => 'Offertes, planning en facturen die zichzelf doen. Een aanvraag wordt een offerte, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld.',
            'note'    => 'Gekoppeld aan je site, agenda en boekhouding',
            'usps'    => [
                'Een offerte klaar in 10 minuten',
                'Facturen en herinneringen gaan automatisch',
                'Website, agenda en boekhouding werken samen',
            ],
        ],
        'pains' => [
            ['title' => 'Offertes maken kost je avonden', 'text' => 'Elke offerte opnieuw uittypen. Met standaardposten staat hij binnen 10 minuten klaar.'],
            ['title' => 'Je zit achter je geld aan', 'text' => 'Facturen en herinneringen vergeten kost geld. Laat ze automatisch op tijd de deur uit gaan.'],
            ['title' => 'Dubbel werk tussen je systemen', 'text' => 'Alles twee keer invoeren? Je website, agenda en boekhouding kunnen met elkaar praten.'],
        ],
        'zkhw' => [
            'label'        => 'Automatisering',
            'title'        => 'Zo kan het worden: papierwerk dat zichzelf doet',
            'intro'        => 'Offertes, planning en facturen kosten je nu uren. Laat de techniek dat overnemen: een aanvraag wordt een offerte, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld. Bekijk hoe de back-office voor je werkt.',
            'brand'        => 'BadkamerBloem',
            'heroTitle'    => 'Minder tijd achter de laptop, meer tijd op de bouw',
            'urlLabel'     => 'app.jouw-badkamerbedrijf.nl',
            'navCta'       => 'Dashboard',
            'heroBtn'      => 'Bekijk demo',
            'ctaLabel'     => 'Bekijk het automatisering-voorbeeld',
            // Kant-en-klare automatisering-mockup (eigen browser-frame) i.p.v. de CSS-mockup.
            'imageSlot'    => 'automatisering-preview',
            'galleryLabel' => 'Loopt automatisch',
            'gallery'      => [['slot' => 'gallery4', 'price' => 'Offerte ✓'], ['slot' => 'gallery5', 'price' => 'Factuur ✓'], ['slot' => 'gallery6', 'price' => 'Review ✓']],
            'bullets'      => [
                ['title' => 'Minder handmatig werk', 'text' => 'Automatiseer terugkerende taken en bespaar tot wel 70% tijd.'],
                ['title' => 'Minder fouten, meer kwaliteit', 'text' => 'Gestandaardiseerde workflows zorgen voor consistente en foutloze processen.'],
                ['title' => 'Snellere opvolging', 'text' => 'Automatische acties en herinneringen zorgen dat niets tussen wal en schip valt.'],
                ['title' => 'Inzicht en controle', 'text' => 'Realtime overzicht van al je processen, prestaties en besparingen.'],
                ['title' => 'Schaalbaar en toekomstbestendig', 'text' => 'Groei zonder extra personeel. Jouw processen groeien gewoon mee.'],
            ],
        ],
    ],

    'ai' => [
        'hero' => [
            'eyebrow' => 'AI-assistent',
            'title'   => 'Een assistent voor je badkamerbedrijf die nooit een aanvraag mist',
            'sub'     => 'De assistent neemt telefoon en chat aan, vraagt door naar wat de klant nodig heeft en bereidt je offerte voor. Dag en nacht, in gewoon Nederlands.',
            'note'    => 'Praat in jouw eigen toon, jij houdt de controle',
            'usps'    => [
                'Neemt telefoon en chat aan, 24 uur per dag',
                'Nooit meer een aanvraag missen buiten kantooruren',
                'Bereidt een eerste offerte voor uit foto\'s',
            ],
        ],
        'pains' => [
            ['title' => 'Je kunt niet altijd opnemen', 'text' => 'Sta je op de steiger, dan gaat de klus vaak naar wie wél opneemt. De assistent vangt dat op.'],
            ['title' => 'Veel vragen, weinig serieuze klussen', 'text' => 'De assistent vraagt door, zodat je alleen de kansrijke aanvragen terugbelt.'],
            ['title' => 'Reviews vragen vergeet je', 'text' => 'Na oplevering vraagt de assistent automatisch om een Google-review.'],
        ],
        'zkhw' => [
            'label'        => 'AI',
            'title'        => 'Zo kan het worden: een assistent die nooit een aanvraag mist',
            'intro'        => 'Bel je een keer niet op tijd terug, dan is de klus vaak al weg. Een slimme assistent neemt telefoon en chat aan, vraagt door en bereidt je offerte voor, dag en nacht. Bekijk hoe dat werkt op een badkamerbedrijf.',
            'brand'        => 'BadkamerBloem',
            'heroTitle'    => 'Altijd bereikbaar, ook als jij aan het werk bent',
            'urlLabel'     => 'jouw-badkamerbedrijf.nl',
            'navCta'       => 'Chat',
            'heroBtn'      => 'Stel je vraag',
            'ctaLabel'     => 'Bekijk het AI-voorbeeld',
            // Kant-en-klare AI-mockup (eigen browser-frame) i.p.v. de CSS-mockup.
            'imageSlot'    => 'ai-preview',
            'galleryLabel' => 'De assistent aan het werk',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Chat ✓'], ['slot' => 'gallery2', 'price' => 'Afspraak ✓'], ['slot' => 'gallery3', 'price' => 'Offerte ✓']],
            'bullets'      => [
                ['title' => 'Neemt op als jij niet kan', 'text' => 'Telefoon en chat worden 24/7 beantwoord, ook \'s avonds en in het weekend.'],
                ['title' => 'Bereidt je offerte voor', 'text' => 'Uit een paar foto\'s een eerste inschatting van werk en materiaal.'],
                ['title' => 'Filtert serieuze aanvragen', 'text' => 'De assistent vraagt door, zodat jij alleen de kansrijke klussen terugbelt.'],
                ['title' => 'Verzamelt reviews', 'text' => 'Vraagt na oplevering automatisch om een Google-review.'],
            ],
        ],
    ],

];
