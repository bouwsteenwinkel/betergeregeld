<?php

/**
 * Productverhalen voor de loodgieter-triggersite. Eén bron voor:
 *  - de overzichts-home (/)            → toont per product het "Zo kan het worden"-blok
 *  - de productlandingspagina's (/{facet}) → toont één product volledig (hero + pijn +
 *    "Zo kan het worden" + funnel), waar ads/SEO op landen.
 *
 * De demo ("zo zou het eruitzien") staat los onder /voorbeeld/{facet}.
 *
 * Zelfde opzet als badkamer_landings.php: we verkopen een weboplossing AAN de
 * loodgieter. De verkoopboodschap is generiek; alleen de vaktermen, het
 * voorbeeldmerk (Waterwerk) en de producten zijn op loodgieters toegespitst.
 *
 * 'gallery' bevat SLOT-namen (channel-image-slots), niet losse URL's, omdat een
 * config statisch is; de view resolvet ze via $site->image($slot).
 */
return [

    'website' => [
        'hero' => [
            'eyebrow' => 'Website laten maken',
            'title'   => 'Een website voor je loodgietersbedrijf die klussen oplevert',
            'sub'     => 'Word gevonden als iemand met spoed een loodgieter zoekt in jouw regio, en laat met een strakke site meteen zien dat je snel, vakkundig en betrouwbaar bent.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google in je eigen regio',
                'Je spoedservice en tarieven meteen duidelijk',
                'Offerteaanvragen rechtstreeks in je mailbox',
            ],
        ],
        'pains' => [
            ['title' => 'Je site is verouderd of je hebt er geen', 'text' => 'Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een klant sneller voor een concurrent die er strak en betrouwbaar uitziet.'],
            ['title' => 'Je bent niet vindbaar in Google', 'text' => 'Wie een loodgieter zoekt in jouw plaats moet jóu vinden, niet de concurrent die wél bovenaan staat.'],
            ['title' => 'Aanvragen blijven uit', 'text' => 'Zonder duidelijke bel- en aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klus.'],
        ],
        'zkhw' => [
            'label'        => 'Website',
            'title'        => 'Zo kan het worden: je snelle service én je vakwerk online',
            'intro'        => 'Loodgieterswerk draait om vertrouwen en snelheid. Je site laat allebei zien: de vakman achter het werk en je 24/7 bereikbaarheid, zodat een klant met spoed meteen jóu belt. We hebben een compleet voorbeeld klaargezet. Klik erdoorheen en stel je voor dat het je eigen bedrijf is.',
            'brand'        => 'Waterwerk',
            'heroTitle'    => 'Lekkage, verstopping of storing? Vandaag nog een vakman aan de deur',
            'urlLabel'     => 'jouw-loodgietersbedrijf.nl',
            'ctaLabel'     => 'Bekijk het volledige voorbeeld',
            'imageSlot'    => 'website-preview',
            'galleryLabel' => 'Recent uitgevoerde klussen',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Meer aanvragen en klanten', 'text' => 'Een professionele website die vertrouwen wekt en bezoekers omzet in klanten.'],
                ['title' => 'Tijdwinst en gemak', 'text' => 'Wij regelen ontwerp, teksten, foto\'s en techniek. Jij hoeft alleen goed te keuren.'],
                ['title' => 'Beter gevonden in Google', 'text' => 'Hogere posities in Google zorgen voor meer zichtbaarheid en lokale klanten.'],
                ['title' => 'Volledig verzorgd', 'text' => 'Hosting, beveiliging, updates en back-ups. Jij hebt er geen omkijken naar.'],
                ['title' => 'Kostenbesparend', 'text' => 'Geen dure bureaus of losse freelancers. Alles in één pakket, voor een vaste prijs.'],
                ['title' => 'Klaar om te groeien', 'text' => 'Uit te breiden met o.a. webshop, online afspraken en automations wanneer jij groeit.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Webshop laten maken',
            'title'   => 'Een webshop voor je loodgietersbedrijf, naast je klussen',
            'sub'     => 'Verkoop kranen, sanitair en cv-onderdelen online. Je assortiment is 24/7 open, met iDEAL en bezorgen of afhalen.',
            'note'    => 'Gekoppeld aan je site en voorraad, dus geen dubbele administratie',
            'usps'    => [
                'Verkoop dag en nacht door',
                'Veilig betalen met iDEAL of op rekening',
                'Bezorgen door heel Nederland of afhalen',
            ],
        ],
        'pains' => [
            ['title' => 'Je verdient nu niks naast je uren', 'text' => 'Klanten kopen hun kranen en sanitair online bij een ander, terwijl jij het net zo goed kunt leveren én plaatsen.'],
            ['title' => 'Losse verkoop kost veel tijd', 'text' => 'Mailtjes, prijsopgaven en pinnen aan de deur. Online rekent de klant zelf af.'],
            ['title' => 'Aannemers kopen bij de groothandel', 'text' => 'Met een webshop op rekening bestellen collega\'s en aannemers voortaan bij jou.'],
        ],
        'zkhw' => [
            'label'        => 'Webshop',
            'title'        => 'Zo kan het worden: je assortiment 24/7 online',
            'intro'        => 'Naast klussen ook producten verkopen? Klanten bestellen zelf kranen, sanitair en cv-onderdelen, betalen met iDEAL en kiezen bezorgen of afhalen. Bekijk het webshop-voorbeeld en zie hoe jouw assortiment online staat.',
            'brand'        => 'Waterwerk',
            'heroTitle'    => 'Bestel kranen en sanitair online, bezorgd of afgehaald',
            'urlLabel'     => 'shop.jouw-loodgietersbedrijf.nl',
            'navCta'       => 'Winkelmand',
            'heroBtn'      => 'In winkelmand',
            'ctaLabel'     => 'Bekijk het webshop-voorbeeld',
            'imageSlot'    => 'webshop-preview',
            'galleryLabel' => 'Populair in de shop',
            'gallery'      => [['slot' => 'gallery4', 'price' => '€ 179'], ['slot' => 'gallery5', 'price' => '€ 129'], ['slot' => 'gallery6', 'price' => '€ 34,50']],
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
            'title'   => 'Laat klanten hun klus zelf volgen',
            'sub'     => 'Klanten plannen zelf hun afspraak, volgen de status en vinden alle documenten op één plek. Jij belt en mailt minder.',
            'note'    => 'Eigen inlog voor elke klant, gekoppeld aan hun opdracht',
            'usps'    => [
                'Afspraken zelf inplannen, ook buiten kantooruren',
                'Altijd inzicht in de status van de klus',
                'Minder telefoontjes en heen-en-weer gemail',
            ],
        ],
        'pains' => [
            ['title' => 'Veel gebel over de afspraak', 'text' => 'Klanten willen weten wanneer je komt en hoe ver het is. Dat kost je elke week uren aan de telefoon.'],
            ['title' => 'Documenten raken kwijt', 'text' => 'Offerte, facturen en garantie zwerven door de mailbox van je klant. Zet ze op één plek.'],
            ['title' => 'Afspraken maken kost heen-en-weer', 'text' => 'Laat de klant zelf een moment kiezen dat ook in jouw agenda past.'],
        ],
        'zkhw' => [
            'label'        => 'Portaal & afspraken',
            'title'        => 'Zo kan het worden: klanten regelen het zelf',
            'intro'        => 'Klanten plannen zelf hun afspraak in, volgen de klus en vinden alle documenten in een eigen omgeving. Dat scheelt jou telefoontjes en heen-en-weer gemail. Bekijk het voorbeeld van zo\'n klantenportaal.',
            'brand'        => 'Waterwerk',
            'heroTitle'    => 'Plan je afspraak en volg je klus in je eigen omgeving',
            'urlLabel'     => 'mijn.jouw-loodgietersbedrijf.nl',
            'navCta'       => 'Inloggen',
            'heroBtn'      => 'Mijn omgeving',
            'ctaLabel'     => 'Bekijk het portaal-voorbeeld',
            'imageSlot'    => 'klantenportaal-preview',
            'galleryLabel' => 'Je opdracht in beeld',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Alles overzichtelijk op één plek', 'text' => 'Opdrachten, afspraken, documenten en berichten centraal en altijd beschikbaar.'],
                ['title' => 'Altijd up-to-date', 'text' => 'Je ziet in real-time de status, planning en belangrijke updates.'],
                ['title' => 'Snelle en duidelijke communicatie', 'text' => 'Direct contact met je aanspreekpunt via het portaal, zonder ruis of vertraging.'],
                ['title' => 'Alle documenten binnen handbereik', 'text' => 'Offertes, facturen en garantiebewijzen eenvoudig terugvinden.'],
                ['title' => 'Acties en taken helder', 'text' => 'Je weet precies wat er van je verwacht wordt en wat de volgende stap is.'],
                ['title' => 'Meer controle en grip', 'text' => 'Inzicht in kosten, planningen en beslissingen, voor een soepel en zorgeloos proces.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Administratie automatiseren',
            'title'   => 'Minder papierwerk in je loodgietersbedrijf',
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
            'brand'        => 'Waterwerk',
            'heroTitle'    => 'Minder tijd achter de laptop, meer tijd op de klus',
            'urlLabel'     => 'app.jouw-loodgietersbedrijf.nl',
            'navCta'       => 'Dashboard',
            'heroBtn'      => 'Bekijk demo',
            'ctaLabel'     => 'Bekijk het automatisering-voorbeeld',
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
            'title'   => 'Een assistent voor je loodgietersbedrijf die nooit een aanvraag mist',
            'sub'     => 'De assistent neemt telefoon en chat aan, vraagt door naar de storing en de spoed en bereidt je offerte voor. Dag en nacht, in gewoon Nederlands.',
            'note'    => 'Praat in jouw eigen toon, jij houdt de controle',
            'usps'    => [
                'Neemt telefoon en chat aan, 24 uur per dag',
                'Nooit meer een spoedklus missen buiten kantooruren',
                'Bereidt een eerste offerte voor uit foto\'s',
            ],
        ],
        'pains' => [
            ['title' => 'Je kunt niet altijd opnemen', 'text' => 'Lig je onder een wastafel, dan gaat de spoedklus vaak naar wie wél opneemt. De assistent vangt dat op.'],
            ['title' => 'Veel vragen, weinig serieuze klussen', 'text' => 'De assistent vraagt door, zodat je alleen de kansrijke aanvragen terugbelt.'],
            ['title' => 'Reviews vragen vergeet je', 'text' => 'Na de klus vraagt de assistent automatisch om een Google-review.'],
        ],
        'zkhw' => [
            'label'        => 'AI',
            'title'        => 'Zo kan het worden: een assistent die nooit een aanvraag mist',
            'intro'        => 'Bel je een keer niet op tijd terug, dan is de spoedklus vaak al weg. Een slimme assistent neemt telefoon en chat aan, vraagt door en bereidt je offerte voor, dag en nacht. Bekijk hoe dat werkt op een loodgietersbedrijf.',
            'brand'        => 'Waterwerk',
            'heroTitle'    => 'Altijd bereikbaar, ook als jij aan het werk bent',
            'urlLabel'     => 'jouw-loodgietersbedrijf.nl',
            'navCta'       => 'Chat',
            'heroBtn'      => 'Stel je vraag',
            'ctaLabel'     => 'Bekijk het AI-voorbeeld',
            'imageSlot'    => 'ai-preview',
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
