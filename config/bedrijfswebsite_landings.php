<?php

/**
 * Verkooplaag voor de generieke bedrijfswebsite-triggersite (mode=dienst).
 * Beter Geregeld verkoopt een weboplossing AAN het mkb-bedrijf. Copy is
 * branche-neutraal; per-facet content voor de groeistappen (/website, /webshop,
 * /klantenportaal, /automatisering, /ai). Hoofdbeeld per facet = de *-preview-slot.
 */

return [
    'website' => [
        // Eigen SEO-titel, want de standaard (eyebrow + " voor jouw bedrijf") gaf
        // "Website laten maken voor jouw bedrijf" terwijl de HOME "Website laten maken
        // voor ondernemers" heet. Twee pagina's die om dezelfde zoekopdracht vechten:
        // Google kiest er één en onderdrukt de andere. De home heeft de meeste
        // autoriteit en houdt dus de hoofdterm; deze pagina pakt de zakelijke variant.
        // Alleen dit facet botste — webshop, klantenportaal, automatisering en ai zijn
        // van zichzelf al onderscheidend. Gemeten 03-08-2026.
        'seo_title' => 'Zakelijke website laten maken met vaste prijs',
        'hero' => [
            'eyebrow' => 'Website laten maken',
            'title'   => 'Een professionele website voor je bedrijf die klanten oplevert',
            'sub'     => 'Word gevonden als iemand jouw dienst zoekt in de regio, en laat met een strakke site zien wat je doet en waarom klanten voor jou kiezen.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google in je eigen regio',
                'Meteen duidelijk wat je doet en voor wie',
                'Aanvragen rechtstreeks in je mailbox',
            ],
        ],
        'pains' => [
            ['title' => 'Je site is verouderd of je hebt er geen', 'text' => 'Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een klant sneller voor een concurrent die er strak en betrouwbaar uitziet.'],
            ['title' => 'Je bent niet vindbaar in Google', 'text' => 'Wie jouw dienst in je plaats zoekt moet jóu vinden, niet de concurrent die wél bovenaan staat.'],
            ['title' => 'Aanvragen blijven uit', 'text' => 'Zonder duidelijke aanvraagknop haakt een geïnteresseerde af. Elke gemiste aanvraag is een misgelopen klant.'],
        ],
        'zkhw' => [
            'label'     => 'Website',
            'title'     => 'Zo kan het worden: je vak en je service online',
            'intro'     => 'Een bedrijf kies je op vertrouwen. Je site laat zien wat je doet, hoe je werkt en wat klanten van je vinden, zodat een bezoeker met een gerust hart voor jou kiest.',
            'brand'     => 'Jouw Website',
            'heroTitle' => 'Op zoek naar een betrouwbaar bedrijf? Kennismaken kan zo gepland zijn',
            'urlLabel'  => 'jouw-bedrijf.nl',
            'ctaLabel'  => 'Bekijk het volledige voorbeeld',
            'imageSlot' => 'website-preview',
            'bullets'   => [
                ['title' => 'Meer aanvragen en klanten', 'text' => 'Een professionele website die vertrouwen wekt en bezoekers omzet in klanten.'],
                ['title' => 'Tijdwinst en gemak', 'text' => 'Wij regelen ontwerp, teksten, foto\'s en techniek. Jij hoeft alleen goed te keuren.'],
                ['title' => 'Beter gevonden in Google', 'text' => 'Hogere posities in Google zorgen voor meer zichtbaarheid en lokale klanten.'],
                ['title' => 'Volledig verzorgd', 'text' => 'Hosting, beveiliging, updates en back-ups. Jij hebt er geen omkijken naar.'],
                ['title' => 'Kostenbesparend', 'text' => 'Geen dure bureaus of losse freelancers. Alles in één pakket, voor een vaste prijs.'],
                ['title' => 'Klaar om te groeien', 'text' => 'Uit te breiden met o.a. webshop, online afspraken en automatisering wanneer jij groeit.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Webshop laten maken',
            'title'   => 'Verkoop je producten en diensten online',
            'sub'     => 'Bied je producten, pakketten en cadeaubonnen online aan, direct af te rekenen.',
            'note'    => 'Gekoppeld aan je site en voorraad, dus geen dubbele administratie',
            'usps'    => [
                'Verkoop dag en nacht door',
                'Veilig betalen met iDEAL of op rekening',
                'Bezorgen door heel Nederland of afhalen',
            ],
        ],
        'pains' => [
            ['title' => 'Je verdient nu niks buiten kantooruren', 'text' => 'Klanten kopen \'s avonds elders online, terwijl jij het net zo goed kunt leveren.'],
            ['title' => 'Losse verkoop kost veel tijd', 'text' => 'Mailtjes, prijsopgaven en pinnen aan de balie. Online rekent de klant zelf af.'],
            ['title' => 'Zakelijke klanten kopen elders', 'text' => 'Met een webshop op rekening bestellen bedrijven en vaste klanten voortaan bij jou.'],
        ],
        'zkhw' => [
            'label'     => 'Webshop',
            'title'     => 'Zo kan het worden: je assortiment 24/7 online',
            'intro'     => 'Naast maatwerk ook standaard producten of diensten online aanbieden? Klanten kiezen zelf, betalen met iDEAL en zijn meteen geholpen.',
            'brand'     => 'Jouw Website',
            'heroTitle' => 'Bestel een product of pakket online',
            'urlLabel'  => 'shop.jouw-bedrijf.nl',
            'navCta'    => 'Winkelmand',
            'heroBtn'   => 'In winkelmand',
            'ctaLabel'  => 'Bekijk het webshop-voorbeeld',
            'imageSlot' => 'webshop-preview',
            'bullets'   => [
                ['title' => 'Verkoop dag en nacht door', 'text' => 'Klanten bestellen ook \'s avonds en in het weekend, zonder dat jij er iets voor hoeft te doen.'],
                ['title' => 'Veilig betalen met iDEAL', 'text' => 'Direct afgerekend. Vaste klanten en bedrijven kunnen op rekening bestellen.'],
                ['title' => 'Bezorgen of afhalen', 'text' => 'Wat op voorraad ligt, is binnen twee werkdagen bezorgd of ligt klaar om af te halen.'],
                ['title' => 'Vaste klanten bestellen zo opnieuw', 'text' => 'Een nabestelling of nieuw pakket is in een paar klikken geplaatst.'],
            ],
        ],
    ],

    'klantenportaal' => [
        'hero' => [
            'eyebrow' => 'Klantenportaal laten maken',
            'title'   => 'Laat klanten alles zelf regelen',
            'sub'     => 'Klanten plannen zelf hun afspraak, volgen de status en vinden alle documenten op één plek. Jij belt en mailt minder.',
            'note'    => 'Eigen inlog voor elke klant, gekoppeld aan hun opdracht',
            'usps'    => [
                'Afspraken zelf inplannen, ook buiten kantooruren',
                'Altijd inzicht in de status',
                'Minder telefoontjes en heen-en-weer gemail',
            ],
        ],
        'pains' => [
            ['title' => 'Veel gebel over de afspraak', 'text' => 'Klanten willen weten wanneer en hoe ver het is. Dat kost je elke week uren aan de telefoon.'],
            ['title' => 'Documenten raken kwijt', 'text' => 'Offerte, facturen en afspraken zwerven door de mailbox van je klant. Zet ze op één plek.'],
            ['title' => 'Afspraken maken kost heen-en-weer', 'text' => 'Laat de klant zelf een moment kiezen dat ook in jouw agenda past.'],
        ],
        'zkhw' => [
            'label'     => 'Portaal & afspraken',
            'title'     => 'Zo kan het worden: klanten regelen het zelf',
            'intro'     => 'Klanten plannen zelf hun afspraak in, volgen hun opdracht en vinden alle documenten in een eigen omgeving. Dat scheelt jou telefoontjes en heen-en-weer gemail. Bekijk het voorbeeld van zo\'n klantenportaal.',
            'brand'     => 'Jouw Website',
            'heroTitle' => 'Plan je afspraak en volg je opdracht in je eigen omgeving',
            'urlLabel'  => 'mijn.jouw-bedrijf.nl',
            'navCta'    => 'Inloggen',
            'heroBtn'   => 'Mijn omgeving',
            'ctaLabel'  => 'Bekijk het portaal-voorbeeld',
            'imageSlot' => 'klantenportaal-preview',
            'bullets'   => [
                ['title' => 'Alles overzichtelijk op één plek', 'text' => 'Opdrachten, afspraken, documenten en berichten centraal en altijd beschikbaar.'],
                ['title' => 'Altijd up-to-date', 'text' => 'Je ziet in real-time de status, planning en belangrijke updates.'],
                ['title' => 'Snelle en duidelijke communicatie', 'text' => 'Direct contact met je aanspreekpunt via het portaal, zonder ruis of vertraging.'],
                ['title' => 'Alle documenten binnen handbereik', 'text' => 'Offertes, facturen en afspraken eenvoudig terugvinden.'],
                ['title' => 'Acties en taken helder', 'text' => 'Je weet precies wat er van je verwacht wordt en wat de volgende stap is.'],
                ['title' => 'Meer controle en grip', 'text' => 'Inzicht in kosten, planning en beslissingen, voor een soepel proces.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Administratie automatiseren',
            'title'   => 'Minder papierwerk in je bedrijf',
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
            'label'     => 'Automatisering',
            'title'     => 'Zo kan het worden: papierwerk dat zichzelf doet',
            'intro'     => 'Offertes, planning en facturen kosten je nu uren. Laat de techniek dat overnemen: een aanvraag wordt een offerte, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld. Bekijk hoe de back-office voor je werkt.',
            'brand'     => 'Jouw Website',
            'heroTitle' => 'Minder tijd achter de laptop, meer tijd voor je klanten',
            'urlLabel'  => 'app.jouw-bedrijf.nl',
            'navCta'    => 'Dashboard',
            'heroBtn'   => 'Bekijk demo',
            'ctaLabel'  => 'Bekijk het automatisering-voorbeeld',
            'imageSlot' => 'automatisering-preview',
            'bullets'   => [
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
            'title'   => 'Een assistent voor je bedrijf die nooit een aanvraag mist',
            'sub'     => 'De assistent neemt telefoon en chat aan, vraagt door naar wat de klant nodig heeft en bereidt je offerte voor. Dag en nacht, in gewoon Nederlands.',
            'note'    => 'Praat in jouw eigen toon, jij houdt de controle',
            'usps'    => [
                'Neemt telefoon en chat aan, 24 uur per dag',
                'Nooit meer een aanvraag missen buiten kantooruren',
                'Bereidt een eerste offerte voor',
            ],
        ],
        'pains' => [
            ['title' => 'Je kunt niet altijd opnemen', 'text' => 'Zit je in een gesprek, dan gaat de aanvraag vaak naar wie wél opneemt. De assistent vangt dat op.'],
            ['title' => 'Veel vragen, weinig serieuze klanten', 'text' => 'De assistent vraagt door, zodat je alleen de kansrijke aanvragen terugbelt.'],
            ['title' => 'Reviews vragen vergeet je', 'text' => 'Na de klus vraagt de assistent automatisch om een Google-review.'],
        ],
        'zkhw' => [
            'label'     => 'AI',
            'title'     => 'Zo kan het worden: een assistent die nooit een aanvraag mist',
            'intro'     => 'Bel je een keer niet op tijd terug, dan is de klant vaak al weg. Een slimme assistent neemt telefoon en chat aan, vraagt door en bereidt je offerte voor, dag en nacht. Bekijk hoe dat werkt.',
            'brand'     => 'Jouw Website',
            'heroTitle' => 'Altijd bereikbaar, ook als jij aan het werk bent',
            'urlLabel'  => 'jouw-bedrijf.nl',
            'navCta'    => 'Chat',
            'heroBtn'   => 'Stel je vraag',
            'ctaLabel'  => 'Bekijk het AI-voorbeeld',
            'imageSlot' => 'ai-preview',
            'bullets'   => [
                ['title' => 'Neemt op als jij niet kan', 'text' => 'Telefoon en chat worden 24/7 beantwoord, ook \'s avonds en in het weekend.'],
                ['title' => 'Bereidt je offerte voor', 'text' => 'Uit wat de klant doorgeeft een eerste inschatting van werk en kosten.'],
                ['title' => 'Filtert serieuze aanvragen', 'text' => 'De assistent vraagt door, zodat jij alleen de kansrijke klanten terugbelt.'],
                ['title' => 'Verzamelt reviews', 'text' => 'Vraagt na afloop automatisch om een Google-review.'],
            ],
        ],
    ],
];
