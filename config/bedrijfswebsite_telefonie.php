<?php

/**
 * Inhoud van /ai-telefonie-bedrijfswebsite op jouw-bedrijfswebsite.nl (16-09-2026).
 * Alleen het eigen deel; de rest komt uit config/telefonie_basis.php.
 * Dit kanaal is niet aan één vak gebonden: het richt zich op het mkb in het algemeen.
 * De pagina laat daarom drie herkenbare situaties zien (dienstverlener, winkel, praktijk)
 * in plaats van één vak te veinzen.
 */

return [
    'demo_nummer'    => '088 254 5160',
    'demo_naam'      => 'Autobedrijf De Wissel',
    'demo_noot'      => 'Het demonummer is Autobedrijf De Wissel: een garage die niet bestaat. Bij jou praat de assistent over jouw bedrijf; de manier van werken is hetzelfde.',
    'demo_titel'     => 'Bel de demo op 088 254 5160',
    'demo_lead'      => 'De demo is een verzonnen garage, omdat iedereen weet wat je een garage vraagt. Bel met een van deze kentekens en achternamen, of stel gewoon je eigen vragen. Er wordt niets gerepareerd en niets afgerekend.',
    'demo_kaartjes' => [
        ['kop' => '12-KLM-3 · De Wit',    'hoor' => 'Grote onderhoudsbeurt en APK klaar, € 386,40. Hij zegt dat de auto vandaag tot half zes opgehaald kan worden.'],
        ['kop' => '3-VBH-21 · Bakker',    'hoor' => 'Onderhoudsbeurt morgen om acht uur. Hij bevestigt de tijd en wat er op de planning staat.'],
        ['kop' => 'Een prijsvraag',       'hoor' => 'Vraag wat een APK kost: hij noemt alleen wat het bedrijf heeft opgegeven.'],
        ['kop' => 'Een verzoek',          'hoor' => 'Vraag of je morgen langs kunt komen: hij noteert het als verzoek en zegt dat het bedrijf de tijd bevestigt.'],
    ],
    'demo_probeer' => [
        'Bel buiten openingstijden: hij verbindt niet door maar legt je verzoek vast.',
        'Vraag iets wat hij niet kan weten: hij zegt dat eerlijk en biedt een terugbelverzoek aan.',
        'Onderbreek hem midden in een zin: hij stopt en luistert.',
    ],
    'kantoor_nummer' => '088-2545101',
    'infoblad'       => 'garage',           // config/telefonie_infobladen.php, knop naast het demonummer

    'woorden' => [
        'bedrijf'   => 'je bedrijf',
        'bedrijven' => 'mkb-bedrijven',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'openingstijden, diensten of producten, prijzen of tarieven, wie waarvoor gebeld mag worden en wat de assistent bewust niet mag zeggen',
    ],

    'seo_titel' => 'AI telefonie voor het mkb | AI telefoonassistent die opneemt als jij aan het werk bent',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor het mkb',
        'title'   => 'AI-telefonie voor je bedrijf: de telefoon wordt opgenomen, ook als jij aan het werk bent',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, de vragen beantwoordt die je nu tien keer per dag krijgt, een verzoek of aanvraag compleet vastlegt en doorverbindt als het moet. Voor de dienstverlener op locatie, de winkel met een volle zaak en de praktijk waar de telefoon tijdens een afspraak op stil staat.',
        'alt'     => 'De telefonische assistent van een mkb-bedrijf',
        'usps'    => [
            'Neemt altijd op, ook buiten openingstijden',
            'Antwoordt uit jouw eigen gegevens, niet uit het internet',
            'Weet hij het niet, dan noteert hij naam en nummer en bel jij terug',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Drie bedrijven, dezelfde telefoon',
    'wanneer' => [
        ['t' => 'De dienstverlener op locatie',     'b' => 'Installateur, hovenier, schilder: je bent de hele dag bij klanten en neemt niet op. Wie voicemail krijgt, belt de volgende. De assistent vraagt de klus uit en zet hem klaar.'],
        ['t' => 'De winkel met een volle zaak',     'b' => 'Zaterdagmiddag, rij bij de kassa, en de telefoon. "Hebben jullie maat 42?" en "Tot hoe laat zijn jullie open?" hoeven niet door de kassa beantwoord te worden.'],
        ['t' => 'De praktijk of het kantoor',       'b' => 'Tijdens een afspraak staat de telefoon op stil. De assistent noteert afspraakverzoeken en afzeggingen en legt inhoudelijke vragen bij jou.'],
        ['t' => 'Buiten openingstijden',            'b' => 'Wie \'s avonds belt, heeft meestal iets besloten. Nu een bandje; met de assistent een antwoord en een verzoek dat morgenochtend in je mail staat.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het bedrijf zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Zijn jullie vandaag open?',                                       'antwoord' => 'Ja, vandaag tot zes uur. Morgen, zondag, zijn we gesloten.'],
        ['vraag' => 'Wat kost het als jullie langskomen?',                              'antwoord' => 'We rekenen € 45 voorrijkosten en € 65 per uur, exclusief btw. Een vaste prijs voor een grotere klus krijgt u na een bezoek. Wilt u dat ik een afspraakverzoek noteer?'],
        ['vraag' => 'Ik wil graag een afspraak maken.',                                 'antwoord' => 'Graag. Ik noteer uw naam, nummer en op welke dagdelen u kunt; u wordt vandaag nog teruggebeld met een tijd.'],
        ['vraag' => 'Hebben jullie dit product op voorraad?',                            'antwoord' => 'Daar kan ik aan de telefoon niet in kijken; ik wil u geen verkeerd antwoord geven. Ik noteer uw vraag en de winkel belt u terug. Mag ik uw nummer?'],
        ['vraag' => 'Kan ik iemand van de administratie spreken?',                      'antwoord' => 'Ik verbind u door. Neemt niemand op, dan noteer ik uw naam en nummer en waar het over gaat, en belt de administratie u terug.'],
        ['vraag' => 'Ik heb een klacht over de levering van gisteren.',                  'antwoord' => 'Vervelend, dat pakken we op. Ik noteer uw naam, ordernummer en wat er mis is; u wordt vandaag nog teruggebeld. Klopt het nummer 06-1234 5678?'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'De veelgestelde vragen',           'b' => 'Openingstijden, adres, wat je doet, wat het kost, hoe het werkt. Uit jouw kennisbank, in jouw woorden.'],
        ['t' => 'Verzoeken en aanvragen',            'b' => 'Afspraak, offerte, bestelling, klacht: hij vraagt uit wat jij belangrijk vindt, leest het terug en zet het klaar. Jij bevestigt.'],
        ['t' => 'Naar de juiste persoon',            'b' => 'Wie waarvoor gebeld mag worden, en wanneer. Hij verbindt door of legt een terugbelverzoek bij de juiste collega.'],
        ['t' => 'Wat hij niet mag zeggen',           'b' => 'Per bedrijf leggen we vast waar hij stopt: medisch, juridisch, technisch, prijzen op maat. Daar verwijst hij door in plaats van te gokken.'],
        ['t' => 'Dagberichten',                      'b' => 'Vandaag eerder dicht, storing, vakantie: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Vakinhoudelijk advies geven. Wat "vakinhoudelijk" is leggen we per bedrijf vast, en daar stopt hij.',
        'Een prijs op maat, een datum of een levering toezeggen. Hij legt het verzoek vast; jij bevestigt.',
        'In systemen kijken die niet gekoppeld zijn. Dan zegt hij eerlijk dat hij het niet weet.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 12,
            'dagen'           => 5,
            'minuten'         => 2,
            'uurloon'         => 30,
            'oppakken'        => 4,
            'gemist_per_week' => 8,
            'bestelling_pct'  => 25,
            'bestelwaarde'    => 150,
            'afhandel_pct'    => 65,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bedrijf en zie wat de telefoon je nu per maand kost aan tijd en onderbrekingen, en welke omzet er in gemiste telefoontjes zit.',
            'deel_label'   => 'Deel daarvan dat een opdracht of aankoop was',
            'waarde_label' => 'Gemiddelde waarde van zo\'n opdracht of aankoop',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of erdoor onderbroken',
            'tijd_tegel'   => 'aan loonkosten die vrijkomen voor het werk zelf',
        ],
    ],

    'faq_titel' => 'Wat ondernemers ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn bedrijf?',       'a' => 'Ja, voor vrijwel elk mkb-bedrijf. Hij neemt op met de naam van je bedrijf, beantwoordt de vragen die jij aanlevert, legt verzoeken vast en verbindt door. Wat hij per vak wel en niet mag zeggen, leggen we samen vast.'],
        ['q' => 'Is hij geschikt voor mijn branche?',                              'a' => 'We hebben inrichtingen voor onder meer bakkerijen, garages, loodgieters, aannemers, praktijken, rijscholen en kantoren. Staat jouw vak er niet bij, dan richten we hem in vanuit dezelfde basis: jouw gegevens, jouw grenzen.'],
        ['q' => 'Kan hij in mijn systemen kijken?',                                'a' => 'Als er een koppeling is, ja: orderstatus, afspraken, voorraad. Zonder koppeling zegt hij eerlijk dat hij het niet weet en zorgt hij dat iemand terugbelt. We beloven geen koppeling voordat we weten dat hij kan.'],
        ['q' => 'Hoe voorkom je dat hij onzin zegt?',                              'a' => 'Hij antwoordt alleen uit jouw kennisbank en zegt "dat weet ik niet" als het er niet in staat. Per bedrijf leggen we vast waar hij stopt en doorverwijst. Je leest elk gesprek terug in je portaal.'],
        ['q' => 'Kan ik hem ook alleen buiten openingstijden gebruiken?',           'a' => 'Ja. Je schakelt je nummer alleen buiten openingstijden of bij geen gehoor door; overdag neem je gewoon zelf op.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Bedrijfswebsite',               'b' => 'De vragen die de assistent het vaakst hoort, horen ook op je site te staan. Dan hoeft er minder gebeld te worden.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een verzoek van de assistent komt op dezelfde lijst als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal',                 'b' => 'Vaste klanten die nu bellen om iets op te vragen, regelen het zelf.'],
    ],

    'cta_titel' => 'Hoor het zelf, of vraag een demo aan',
    'cta_tekst' => 'Bel het demonummer en stel de vragen die jouw klanten stellen. Of plan een gesprek van een half uur; dan richten we een proefversie in met jouw gegevens.',
];
