<?php

/**
 * Inhoud van /ai-telefonie-badkamerspecialist op jouw-badkamerspecialist-website.nl
 * (16-09-2026). Alleen het vak-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een badkamerbedrijf heeft een showroom én montageploegen: de telefoon is half
 * showroomvragen (open? afspraak? merken?) en half "wanneer komt de tegelzetter".
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je badkamerbedrijf',
        'bedrijven' => 'badkamerspecialisten',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'openingstijden van de showroom, hoe een adviesgesprek loopt, merken, vanaf-prijzen, werkgebied en de lopende projecten die hij mag noemen',
    ],

    'seo_titel' => 'AI telefonie voor badkamerspecialisten | AI telefoonassistent voor showroom en montage',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor badkamerspecialisten',
        'title'   => 'AI-telefonie voor je badkamerbedrijf: de telefoon wordt opgenomen, in de showroom én op de montage',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, showroomvragen beantwoordt, een verzoek voor een adviesgesprek vastlegt, de klant met een lopende verbouwing vertelt wat jij hebt vastgelegd en de tegelzetter doorverbindt met de projectleider. Tijdens een adviesgesprek, op de montage en op zaterdag.',
        'alt'     => 'De telefonische assistent van een badkamerspecialist',
        'usps'    => [
            'Neemt altijd op, ook midden in een adviesgesprek',
            'Showroomvragen en afspraakverzoeken zonder onderbreking',
            '"Wanneer komt de tegelzetter?" uit jouw eigen planning',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Midden in een adviesgesprek',   'b' => 'Je zit met een stel aan de tekentafel voor een badkamer van vijftienduizend euro en de telefoon gaat. Opnemen breekt het gesprek; niet opnemen kost misschien de volgende.'],
        ['t' => 'Zaterdag in de showroom',        'b' => 'Drie stellen lopen rond, één medewerker, en de telefoon. "Zijn jullie open?" en "Hebben jullie Grohe?" hoeven niet door die ene medewerker beantwoord te worden.'],
        ['t' => '"Wanneer komt de tegelzetter?"', 'b' => 'Een klant met een verbouwing van drie weken belt gemiddeld zes keer. Het antwoord staat in de planning; de assistent zegt het.'],
        ['t' => 'Montageploeg op de lijn',        'b' => 'De installateur op de klus belt over een afvoer die niet klopt. Dat moet naar de projectleider, meteen, niet naar de voicemail van de showroom.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het badkamerbedrijf zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Zijn jullie zaterdag open, en moet ik een afspraak maken?',      'antwoord' => 'Zaterdag zijn we open van tien tot vier. Rondkijken kan zonder afspraak; voor een adviesgesprek met ontwerp plannen we anderhalf uur en is een afspraak handig. Wilt u dat ik een verzoek noteert?'],
        ['vraag' => 'Wat kost een complete badkamer bij jullie?',                    'antwoord' => 'Een complete badkamer inclusief montage begint bij ons rond € 9.500; de meeste klanten zitten tussen € 12.000 en € 20.000. Een echte prijs komt na een adviesgesprek en een opname thuis. Zal ik een adviesgesprek voor u aanvragen?'],
        ['vraag' => 'Wanneer komt de tegelzetter bij ons?',                          'antwoord' => 'Op naam van Visser aan de Beukenlaan zie ik: tegelzetter van maandag tot en met woensdag volgende week, daarna de installateur. Verandert er iets, dan belt de projectleider u.'],
        ['vraag' => 'Ik ben de installateur op de Beukenlaan, de afvoer zit verkeerd.', 'antwoord' => 'Ik verbind u door met de projectleider. Neemt hij niet op, dan stuur ik het bericht direct door en belt hij u terug.'],
        ['vraag' => 'Hebben jullie ook inloopdouches zonder drempel?',               'antwoord' => 'Ja, drempelloze inloopdouches zijn een van onze specialiteiten, ook voor mensen die slecht ter been zijn. In de showroom staan er vier opgesteld. Wilt u langskomen?'],
        ['vraag' => 'Ik heb een lekkage bij de badkamer die jullie vorig jaar hebben gedaan.', 'antwoord' => 'Vervelend, dat pakken we op. Ik noteer uw naam, adres en waar het lekt, en de service belt u vandaag nog terug. Klopt het nummer 06-1234 5678?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Showroomvragen',                   'b' => 'Openingstijden, met of zonder afspraak, welke merken, wat er staat opgesteld. Uit jouw gegevens.'],
        ['t' => 'Adviesgesprek aanvragen',           'b' => 'Naam, adres, wat de klant wil, wanneer het uitkomt. Jij plant het in en bevestigt.'],
        ['t' => 'Vanaf-prijzen',                     'b' => 'Wat een badkamer, een toilet of een renovatie bij jou ongeveer kost, met de opmerking dat de echte prijs na een opname komt.'],
        ['t' => 'Klanten met een lopende verbouwing', 'b' => 'Welke week welke vakman, wat jij per project hebt vastgelegd. Alleen tegen de klant zelf.'],
        ['t' => 'Service en garantie',               'b' => 'Een lekkage of losse tegel na oplevering legt hij vast als servicemelding, met adres en wat er mis is.'],
        ['t' => 'Dagberichten',                      'b' => 'Showroom dicht wegens beurs, tegelzetter ziek, levering vertraagd: één bericht van jou en de assistent zegt het tegen wie ernaar vraagt.'],
    ],

    'niet' => [
        'Een prijs of ontwerp toezeggen. Hij noemt vanaf-prijzen die jij opgeeft; een offerte komt na een opname.',
        'Een montagedatum beloven of verschuiven. Hij zegt wat in de planning staat en legt een wijzigingsverzoek vast.',
        'Technisch advies geven over een lekkage of aansluiting. Dat gaat naar de service of de projectleider.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 15,
            'dagen'           => 6,
            'minuten'         => 2.5,
            'uurloon'         => 40,
            'oppakken'        => 5,
            'gemist_per_week' => 4,
            'bestelling_pct'  => 5,
            'bestelwaarde'    => 3000,
            'afhandel_pct'    => 65,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bedrijf en zie wat de telefoon je nu per maand kost aan onderbroken adviesgesprekken, en welke marge er in gemiste telefoontjes zit. Eén gemiste badkamer betaalt de assistent voor jaren.',
            'deel_label'   => 'Deel daarvan dat een badkamer was geworden',
            'waarde_label' => 'Gemiddelde marge op zo\'n opdracht',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of uit een adviesgesprek gehaald',
            'tijd_tegel'   => 'aan uren die weer naar advies en montage gaan',
            'omzet_tegel'  => 'marge per maand in aanvragen die nu niemand opneemt',
        ],
    ],

    'faq_titel' => 'Wat badkamerspecialisten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn badkamerbedrijf?', 'a' => 'Ja. Hij neemt op met de naam van je bedrijf, beantwoordt showroomvragen, legt verzoeken voor een adviesgesprek vast en verbindt de montage door met de projectleider.'],
        ['q' => 'Kan hij klanten vertellen wanneer de tegelzetter komt?',           'a' => 'Alleen wat jij per project hebt vastgelegd, en alleen tegen de klant zelf. Verandert de planning, dan pas je het aan; de assistent zegt nooit een datum die er niet staat.'],
        ['q' => 'Noemt hij prijzen?',                                             'a' => 'Vanaf-prijzen en bandbreedtes die jij opgeeft, altijd met de opmerking dat de echte prijs na een adviesgesprek en opname komt.'],
        ['q' => 'Kan hij adviesgesprekken inplannen?',                             'a' => 'Hij legt een verzoek vast met naam, adres, wens en voorkeursmoment. Jij plant het in je showroomagenda en bevestigt. Zo blijven dubbele boekingen uit.'],
        ['q' => 'Wat doet hij met een servicemelding na oplevering?',              'a' => 'Adres, wat er mis is en wanneer het is opgeleverd vastleggen, en de service laten terugbellen. Bij een lekkage verbindt hij tijdens openingstijden direct door.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor badkamerspecialisten', 'b' => 'Showroom, projecten en een afspraakknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken',  'b' => 'Een adviesgesprek of servicemelding van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor lopende projecten', 'b' => 'Planning, tekeningen en meerwerk op één plek, zodat de klant minder hoeft te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw showroom',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw openingstijden, merken en vanaf-prijzen en bel je zelf alsof je een nieuwe badkamer wilt.',
];
