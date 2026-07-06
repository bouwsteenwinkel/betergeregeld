<?php

/**
 * KLUSBEDRIJF — facet-content voor de bespoke _sales/_landing-pagina's.
 * Zelfde structuur als config/badkamer_landings.php, met teksten en diensten
 * die op een klusbedrijf slaan.
 *
 * NB: nog GEEN 'imageSlot' per facet — zolang die ontbreekt toont de
 * "zo-kan-het-worden"-sectie de CSS-mockup (met de klusbedrijf-hero als banner).
 * Zodra er echte mockup-beelden zijn: plaats ze als slot 'website-preview',
 * 'webshop-preview', enz. en voeg per facet 'imageSlot' toe (net als badkamer).
 */

return [

    'website' => [
        'hero' => [
            'eyebrow' => 'Website laten maken',
            'title'   => 'Een website voor je klusbedrijf die klussen oplevert',
            'sub'     => 'Word gevonden als iemand een klusser zoekt in jouw regio, en laat je vakwerk zien met een strakke site die aanvragen binnenhaalt.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google in je eigen regio',
                'Je vakwerk en afgeronde klussen in beeld',
                'Aanvragen rechtstreeks in je mailbox',
            ],
        ],
        'pains' => [
            ['title' => 'Je site is verouderd of je hebt er geen', 'text' => 'Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een klant sneller voor een klusbedrijf dat er strak en betrouwbaar uitziet.'],
            ['title' => 'Je bent niet vindbaar in Google', 'text' => 'Wie een klusser zoekt in jouw plaats moet jóu vinden, niet de concurrent een dorp verderop.'],
            ['title' => 'Aanvragen blijven uit', 'text' => 'Zonder een duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klus.'],
        ],
        'zkhw' => [
            'label'        => 'Website',
            'title'        => 'Zo kan het worden: je vakwerk professioneel online',
            'intro'        => 'Een klant kiest een klusbedrijf dat betrouwbaar oogt en makkelijk te bereiken is. Je site laat je afgeronde klussen, reviews en een duidelijke aanvraagknop zien. We hebben een compleet voorbeeld klaargezet. Klik erdoorheen en stel je voor dat het je eigen bedrijf is.',
            'brand'        => 'Klusbedrijf Vakwerk',
            'heroTitle'    => 'Elke klus vakkundig geregeld, groot of klein',
            'urlLabel'     => 'jouw-klusbedrijf.nl',
            'ctaLabel'     => 'Bekijk het volledige voorbeeld',
            'galleryLabel' => 'Recent afgeronde klussen',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Meer aanvragen en klanten', 'text' => 'Een professionele website die vertrouwen wekt en bezoekers omzet in klanten.'],
                ['title' => 'Vertrouwen door vakmanschap', 'text' => 'Je afgeronde klussen, reviews en werkwijze in beeld. Daar durven klanten op te bouwen.'],
                ['title' => 'Beter gevonden in Google', 'text' => 'Hogere posities zorgen voor meer zichtbaarheid en klussen uit je eigen regio.'],
                ['title' => 'Dag en nacht aanvragen', 'text' => 'Een duidelijke knop die ook \'s avonds nieuwe aanvragen oplevert, op mobiel en desktop.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Online verkoop laten maken',
            'title'   => 'Verkoop vaste klussen en onderhoud online',
            'sub'     => 'Standaardklussen met een vaste prijs, montagepakketten en onderhoudsabonnementen. Klanten boeken en betalen zelf met iDEAL.',
            'note'    => 'Gekoppeld aan je site en planning, dus geen dubbele administratie',
            'usps'    => [
                'Vaste klussen dag en nacht geboekt',
                'Direct betaald met iDEAL, geen offerte-gedoe',
                'Automatisch in je planning',
            ],
        ],
        'pains' => [
            ['title' => 'Kleine klussen kosten veel offertewerk', 'text' => 'Voor elk lampje of kraantje een offerte maken kost meer tijd dan de klus zelf. Zet ze online tegen een vaste prijs.'],
            ['title' => 'Boeken kan alleen overdag', 'text' => 'Buiten kantooruren loop je klussen mis van klanten die juist \'s avonds regelen.'],
            ['title' => 'Onderhoud loop je mis', 'text' => 'Terugkerend onderhoud als abonnement verkopen levert vaste omzet op die je nu laat liggen.'],
        ],
        'zkhw' => [
            'label'        => 'Webshop',
            'title'        => 'Zo kan het worden: je klussen 24/7 te boeken',
            'intro'        => 'Naast maatwerk ook standaardklussen online verkopen? Klanten kiezen een vaste klus of onderhoudsabonnement, betalen met iDEAL en staan meteen in je planning. Bekijk hoe jouw aanbod online staat.',
            'brand'        => 'Klusbedrijf Vakwerk',
            'heroTitle'    => 'Boek je klus online: vaste prijs, direct geregeld',
            'urlLabel'     => 'shop.jouw-klusbedrijf.nl',
            'navCta'       => 'Winkelmand',
            'heroBtn'      => 'In winkelmand',
            'ctaLabel'     => 'Bekijk het webshop-voorbeeld',
            'galleryLabel' => 'Populaire klussen',
            'gallery'      => [['slot' => 'gallery1', 'price' => '€ 75'], ['slot' => 'gallery2', 'price' => '€ 120'], ['slot' => 'gallery3', 'price' => '€ 45']],
            'bullets'      => [
                ['title' => 'Verkoop dag en nacht door', 'text' => 'Klanten boeken ook \'s avonds en in het weekend, zonder dat jij er iets voor hoeft te doen.'],
                ['title' => 'Veilig betalen met iDEAL', 'text' => 'Direct afgerekend, geen offertes en geen openstaande facturen.'],
                ['title' => 'Onderhoudsabonnementen', 'text' => 'Vaste, terugkerende omzet uit onderhoud dat je anders zou mislopen.'],
                ['title' => 'Alles gekoppeld', 'text' => 'Elke boeking staat meteen in je planning en administratie.'],
            ],
        ],
    ],

    'klantenportaal' => [
        'hero' => [
            'eyebrow' => 'Klantenportaal laten maken',
            'title'   => 'Laat klanten hun klus zelf volgen',
            'sub'     => 'Klanten plannen zelf een afspraak, volgen de status van hun klus en vinden offertes en facturen op één plek. Jij belt en appt minder.',
            'note'    => 'Eigen inlog voor elke klant, gekoppeld aan hun klus',
            'usps'    => [
                'Afspraken zelf inplannen, ook buiten kantooruren',
                'Altijd inzicht in status en planning',
                'Minder appjes en heen-en-weer gebel',
            ],
        ],
        'pains' => [
            ['title' => 'Veel geappt over de planning', 'text' => 'Klanten willen weten wanneer je komt en hoe ver het is. Dat kost je elke week uren.'],
            ['title' => 'Offertes en facturen raken kwijt', 'text' => 'Documenten zwerven door de mailbox van je klant. Zet ze overzichtelijk op één plek.'],
            ['title' => 'Afspraken maken kost heen-en-weer', 'text' => 'Laat de klant zelf een moment kiezen dat ook in jouw agenda past.'],
        ],
        'zkhw' => [
            'label'        => 'Klantenportaal',
            'title'        => 'Zo kan het worden: klanten regelen het zelf',
            'intro'        => 'Klanten plannen zelf hun afspraak, volgen de status van hun klus en vinden offertes en facturen in een eigen omgeving. Dat scheelt jou telefoontjes en geapp. Bekijk het voorbeeld van zo\'n klantenportaal.',
            'brand'        => 'Klusbedrijf Vakwerk',
            'heroTitle'    => 'Plan je afspraak en volg je klus in je eigen omgeving',
            'urlLabel'     => 'mijn.jouw-klusbedrijf.nl',
            'navCta'       => 'Inloggen',
            'heroBtn'      => 'Mijn omgeving',
            'ctaLabel'     => 'Bekijk het portaal-voorbeeld',
            'galleryLabel' => 'Jouw klus in beeld',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Alles overzichtelijk op één plek', 'text' => 'Afspraken, status, offertes en facturen centraal en altijd beschikbaar.'],
                ['title' => 'Afspraken zelf inplannen', 'text' => 'Klanten kiezen zelf een moment voor de klus, ook \'s avonds.'],
                ['title' => 'Status van de klus', 'text' => 'Klanten zien in real-time hoe ver de klus is, zonder te bellen.'],
                ['title' => 'Minder telefoontjes', 'text' => 'Klanten vinden hun antwoord zelf, jij houdt tijd over voor het werk.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Administratie automatiseren',
            'title'   => 'Minder papierwerk in je klusbedrijf',
            'sub'     => 'Offertes, facturen en planning die zichzelf doen. Een aanvraag wordt een offerte, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld.',
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
            ['title' => 'Dubbel werk tussen je systemen', 'text' => 'Aanvraag, planning en boekhouding apart bijhouden? Die kunnen met elkaar praten.'],
        ],
        'zkhw' => [
            'label'        => 'Automatisering',
            'title'        => 'Zo kan het worden: papierwerk dat zichzelf doet',
            'intro'        => 'Offertes, planning en facturen kosten je nu uren. Laat de techniek dat overnemen: een aanvraag wordt een offerte, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld. Bekijk hoe de back-office voor je werkt.',
            'brand'        => 'Klusbedrijf Vakwerk',
            'heroTitle'    => 'Minder tijd achter de laptop, meer tijd op de klus',
            'urlLabel'     => 'app.jouw-klusbedrijf.nl',
            'navCta'       => 'Dashboard',
            'heroBtn'      => 'Bekijk demo',
            'ctaLabel'     => 'Bekijk het automatisering-voorbeeld',
            'galleryLabel' => 'Loopt automatisch',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Offerte ✓'], ['slot' => 'gallery2', 'price' => 'Factuur ✓'], ['slot' => 'gallery3', 'price' => 'Planning ✓']],
            'bullets'      => [
                ['title' => 'Minder handmatig werk', 'text' => 'Automatiseer terugkerende taken en bespaar tot wel 70% tijd.'],
                ['title' => 'Facturen en herinneringen vanzelf', 'text' => 'Op het juiste moment verstuurd, jij hoeft niet achter je geld aan.'],
                ['title' => 'Offertes in 10 minuten', 'text' => 'Standaardposten klaarzetten en versturen, geen uren typen.'],
                ['title' => 'Alles gekoppeld', 'text' => 'Website, agenda en boekhouding werken met elkaar mee, geen dubbel werk.'],
            ],
        ],
    ],

    'ai' => [
        'hero' => [
            'eyebrow' => 'AI-assistent',
            'title'   => 'Een assistent voor je klusbedrijf die nooit een aanvraag mist',
            'sub'     => 'De assistent neemt telefoon en chat aan, vraagt door naar wat de klant nodig heeft en bereidt je offerte voor. Dag en nacht, in gewoon Nederlands.',
            'note'    => 'Praat in jouw eigen toon, jij houdt de controle',
            'usps'    => [
                'Neemt telefoon en chat aan, 24 uur per dag',
                'Nooit meer een aanvraag missen terwijl je op de klus staat',
                'Bereidt een eerste offerte voor uit foto\'s',
            ],
        ],
        'pains' => [
            ['title' => 'Je kunt niet opnemen op de klus', 'text' => 'Sta je op de ladder, dan gaat de klus vaak naar wie wél opneemt. De assistent vangt dat op.'],
            ['title' => 'Veel vragen, weinig serieuze klussen', 'text' => 'De assistent vraagt door, zodat je alleen de kansrijke aanvragen terugbelt.'],
            ['title' => 'Reviews vragen vergeet je', 'text' => 'Na de klus vraagt de assistent automatisch om een Google-review.'],
        ],
        'zkhw' => [
            'label'        => 'AI',
            'title'        => 'Zo kan het worden: een assistent die nooit een aanvraag mist',
            'intro'        => 'Bel je een keer niet op tijd terug, dan is de klus vaak al weg. Een slimme assistent neemt telefoon en chat aan, vraagt door en bereidt je offerte voor, dag en nacht. Bekijk hoe dat werkt op een klusbedrijf.',
            'brand'        => 'Klusbedrijf Vakwerk',
            'heroTitle'    => 'Altijd bereikbaar, ook als jij aan het klussen bent',
            'urlLabel'     => 'jouw-klusbedrijf.nl',
            'navCta'       => 'Chat',
            'heroBtn'      => 'Stel je vraag',
            'ctaLabel'     => 'Bekijk het AI-voorbeeld',
            'galleryLabel' => 'De assistent aan het werk',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Chat ✓'], ['slot' => 'gallery2', 'price' => 'Afspraak ✓'], ['slot' => 'gallery3', 'price' => 'Offerte ✓']],
            'bullets'      => [
                ['title' => 'Neemt op als jij niet kan', 'text' => 'Telefoon en chat worden 24/7 beantwoord, ook \'s avonds en in het weekend.'],
                ['title' => 'Bereidt je offerte voor', 'text' => 'Uit een paar foto\'s een eerste inschatting van werk en materiaal.'],
                ['title' => 'Filtert serieuze aanvragen', 'text' => 'De assistent vraagt door, zodat jij alleen de kansrijke klussen terugbelt.'],
                ['title' => 'Verzamelt reviews', 'text' => 'Vraagt na de klus automatisch om een Google-review.'],
            ],
        ],
    ],

];
