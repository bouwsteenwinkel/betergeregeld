<?php

/**
 * Infobladen (PDF) bij de openbare AI-telefoniedemo's (16-09-2026).
 *
 * Eén blad per demo, te downloaden op /ai-telefonie-{key}/infoblad.pdf van elk kanaal dat
 * in zijn telefonieconfig 'infoblad' => '<sleutel hieronder>' heeft staan. Gerenderd door
 * App\Http\Controllers\ChannelSite\TelefonieInfobladController met dompdf uit
 * resources/views/pdf/telefonie-infoblad.blade.php, in de huisstijl van Betergeregeld.
 *
 * Voor wie: de ondernemer die de demo belt. Dus: wat je kunt vragen, welke verzonnen
 * gegevens je mag gebruiken, en wat de assistent bewust niet doet. Geen interne zaken
 * (portaal-inloggen, beheerschermen) -- die staan in de testbladen voor ons eigen team.
 *
 * De feiten komen uit bouwsteenwinkel_v3/telefonie/beleid/<map>/ en de _aitest-scripts;
 * verandert daar iets, dan hoort dit mee te veranderen.
 */

return [
    'garage' => [
        'titel'      => 'Zo probeer je de AI-telefoniste van Autobedrijf De Wissel',
        'bestand'    => 'Infoblad-AI-telefonie-garagedemo.pdf',
        'nummer'     => '088 254 5160',
        'bedrijf'    => 'Autobedrijf De Wissel',
        'intro'      => 'Autobedrijf De Wissel bestaat niet. Het is een verzonnen garage aan de Zonnestraat 40 in Hilversum, met een werkplaats vol verzonnen auto\'s, zodat je vrij kunt vragen wat je wilt. Er wordt niets gerepareerd en niets afgerekend. De assistent neemt op met de naam van de garage en gedraagt zich precies zoals hij dat bij jouw bedrijf zou doen: hij weet alleen wat de garage hem heeft verteld.',
        'feiten'     => [
            ['Openingstijden', 'Maandag t/m vrijdag van acht uur tot half zes. Ophalen tot half zes. Buiten die tijden verbindt hij niet door, maar legt hij je verzoek vast.'],
            ['Identificatie',  'Voor alles wat over een auto gaat vraagt hij kenteken én achternaam. Kentekens leest hij teken voor teken terug.'],
            ['Prijzen',        'APK € 49,50 inclusief afmelden · banden wisselen € 35 · bandenopslag € 50 per seizoen · leenauto € 10 per dag (brandstof zelf, alleen als de auto minstens een dag blijft).'],
            ['Wachten',        'Op een APK of bandenwissel kun je wachten, ongeveer een uur. Station Hilversum is tien minuten lopen.'],
        ],
        'data_titel' => 'De auto\'s die bestaan',
        'data_kop'   => ['Kenteken', 'Achternaam', 'Auto', 'Wat er staat'],
        'data'       => [
            ['12-KLM-3', 'De Wit',    'Volkswagen Golf, grijs, 2019',  'Grote onderhoudsbeurt en APK klaar, € 386,40; vandaag tot half zes ophalen'],
            ['7-XRP-88', 'Jansen',    'Toyota Yaris, rood, 2016',      'Start slecht; wacht op een startmotor'],
            ['KP-482-T', 'El Amrani', 'Renault Clio, blauw, 2014',     'Niets in de werkplaats; APK verloopt over twaalf dagen'],
            ['3-VBH-21', 'Bakker',    'Skoda Octavia, wit, 2021',      'Onderhoudsbeurt gepland, morgen om acht uur'],
        ],
        'probeer'    => [
            ['"Is mijn auto al klaar? Kenteken 12-KLM-3."',              'Hij vraagt om je achternaam. Met "De Wit": klaar, € 386,40, vandaag tot half zes ophalen.'],
            ['Kenteken 12-KLM-3 met achternaam "Jansen"',                 'Past niet bij elkaar: hij geeft niets prijs en gaat niet raden.'],
            ['"Wanneer moet mijn Clio APK?" (KP-482-T, El Amrani)',        'Over twaalf dagen, en hij biedt aan een afspraakverzoek te noteren.'],
            ['"Kan ik morgen komen voor een piepend geluid bij het remmen?"', 'Hij noteert kenteken, klacht en voorkeur en zegt dat de werkplaats de tijd bevestigt. Hij plant niet zelf.'],
            ['"Mijn motorlampje brandt, kan ik doorrijden?"',              'Geen technisch oordeel: hij verbindt door of noteert een terugbelverzoek.'],
            ['"Wat kost een APK, en kan ik erop wachten?"',                '€ 49,50 inclusief afmelden; wachten kan, ongeveer een uur. Hiervoor hoeft hij geen kenteken te weten.'],
            ['"Is er donderdag een leenauto vrij?"',                        'Dat weet hij niet; hij noteert het als wens bij de afspraak.'],
            ['Bel na half zes',                                            'Hij verbindt niet door, legt je verzoek vast en noemt de openingstijden als "van acht tot half zes".'],
        ],
        'niet'       => [
            'Een werkplaatsafspraak inplannen zonder dat de garage hem bevestigt.',
            'Technisch advies geven over een klacht.',
            'Iets over een auto zeggen zonder kenteken én achternaam.',
            'Betalingen aannemen, gesprekken opnemen of iets verzinnen.',
        ],
        'tips'       => [
            'Praat gewoon, zoals je tegen een garage praat. Geen trefwoorden.',
            'Onderbreek hem gerust midden in een zin: hij stopt en luistert.',
            'Zeg een keer alleen "bedankt": hij rondt dan kort af.',
            'Vraag iets wat hij niet kan weten: het goede antwoord is "dat weet ik niet", met een terugbelverzoek.',
        ],
        'slot'       => 'Zo zou hij ook bij jou opnemen, met jouw openingstijden, jouw prijzen en jouw werkplaatsplanning. Plan een gesprek van een half uur via de website; dan richten we een proefversie in met jouw gegevens.',
    ],

    'bakkerij' => [
        'titel'      => 'Zo probeer je de AI-telefoniste van Bakkerij Kruimel',
        'bestand'    => 'Infoblad-AI-telefonie-bakkerijdemo.pdf',
        'nummer'     => '088 254 5170',
        'bedrijf'    => 'Bakkerij Kruimel',
        'intro'      => 'Bakkerij Kruimel bestaat niet. Het is een verzonnen ambachtelijke bakkerij aan de Kerkbrink 12 in Hilversum: brood, banket, gebak, taarten en belegde broodjes voor kantoren. Het assortiment bestaat wél, en de assistent neemt echt bestellingen aan: hij zoekt het product op, let op hoe ver vooruit het besteld moet worden, leest de bestelling terug en geeft een bestelnummer. Er wordt niets gebakken en niets afgerekend.',
        'feiten'     => [
            ['Openingstijden', 'Maandag t/m zaterdag van acht tot vijf. Zondag dicht: wie iets "voor zondag" wil, haalt het zaterdag.'],
            ['Bestellen',      'Brood en gebak: vandaag. Appeltaart en belegde broodjes: een dag vooruit. Verjaardags- en chocoladetaart: twee dagen vooruit; een foto op de taart twee dagen extra. Bruidstaart: op aanvraag, na een proefsessie.'],
            ['Betalen',        'Bij afhalen. Zakelijk op rekening in overleg. Geen betaling aan de telefoon.'],
            ['Bezorgen',       'Alleen in Hilversum, gratis vanaf twintig broodjes of vijftig euro.'],
        ],
        'data_titel' => 'De bestellingen die bestaan',
        'data_kop'   => ['Bestelnummer', 'Achternaam', 'Wat', 'Wanneer en status'],
        'data'       => [
            ['K-1042', 'De Wit',    '2 appeltaarten, € 33, al betaald',                          'Vandaag 10:00 afhalen; klaar'],
            ['K-1057', 'Jansen',    'Slagroomtaart voor 10 met "Sam 7", € 24,95, betaald',        'Over twee dagen 11:30 afhalen; bevestigd'],
            ['K-1063', 'El Amrani', '40 belegde broodjes voor Notariskantoor Van Dijk, € 158, op rekening', 'Morgen vóór 12:00 bezorgen; bevestigd'],
            ['K-1071', 'Bakker',    'Chocoladetaart voor 10; vraag over een foto op de taart staat open', 'Over vijf dagen; open'],
        ],
        'probeer'    => [
            ['"Is mijn bestelling klaar? K-1042, De Wit."',                 'Klaar: twee appeltaarten, vandaag, al betaald. Hij leest het nummer als "K, een nul vier twee".'],
            ['"Ik wil een slagroomtaart voor zaterdag, voor tien man, met Lieve Oma erop."', 'Hij rekent met de besteltermijn, vraagt naam en nummer, leest de bestelling terug en geeft een nieuw bestelnummer (K-2…). De bakkerij bevestigt; betalen bij afhalen.'],
            ['"Kan die taart morgen al?"',                                  'Eerlijk: op zijn vroegst overmorgen. Geen uitzondering beloven.'],
            ['"Ik wil brood voor zondag."',                                 'Zondag dicht; zaterdag ophalen.'],
            ['"Zitten er noten in de amandelstaaf?"',                       'Geen allergie-uitspraak: een medewerker belt terug, of hij verbindt door als de winkel open is.'],
            ['"40 belegde broodjes voor ons kantoor, morgen bezorgen."',     'Kan een dag vooruit; hij vraagt afhalen of bezorgen, adres en bedrijfsnaam, leest terug en geeft een bestelnummer.'],
            ['"Hebben jullie glutenvrij brood?"',                            'Ja, op bestelling, alleen op vrijdag, twee dagen vooruit. Hij leest niet de hele lijst voor.'],
            ['"Wat kost een bruidstaart?"',                                  'Op aanvraag, na een proefsessie; hij noemt geen bedrag.'],
            ['Bestelnummer K-1057 met achternaam "De Wit"',                 'Past niet: hij kan die bestelling niet vinden op die naam.'],
        ],
        'niet'       => [
            'Uitspraken doen over allergenen die niet uit de productinformatie komen.',
            'Een bestelling wijzigen of annuleren; dat loopt via de bakkerij.',
            'Betalingen aannemen, gesprekken opnemen of iets verzinnen.',
            'Afspraken in een agenda zetten.',
        ],
        'tips'       => [
            'Praat gewoon, zoals je tegen een bakker praat. Geen trefwoorden.',
            'Onderbreek hem gerust midden in een zin: hij stopt en luistert.',
            'Zeg een keer alleen "bedankt": hij rondt dan kort af.',
            'Bestel gerust iets: je zit nergens aan vast en er wordt niets gebakken.',
        ],
        'slot'       => 'Zo zou hij ook bij jou opnemen, met jouw openingstijden, jouw assortiment en jouw bestelregels. Plan een gesprek van een half uur via de website; dan richten we een proefversie in met jouw gegevens.',
    ],

    'apotheek' => [
        'titel'      => 'Zo probeer je de AI-telefoniste van Apotheek De Linde',
        'bestand'    => 'Infoblad-AI-telefonie-apotheekdemo.pdf',
        'nummer'     => '088 254 5150',
        'bedrijf'    => 'Apotheek De Linde',
        'intro'      => 'Apotheek De Linde bestaat niet. Het is een verzonnen apotheek aan de Lindelaan 12 in Amersfoort, met drie verzonnen patiënten, zodat je kunt horen hoe een assistent zich in een apotheek gedraagt: eerst vaststellen wie er belt, dan pas iets zeggen, en bij elke vraag over werking of dosering meteen naar een mens. Herhaalverzoeken die je doet komen in een demo-omgeving terecht; er wordt niets bereid en niets bezorgd.',
        'feiten'     => [
            ['Openingstijden',  'Maandag t/m vrijdag van acht uur tot half zes. Buiten die tijden noemt hij de dienstapotheek en verbindt hij niet door.'],
            ['Identificatie',   'Geboortedatum én postcode, in die volgorde. Hij leest terug en zoekt pas op na een "ja". Zonder die combinatie zegt hij niets over een patiënt, ook niet dát iemand patiënt is.'],
            ['Dienstwaarneming', 'Dienstapotheek Eemland in Amersfoort, dag en nacht open. Adres en nummer noemt hij als je erom vraagt.'],
            ['Bezorgen',        'Gratis, op werkdagen in Amersfoort, Leusden, Soest, Hoogland en Nijkerk, tussen twee en zes uur. Wat voor elf uur klaarligt gaat dezelfde dag mee.'],
        ],
        'data_titel' => 'De patiënten die bestaan',
        'data_kop'   => ['Naam', 'Geboortedatum', 'Postcode', 'Wat er staat'],
        'data'       => [
            ['Willemien de Groot', '14 maart 1951',    '3817 GH', 'Bloeddrukverlager, herhaalbaar; laatste uitgifte drie maanden geleden. Er ligt niets klaar.'],
            ['Ahmed Yilmaz',       '2 november 1978',  '3823 CD', 'Maagbeschermer, klaar sinds gisteren, in de afhaalkluis.'],
            ['Trees Bakker',       '27 juli 1943',     '3817 GH', 'Bloedverdunner, vandaag klaar, aan de balie.'],
        ],
        'probeer'    => [
            ['"Ligt mijn recept klaar?" als Ahmed Yilmaz',                   'Hij vraagt geboortedatum, dan postcode, leest terug en wacht op ja. Dan: het ligt klaar in de kluis.'],
            ['"Ik wil mijn bloeddrukpillen herhalen" als Willemien de Groot', 'Na identificatie noteert hij een herhaalverzoek en zegt wanneer het klaarligt, ophalen of bezorgen. Hij annuleert of wijzigt nooit iets.'],
            ['Goede geboortedatum, verkeerde postcode',                       'Hij vindt niemand en zegt dat eerlijk; hij raadt niet en probeert het niet op een andere manier.'],
            ['"Mag ik dit samen met paracetamol nemen?"',                      'Hij stopt meteen: werking, bijwerking, dosering en combinaties zijn voor de apotheker. Binnen openingstijden verbindt hij door.'],
            ['"Welke apotheek heeft vanavond dienst?"',                        'Dienstapotheek Eemland, Amersfoort, dag en nacht open. Adres en nummer alleen als je erom vraagt.'],
            ['"Ik bel voor mijn moeder, Trees Bakker"',                        'Alleen met háár geboortedatum en postcode; anders zegt hij niets, ook niet dat ze patiënt is.'],
            ['"Bezorgen jullie?"',                                            'Ja, gratis, op werkdagen in Amersfoort en omliggende dorpen, tussen twee en zes.'],
            ['"Tot hoe laat zijn jullie open?"',                              'Van acht tot half zes. Let op hoe hij daarna stópt: hoogstens één ding erbij.'],
        ],
        'niet'       => [
            'Iets zeggen over werking, bijwerkingen, dosering of combinaties van middelen.',
            'Iets over een patiënt zeggen zonder geboortedatum én postcode.',
            'Een herhaalverzoek annuleren of wijzigen, of een middel toezeggen.',
            'Gesprekken opnemen of iets verzinnen. Bij spoedwoorden verbindt hij direct door of verwijst hij naar 112 en de huisartsenpost.',
        ],
        'tips'       => [
            'Praat gewoon, zoals je tegen een apotheek praat. Geen trefwoorden.',
            'Noem de geboortedatum zoals je dat normaal doet: "veertien maart negentienéénenvijftig".',
            'Onderbreek hem gerust midden in een zin: hij stopt en luistert.',
            'Noem geen echte patiëntgegevens: de drie hierboven zijn de enige die bestaan.',
        ],
        'slot'       => 'Zo zou hij ook bij jou opnemen, met jouw openingstijden, jouw dienstwaarneming en jouw herhaalregels. Plan een gesprek van een half uur via de website; dan richten we een proefversie in met jouw gegevens.',
    ],
];
