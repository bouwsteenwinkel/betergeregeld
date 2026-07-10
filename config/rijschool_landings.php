<?php

/**
 * RIJSCHOOL — facet-content voor de bespoke _sales/_landing-pagina's.
 * Zelfde structuur als config/badkamer_landings.php, met teksten en diensten
 * die op een autorijschool slaan.
 *
 * Elke facet heeft een 'imageSlot' ({facet}-preview) → de "zo-kan-het-worden"-
 * sectie toont het echte preview-beeld uit channel-media/rijschool/, met de
 * CSS-mockup als terugval zolang een beeld ontbreekt.
 */

return [

    'website' => [
        'hero' => [
            'eyebrow' => 'Website laten maken',
            'title'   => 'Een website voor je rijschool die leerlingen oplevert',
            'sub'     => 'Word gevonden als iemand rijlessen zoekt in jouw regio, en laat met je slagingspercentage en reviews zien waarom leerlingen voor jóu kiezen.',
            'note'    => 'Gratis en vrijblijvend een voorbeeld van jóuw site, vaak binnen 1 à 2 dagen',
            'usps'    => [
                'Gevonden in Google in je eigen regio',
                'Je slagingspercentage en reviews in beeld',
                'Aanmeldingen rechtstreeks in je mailbox',
            ],
        ],
        'pains' => [
            ['title' => 'Je site is verouderd of je hebt er geen', 'text' => 'Alleen een Facebook-pagina of een site van jaren terug? Dan kiest een leerling sneller voor een rijschool die er strak en betrouwbaar uitziet.'],
            ['title' => 'Je bent niet vindbaar in Google', 'text' => 'Wie een rijschool zoekt in jouw plaats moet jóu vinden, niet de concurrent een dorp verderop.'],
            ['title' => 'Aanmeldingen blijven uit', 'text' => 'Zonder een duidelijke aanmeldknop haakt een geïnteresseerde af. Elke gemiste aanmelding is een leerling die je maanden had kunnen lesgeven.'],
        ],
        'zkhw' => [
            'imageSlot'    => 'website-preview',
            'label'        => 'Website',
            'title'        => 'Zo kan het worden: je rijschool professioneel online',
            'intro'        => 'Een leerling kiest een rijschool die betrouwbaar oogt en makkelijk te bereiken is. Je site laat je slagingspercentage, je lesauto\'s en echte reviews zien, met een duidelijke aanmeldknop. We hebben een compleet voorbeeld klaargezet. Klik erdoorheen en stel je voor dat het je eigen rijschool is.',
            'brand'        => 'Rijschool Vooruit',
            'heroTitle'    => 'Haal in één keer je rijbewijs bij een rijschool die je vertrouwt',
            'urlLabel'     => 'jouw-rijschool.nl',
            'ctaLabel'     => 'Bekijk het volledige voorbeeld',
            'galleryLabel' => 'Onze lesauto\'s en geslaagden',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Meer aanmeldingen en leerlingen', 'text' => 'Een professionele website die vertrouwen wekt en bezoekers omzet in leerlingen.'],
                ['title' => 'Vertrouwen door resultaat', 'text' => 'Je slagingspercentage, reviews en instructeurs in beeld. Daar meldt een leerling zich op aan.'],
                ['title' => 'Beter gevonden in Google', 'text' => 'Hogere posities zorgen voor meer zichtbaarheid en leerlingen uit je eigen regio.'],
                ['title' => 'Dag en nacht aanmelden', 'text' => 'Een duidelijke knop die ook \'s avonds nieuwe leerlingen oplevert, op mobiel en desktop.'],
            ],
        ],
    ],

    'webshop' => [
        'hero' => [
            'eyebrow' => 'Online verkoop laten maken',
            'title'   => 'Verkoop lespakketten en cursussen online, 24/7',
            'sub'     => 'Lespakketten, losse lessen, theoriecursussen en cadeaubonnen. Leerlingen rekenen zelf af met iDEAL, jij hoeft er niks voor te doen.',
            'note'    => 'Gekoppeld aan je site en planning, dus geen dubbele administratie',
            'usps'    => [
                'Lespakketten en cadeaubonnen dag en nacht verkocht',
                'Direct betaald met iDEAL, geen gedoe met facturen',
                'Automatisch gekoppeld aan je leerlingadministratie',
            ],
        ],
        'pains' => [
            ['title' => 'Aanmelden kan alleen overdag', 'text' => 'Buiten kantooruren loop je leerlingen mis die juist \'s avonds oriënteren en willen boeken.'],
            ['title' => 'Losse verkoop kost veel tijd', 'text' => 'Appjes, prijsvragen en pinnen bij de eerste les. Online rekent de leerling zelf af.'],
            ['title' => 'Cadeaubonnen loop je mis', 'text' => 'Een rijbewijs is een populair cadeau. Met een webshop verkoop je bonnen die je nu misloopt.'],
        ],
        'zkhw' => [
            'imageSlot'    => 'webshop-preview',
            'label'        => 'Webshop',
            'title'        => 'Zo kan het worden: je lespakketten 24/7 te koop',
            'intro'        => 'Naast lesgeven ook online verkopen? Leerlingen kiezen zelf een lespakket, theoriecursus of cadeaubon, betalen met iDEAL en staan meteen in je administratie. Bekijk hoe jouw aanbod online staat.',
            'brand'        => 'Rijschool Vooruit',
            'heroTitle'    => 'Boek je lespakket of theoriecursus online, direct geregeld',
            'urlLabel'     => 'shop.jouw-rijschool.nl',
            'navCta'       => 'Winkelmand',
            'heroBtn'      => 'In winkelmand',
            'ctaLabel'     => 'Bekijk het webshop-voorbeeld',
            'galleryLabel' => 'Populair aanbod',
            'gallery'      => [['slot' => 'gallery1', 'price' => '€ 1.150'], ['slot' => 'gallery2', 'price' => '€ 45'], ['slot' => 'gallery3', 'price' => '€ 25']],
            'bullets'      => [
                ['title' => 'Verkoop dag en nacht door', 'text' => 'Leerlingen boeken ook \'s avonds en in het weekend, zonder dat jij er iets voor hoeft te doen.'],
                ['title' => 'Veilig betalen met iDEAL', 'text' => 'Direct afgerekend, geen openstaande facturen of gedoe met pinnen.'],
                ['title' => 'Cadeaubonnen erbij', 'text' => 'Verkoop rijles-cadeaubonnen die je anders zou mislopen.'],
                ['title' => 'Alles gekoppeld', 'text' => 'Elke aankoop staat meteen in je leerlingadministratie en planning.'],
            ],
        ],
    ],

    'klantenportaal' => [
        'hero' => [
            'eyebrow' => 'Leerlingportaal laten maken',
            'title'   => 'Laat leerlingen hun lessen en voortgang zelf volgen',
            'sub'     => 'Leerlingen plannen zelf hun lessen, zien hun voortgang per onderdeel en vinden hun facturen en examendatum op één plek. Jij belt en appt minder.',
            'note'    => 'Eigen inlog voor elke leerling, gekoppeld aan hun lesdossier',
            'usps'    => [
                'Lessen zelf inplannen, ook buiten kantooruren',
                'Altijd inzicht in voortgang en examendatum',
                'Minder appjes en heen-en-weer gebel',
            ],
        ],
        'pains' => [
            ['title' => 'Veel geappt over de planning', 'text' => 'Leerlingen willen weten wanneer hun volgende les is en hoe ver ze zijn. Dat kost je elke week uren.'],
            ['title' => 'Voortgang bijhouden op papier', 'text' => 'Leskaarten en aftekenlijsten raken kwijt. Zet de voortgang overzichtelijk online.'],
            ['title' => 'Lessen plannen kost heen-en-weer', 'text' => 'Laat de leerling zelf een moment kiezen dat ook in jouw agenda past.'],
        ],
        'zkhw' => [
            'imageSlot'    => 'klantenportaal-preview',
            'label'        => 'Leerlingportaal',
            'title'        => 'Zo kan het worden: leerlingen regelen het zelf',
            'intro'        => 'Leerlingen plannen zelf hun lessen, volgen hun voortgang per onderdeel en vinden facturen en hun examendatum in een eigen omgeving. Dat scheelt jou telefoontjes en geapp. Bekijk het voorbeeld van zo\'n leerlingportaal.',
            'brand'        => 'Rijschool Vooruit',
            'heroTitle'    => 'Plan je lessen en volg je voortgang in je eigen omgeving',
            'urlLabel'     => 'mijn.jouw-rijschool.nl',
            'navCta'       => 'Inloggen',
            'heroBtn'      => 'Mijn omgeving',
            'ctaLabel'     => 'Bekijk het portaal-voorbeeld',
            'galleryLabel' => 'Jouw lessen in beeld',
            'gallery'      => [['slot' => 'gallery1'], ['slot' => 'gallery2'], ['slot' => 'gallery3']],
            'bullets'      => [
                ['title' => 'Alles overzichtelijk op één plek', 'text' => 'Lessen, voortgang, facturen en berichten centraal en altijd beschikbaar.'],
                ['title' => 'Lessen zelf inplannen', 'text' => 'Leerlingen kiezen zelf een moment voor hun volgende les, ook \'s avonds.'],
                ['title' => 'Voortgang inzichtelijk', 'text' => 'Per onderdeel zien leerlingen hoe ver ze zijn en wat er nog komt.'],
                ['title' => 'Minder telefoontjes', 'text' => 'Leerlingen vinden hun antwoord zelf, jij houdt tijd over voor de les.'],
            ],
        ],
    ],

    'automatisering' => [
        'hero' => [
            'eyebrow' => 'Administratie automatiseren',
            'title'   => 'Minder papierwerk in je rijschool',
            'sub'     => 'Facturen, herinneringen en lesplanning die zichzelf doen. Een aanmelding wordt een leerlingdossier, facturen gaan vanzelf, en alles staat gekoppeld.',
            'note'    => 'Gekoppeld aan je site, planning en boekhouding',
            'usps'    => [
                'Facturen en herinneringen gaan automatisch',
                'Lesplanning zonder dubbel werk',
                'Website, planning en boekhouding werken samen',
            ],
        ],
        'pains' => [
            ['title' => 'Facturen maken kost je avonden', 'text' => 'Elke maand opnieuw facturen typen en versturen. Dat kan automatisch, op het juiste moment.'],
            ['title' => 'Je zit achter je geld aan', 'text' => 'Vergeten herinneringen kosten geld. Laat ze automatisch op tijd de deur uit gaan.'],
            ['title' => 'Dubbel werk tussen je systemen', 'text' => 'Aanmelding, planning en boekhouding apart bijhouden? Die kunnen met elkaar praten.'],
        ],
        'zkhw' => [
            'imageSlot'    => 'automatisering-preview',
            'label'        => 'Automatisering',
            'title'        => 'Zo kan het worden: papierwerk dat zichzelf doet',
            'intro'        => 'Facturen, herinneringen en planning kosten je nu uren. Laat de techniek dat overnemen: een aanmelding wordt een leerlingdossier, facturen en herinneringen gaan vanzelf, en alles staat gekoppeld. Bekijk hoe de back-office voor je werkt.',
            'brand'        => 'Rijschool Vooruit',
            'heroTitle'    => 'Minder tijd achter de laptop, meer tijd achter het stuur',
            'urlLabel'     => 'app.jouw-rijschool.nl',
            'navCta'       => 'Dashboard',
            'heroBtn'      => 'Bekijk demo',
            'ctaLabel'     => 'Bekijk het automatisering-voorbeeld',
            'galleryLabel' => 'Loopt automatisch',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Factuur ✓'], ['slot' => 'gallery2', 'price' => 'Herinnering ✓'], ['slot' => 'gallery3', 'price' => 'Planning ✓']],
            'bullets'      => [
                ['title' => 'Minder handmatig werk', 'text' => 'Automatiseer terugkerende taken en bespaar tot wel 70% tijd.'],
                ['title' => 'Facturen en herinneringen vanzelf', 'text' => 'Op het juiste moment verstuurd, jij hoeft niet achter je geld aan.'],
                ['title' => 'Planning zonder gedoe', 'text' => 'Lessen, examens en beschikbaarheid in één overzicht, geen dubbele boekingen.'],
                ['title' => 'Alles gekoppeld', 'text' => 'Website, planning en boekhouding werken met elkaar mee, geen dubbel werk.'],
            ],
        ],
    ],

    'ai' => [
        'hero' => [
            'eyebrow' => 'AI-assistent',
            'title'   => 'Een assistent voor je rijschool die nooit een aanmelding mist',
            'sub'     => 'De assistent neemt telefoon en appjes aan, beantwoordt vragen over prijzen en beschikbaarheid en plant proeflessen in. Dag en nacht, in gewoon Nederlands.',
            'note'    => 'Praat in jouw eigen toon, jij houdt de controle',
            'usps'    => [
                'Neemt telefoon en chat aan, 24 uur per dag',
                'Nooit meer een aanmelding missen tijdens de les',
                'Beantwoordt vragen over prijzen en beschikbaarheid',
            ],
        ],
        'pains' => [
            ['title' => 'Je kunt niet opnemen tijdens de les', 'text' => 'Zit je in de auto, dan gaat de leerling vaak naar wie wél opneemt. De assistent vangt dat op.'],
            ['title' => 'Steeds dezelfde vragen', 'text' => 'Prijzen, beschikbaarheid, pakketten. De assistent beantwoordt ze meteen, zodat jij alleen de serieuze aanmeldingen terugbelt.'],
            ['title' => 'Reviews vragen vergeet je', 'text' => 'Na het slagen vraagt de assistent automatisch om een Google-review.'],
        ],
        'zkhw' => [
            'imageSlot'    => 'ai-preview',
            'label'        => 'AI',
            'title'        => 'Zo kan het worden: een assistent die nooit een aanmelding mist',
            'intro'        => 'Zit je in de auto, dan kun je niet opnemen, en is de leerling vaak al weg. Een slimme assistent neemt telefoon en chat aan, beantwoordt vragen en plant een proefles in, dag en nacht. Bekijk hoe dat werkt bij een rijschool.',
            'brand'        => 'Rijschool Vooruit',
            'heroTitle'    => 'Altijd bereikbaar, ook als jij lesgeeft',
            'urlLabel'     => 'jouw-rijschool.nl',
            'navCta'       => 'Chat',
            'heroBtn'      => 'Stel je vraag',
            'ctaLabel'     => 'Bekijk het AI-voorbeeld',
            'galleryLabel' => 'De assistent aan het werk',
            'gallery'      => [['slot' => 'gallery1', 'price' => 'Chat ✓'], ['slot' => 'gallery2', 'price' => 'Proefles ✓'], ['slot' => 'gallery3', 'price' => 'Antwoord ✓']],
            'bullets'      => [
                ['title' => 'Neemt op als jij lesgeeft', 'text' => 'Telefoon en chat worden 24/7 beantwoord, ook \'s avonds en in het weekend.'],
                ['title' => 'Plant proeflessen in', 'text' => 'De assistent stelt een moment voor dat in jouw agenda past.'],
                ['title' => 'Filtert serieuze aanmeldingen', 'text' => 'De assistent vraagt door, zodat jij alleen de kansrijke leerlingen terugbelt.'],
                ['title' => 'Verzamelt reviews', 'text' => 'Vraagt na het slagen automatisch om een Google-review.'],
            ],
        ],
    ],

];
