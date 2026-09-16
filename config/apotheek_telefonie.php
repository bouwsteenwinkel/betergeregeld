<?php

/**
 * Inhoud van /ai-telefonie-apotheek op jouw-apotheek-website.nl (16-09-2026).
 * Alleen het apotheek-eigen deel; de rest komt uit config/telefonie_basis.php.
 *
 * Geënt op wat de apotheekdemo (Apotheek De Linde, 088 254 5150, beleid/apotheek/) echt
 * doet: identificatie op geboortedatum + postcode, "ligt mijn recept klaar", herhaalverzoek
 * noteren, dienstwaarneming buiten openingstijden, en STOPPEN bij werking, bijwerking,
 * dosering of interactie. Rode woorden (spoed) gaan altijd door naar een mens.
 *
 * demo_nummer: 088 254 5150 is de apotheekdemo (Dennis, 16-09-2026: "de bestaande 5150 is
 * de demo voor apothekers"). De drie patiënten zijn de vaste rijen uit
 * scripts/_aitest-apotheek-tabellen.php; herhaalverzoeken landen in het demoportaal.
 */

return [
    'demo_nummer'    => '088 254 5150',
    'demo_naam'      => 'Apotheek De Linde',
    'demo_noot'      => 'Het demonummer is Apotheek De Linde: een apotheek die niet bestaat, met drie verzonnen patiënten, zodat je vrij kunt vragen wat je wilt. Je sluit niets af door te bellen.',
    'demo_titel'     => 'Bel Apotheek De Linde op 088 254 5150',
    'demo_lead'      => 'De Linde bestaat niet, de drie patiënten ook niet. Bel als een van hen, met geboortedatum en postcode, en vraag of het recept klaarligt of vraag een herhaling aan. Stel gerust een vraag over dosering: dan hoor je precies waar hij stopt. Er wordt niets bereid en niets bezorgd.',
    'demo_kaartjes' => [
        ['kop' => 'Willemien de Groot · 14 maart 1951 · 3817 GH', 'hoor' => 'Bloeddrukverlager, herhaalbaar. Er ligt niets klaar; hij biedt aan een herhaalverzoek te noteren.'],
        ['kop' => 'Ahmed Yilmaz · 2 november 1978 · 3823 CD',     'hoor' => 'Maagbeschermer, klaar sinds gisteren. Hij zegt dat het in de afhaalkluis ligt.'],
        ['kop' => 'Trees Bakker · 27 juli 1943 · 3817 GH',        'hoor' => 'Bloedverdunner, vandaag klaar aan de balie. Zelfde postcode als Willemien: alleen een postcode is nooit genoeg.'],
        ['kop' => 'Iemand die niet bestaat',                       'hoor' => 'Noem een andere geboortedatum: hij vindt niemand, zegt dat eerlijk en raadt niet.'],
    ],
    'demo_probeer' => [
        'Vraag of je een middel samen met paracetamol mag nemen: hij stopt en verbindt door naar de apotheker.',
        'Vraag welke apotheek vanavond dienst heeft: Dienstapotheek Eemland, dag en nacht open.',
        'Bel voor je moeder zonder haar gegevens: hij zegt niets, ook niet dat ze patiënt is.',
        'Bel na half zes: hij verbindt niet door maar noemt de dienstapotheek en legt je verzoek vast.',
    ],
    'kantoor_nummer' => '088-2545101',
    'infoblad'         => 'apotheek',         // config/telefonie_infobladen.php, knop naast het demonummer

    // CTA op de homepage (channels/partials/telefonie-home-cta): een telefoonscherm waarop
    // de assistent opneemt en dit gesprek zich uittikt. 'b' = beller, 'a' = assistent.
    'home_cta' => [
        'kicker'      => 'Nieuw: AI-telefonie voor apotheken',
        'kop'         => 'De telefoon gaat. Niemand hoeft van de balie weg.',
        'lead'        => 'Een digitale assistent die opneemt met de naam van je apotheek, eerst geboortedatum en postcode vraagt, zegt of het recept klaarligt en een herhaalverzoek noteert. Bij een vraag over dosering stopt hij en gaat het naar de apotheker. Bel de demo en hoor het zelf.',
        'bel_label'   => 'Bel de demo',
        'meer_label'  => 'Zo werkt het voor apotheken',
        'noot'        => 'Apotheek De Linde bestaat niet; je sluit niets af door te bellen. Vanaf € 89 per maand.',
        'toestel_naam'=> 'Apotheek De Linde',
        'toestel_sub' => 'Inkomend gesprek · de assistent neemt op',
        'tijd'        => '08:12',
        // Luisterstrook onder de band: de ingesproken demo op telefonie.betergeregeld.com.
        'luister' => [
            'url'          => 'https://telefonie.betergeregeld.com/demo/apotheek',
            'titel'        => 'Liever eerst luisteren?',
            'sub'          => 'Vijf ingesproken gesprekken met Apotheek De Linde, naast het portaal dat live meebeweegt',
            'duur'         => '± 6 min',
            'hoofdstukken' => ['Herhaalrecept en bezorgen', 'Klaarliggen en eigen risico', 'Een bijwerking', 'Inschrijven en de dienstapotheek', 'Spoed'],
        ],
        'gesprek' => [
            ['b', 'Ligt mijn recept al klaar?'],
            ['a', 'Dat kijk ik na. Mag ik uw geboortedatum?'],
            ['b', 'Twee november 1978.'],
            ['a', 'En uw postcode?'],
            ['b', '3823 CD.'],
            ['a', 'Dank u, meneer Yilmaz. Uw recept ligt klaar in de afhaalkluis.'],
        ],
    ],

    'woorden' => [
        'bedrijf'   => 'je apotheek',
        'bedrijven' => 'apotheken',
        'klant'     => 'patiënt',
        'klanten'   => 'patiënten',
        'gegevens'  => 'openingstijden, dienstwaarneming, hoe herhaalrecepten en bezorgen bij jou lopen, wat er zonder recept te koop is en wanneer een vraag naar een apotheker moet',
    ],

    'seo_titel' => 'AI telefonie voor apotheken | AI telefoonassistent die opneemt als de balie vol staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor apotheken',
        'title'   => 'AI-telefonie voor je apotheek: de telefoon wordt opgenomen, ook als er vijf mensen aan de balie staan',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je apotheek, de beller eerst vaststelt op geboortedatum en postcode, zegt of een recept klaarligt, een herhaalverzoek noteert, de dienstapotheek noemt buiten openingstijden, en bij elke vraag over werking, dosering of bijwerking meteen doorverbindt met een mens. Tijdens de ochtendpiek, de lunch en na sluitingstijd.',
        'alt'     => 'De telefonische assistent van een apotheek',
        'usps'    => [
            'Neemt altijd op, ook als de balie vol staat',
            'Eerst geboortedatum en postcode, dan pas een antwoord',
            'Geen woord over werking of dosering: dat gaat naar de apotheker',
            'Na elk gesprek een samenvatting in je portaal',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Maandagochtend',                    'b' => 'Rij tot de deur, recepten van het weekend, en de telefoon gaat elke twee minuten. De assistente die opneemt is de assistente die niet aan de balie staat.'],
        ['t' => '"Ligt mijn recept klaar?"',         'b' => 'De vraag die het vaakst gesteld wordt en het minst een apotheker nodig heeft. Het antwoord staat in je systeem; alleen de identificatie moet kloppen.'],
        ['t' => 'Herhaalrecepten',                   'b' => 'Wie belt om een herhaalrecept aan te vragen, wil het kwijt en ophangen. De assistent noteert middel, naam en geboortedatum en zegt wanneer het klaarligt volgens jouw regel.'],
        ['t' => 'Na sluitingstijd',                  'b' => '"Welke apotheek heeft dienst?" om acht uur \'s avonds. Nu een bandje; met de assistent een antwoord met adres en nummer, en bij spoed direct de juiste verwijzing.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de apotheek zelf heeft aangeleverd. Zo praat de assistent ook over jouw apotheek: alleen wat jij hebt doorgegeven, en nooit iets over een patiënt zonder geboortedatum én postcode.',
    'gesprekken' => [
        ['vraag' => 'Ligt mijn recept al klaar?',                                       'antwoord' => 'Dat kijk ik na. Mag ik uw geboortedatum? En uw postcode? Dank u. Er ligt een recept voor u klaar; u kunt het vandaag tot half zes ophalen.'],
        ['vraag' => 'Ik wil mijn bloeddrukmedicijn herhalen.',                            'antwoord' => 'Dat noteer ik. Na uw geboortedatum en postcode leg ik het herhaalverzoek vast op de naam van het middel zoals u het noemt. Het ligt volgens onze regel twee werkdagen later klaar; is het eerder op, zeg het dan nu, dan verbind ik u door.'],
        ['vraag' => 'Mag ik dit middel samen met ibuprofen nemen?',                        'antwoord' => 'Die vraag hoort bij de apotheker; daar zeg ik zelf niets over. Ik verbind u nu door. Is de apotheek gesloten, dan geef ik u het nummer van de dienstapotheek.'],
        ['vraag' => 'Welke apotheek heeft vanavond dienst?',                               'antwoord' => 'Vanavond neemt Dienstapotheek Gooi en Vechtstreek waar, aan de Rijksstraatweg 1 in Naarden, telefoon 035 123 45 67. Gaat het om iets dat niet kan wachten, dan belt u de huisartsenpost.'],
        ['vraag' => 'Bezorgen jullie ook?',                                                'antwoord' => 'Ja, binnen Hilversum bezorgen we op dinsdag en vrijdag, gratis bij herhaalrecepten. Wilt u dat ik bezorgen bij uw verzoek noteer?'],
        ['vraag' => 'Ik bel voor mijn moeder, ligt haar recept klaar?',                     'antwoord' => 'Voor iemand anders kan ik alleen iets nakijken als u haar geboortedatum en postcode weet en zij daarmee akkoord is. Anders vraag ik haar zelf te bellen, of ik noteer een terugbelverzoek.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => '"Ligt mijn recept klaar?"',          'b' => 'Na geboortedatum en postcode zegt hij of er iets klaarligt en tot hoe laat. Hij noemt het middel niet uit zichzelf.'],
        ['t' => 'Herhaalverzoeken',                    'b' => 'Middel zoals de patiënt het noemt, naam, geboortedatum, ophalen of bezorgen. Volgens jouw regel voor wanneer het klaarligt; jij verwerkt het.'],
        ['t' => 'Dienstwaarneming',                    'b' => 'Buiten openingstijden de dienstapotheek en de huisartsenpost, met adres en nummer, precies zoals jij ze aanlevert.'],
        ['t' => 'Praktische vragen',                   'b' => 'Openingstijden, bezorgen, inschrijven als nieuwe patiënt, de app, wat er zonder recept te koop is. Uit jouw gegevens.'],
        ['t' => 'Spoed herkennen',                     'b' => 'Op de rode woorden die jij vastlegt (benauwd, allergische reactie, kind, insuline op) verbindt hij meteen door of verwijst hij naar 112 en de huisartsenpost.'],
        ['t' => 'Dagberichten',                        'b' => 'Middel X niet leverbaar, vandaag vanaf drie uur gesloten, bezorging vervalt: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Iets zeggen over werking, bijwerkingen, dosering of combinaties van middelen. Daar stopt hij en verbindt hij door naar de apotheker.',
        'Iets over een patiënt zeggen aan iemand die niet is vastgesteld op geboortedatum én postcode, ook niet dát iemand patiënt is.',
        'Een herhaalverzoek annuleren, wijzigen of een medicijn toezeggen. Hij noteert; de apotheek beslist.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 60,
            'dagen'           => 5,
            'minuten'         => 1.5,
            'uurloon'         => 32,
            'oppakken'        => 2,
            'gemist_per_week' => 25,
            'bestelling_pct'  => 0,
            'bestelwaarde'    => 0,
            'afhandel_pct'    => 55,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Bij een apotheek gaat het niet om gemiste omzet maar om baliemedewerkers die niet meer elke twee minuten de telefoon hoeven op te nemen. Zet de schuiven op jouw apotheek en zie hoeveel uur er per maand vrijkomt voor de balie. Laat de omzetschuiven op nul.',
            'deel_label'   => 'Deel daarvan dat omzet was (bij een apotheek meestal nul)',
            'waarde_label' => 'Waarde per gemist gesprek (laat op nul)',
            'uren_tegel'   => 'per maand niet meer aan de telefoon achter de balie',
            'tijd_tegel'   => 'aan loonkosten van assistentes die vrijkomen voor de balie en de receptverwerking',
            'omzet_tegel'  => 'omzet in gemiste telefoontjes (voor een apotheek meestal niet van toepassing)',
        ],
    ],

    'faq_titel' => 'Wat apothekers ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn apotheek?',          'a' => 'Ja. Hij neemt op met de naam van je apotheek, stelt de beller vast op geboortedatum en postcode, zegt of een recept klaarligt, noteert herhaalverzoeken en geeft buiten openingstijden de dienstwaarneming door.'],
        ['q' => 'Geeft hij farmaceutisch advies?',                                    'a' => 'Nee, nooit. Bij elke vraag over werking, bijwerking, dosering of combinaties stopt hij en verbindt hij door naar een apotheker. Dat is de hardste regel in zijn opdracht.'],
        ['q' => 'Hoe zit het met de privacy van patiënten?',                          'a' => 'Hij zegt niets over een patiënt voordat geboortedatum en postcode kloppen, ook niet dát iemand patiënt is. Hij noemt een middel niet uit zichzelf. Geen opnames; de samenvatting staat alleen in jouw portaal. We sluiten een verwerkersovereenkomst en richten de bewaartermijn in zoals jij die wilt.'],
        ['q' => 'Kan hij zien of een recept klaarligt?',                              'a' => 'Met een koppeling op je apotheeksysteem leest hij dat live. Zonder koppeling werkt hij met een lijst die jij bijhoudt, of noteert hij de vraag voor de balie.'],
        ['q' => 'Wat doet hij bij spoed?',                                             'a' => 'Op de rode woorden die jij vastlegt verbindt hij direct door naar een mens, en buiten openingstijden verwijst hij naar de dienstapotheek, de huisartsenpost of 112. Hij probeert nooit zelf te beoordelen hoe erg het is.'],
        ['q' => 'Werkt hij samen met onze herhaalservice en app?',                    'a' => 'Hij legt uit hoe die werken en verwijst ernaar. Wie liever belt, wordt gewoon geholpen; het verzoek komt op dezelfde plek terecht als jij dat inricht.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor apotheken',          'b' => 'Openingstijden, dienstwaarneming en herhaalrecepten aanvragen op je site, zodat er minder gebeld wordt.'],
        ['facet' => 'automatisering', 't' => 'Herhaalverzoeken automatisch verwerken', 'b' => 'Een herhaalverzoek van de assistent komt op dezelfde lijst als een aanvraag via je website of app.'],
        ['facet' => 'klantenportaal', 't' => 'Patiëntenportaal',                'b' => 'Patiënten zien zelf of hun recept klaarligt en vragen herhaalrecepten aan, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan voor je apotheek',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan laten we je de apotheekdemo horen en richten we een proefversie in met jouw openingstijden, dienstwaarneming en herhaalregels.',
];
