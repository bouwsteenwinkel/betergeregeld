<?php

/**
 * Inhoud van /ai-telefonie-golfschool op jouw-golfschool-website.nl (16-09-2026).
 * Alleen het golfschool-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Een golfpro staat op de driving range of de baan; de telefoon zit in de tas.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je golfschool',
        'bedrijven' => 'golfscholen',
        'klant'     => 'leerling',
        'klanten'   => 'leerlingen',
        'gegevens'  => 'lessen en pakketten met prijzen, het traject naar handicap 54, clinics, de baan of range waar je lesgeeft, wat mensen mee moeten nemen en de afzegregels',
    ],

    'seo_titel' => 'AI telefonie voor golfscholen | AI telefoonassistent die opneemt terwijl jij op de range staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor golfscholen',
        'title'   => 'AI-telefonie voor je golfschool: de telefoon wordt opgenomen, ook als jij op de range staat',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je golfschool, uitlegt wat een kennismakingsles of het traject naar handicap 54 kost, een lesverzoek of clinic-aanvraag vastlegt, zegt wat een beginner mee moet nemen en afzeggingen noteert. Tijdens de les, op de baan en in het weekend.',
        'alt'     => 'De telefonische assistent van een golfschool',
        'usps'    => [
            'Neemt altijd op, ook midden in een swinganalyse',
            'Lesverzoeken en clinic-aanvragen compleet in je mail',
            'Prijzen, pakketten en het handicap-54-traject uit jouw eigen gegevens',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Tijdens de les',                     'b' => 'Je staat achter een leerling die net zijn grip heeft veranderd en de telefoon gaat in je tas. Opnemen kan niet; de beller wil een bedrijfsclinic voor twintig man.'],
        ['t' => 'Zaterdagochtend',                    'b' => 'Groepsles, drukte op de range, en iedereen die een kennismakingsles wil, belt precies dan. Nu: voicemail.'],
        ['t' => '"Wat heb ik nodig om te beginnen?"', 'b' => 'Kleding, clubs, wel of geen handicap: dezelfde vragen, elke week. De assistent beantwoordt ze uit jouw lijst.'],
        ['t' => 'Bedrijfsclinics',                    'b' => 'Een HR-medewerker belt over een clinic voor een teamuitje. Dat is de grootste order van de maand en hij belt onder kantoortijd, als jij lesgeeft.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de golfschool zelf heeft aangeleverd. Zo praat de assistent ook over jouw golfschool: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Ik heb nog nooit gegolfd, hoe begin ik?',                                'antwoord' => 'Met een kennismakingsles van een uur voor € 45, clubs en ballen inbegrepen. Bevalt het, dan is er het pakket naar handicap 54: acht lessen, theorie en het examen voor € 495. Zal ik een kennismakingsles voor u aanvragen?'],
        ['vraag' => 'Wat moet ik meenemen?',                                                    'antwoord' => 'Gemakkelijk zittende kleding en sportschoenen zijn genoeg; clubs en ballen zijn er. Op de baan geldt een kledingcode, maar op de range niet.'],
        ['vraag' => 'We willen een clinic voor ons bedrijf, twintig mensen.',                    'antwoord' => 'Leuk. Een bedrijfsclinic van twee uur voor twintig personen kost € 950, inclusief clubs, ballen en een afsluitende putt-wedstrijd. Ik noteer uw bedrijf, het aantal personen en de voorkeursdatum; Mark belt u vandaag nog terug om het te plannen.'],
        ['vraag' => 'Ik moet mijn les van morgen afzeggen.',                                    'antwoord' => 'Ik geef het door. Afzeggen tot 24 uur van tevoren is gratis; daarna wordt de les in rekening gebracht. Mag ik uw naam, en wilt u meteen een nieuwe voorkeur doorgeven?'],
        ['vraag' => 'Kan ik bij jullie mijn handicap 54 halen?',                                'antwoord' => 'Ja. Het traject bestaat uit acht praktijklessen, een theoriecursus en het praktijkexamen op onze baan. De meeste mensen doen er drie tot vier maanden over. Zal ik een intake voor u aanvragen?'],
        ['vraag' => 'Wat doe ik verkeerd als mijn bal steeds naar rechts gaat?',                 'antwoord' => 'Dat kan ik aan de telefoon niet beoordelen, en de pro ook niet zonder u te zien. Ik noteer een verzoek voor een losse les; dan kijkt hij ernaar op de range.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Lessen, pakketten en prijzen',     'b' => 'Kennismakingsles, losse les, het traject naar handicap 54, groepslessen, clinics. Uit jouw prijslijst.'],
        ['t' => 'Lesverzoeken',                      'b' => 'Naam, niveau, wanneer, alleen of samen. Jij plant in en bevestigt.'],
        ['t' => 'Bedrijfsclinics en groepen',        'b' => 'Aantal, datum, wensen, contactpersoon. Compleet in je mail, klaar om een offerte te sturen.'],
        ['t' => 'Praktische vragen',                 'b' => 'Wat meenemen, kledingcode, parkeren, waar de range is, of kinderen mee kunnen. Uit jouw gegevens.'],
        ['t' => 'Dagberichten',                      'b' => 'Range dicht door onweer, greens beluchtingsweek, vakantie: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Golftechnisch advies geven. "Waarom slice ik?" is een vraag voor op de range, niet voor aan de telefoon.',
        'Lessen inplannen of verzetten. Hij legt het verzoek vast; jij bevestigt de tijd.',
        'Baanreserveringen of greenfees regelen. Dat loopt via de club; hij verwijst door naar het nummer dat jij opgeeft.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 6,
            'dagen'           => 6,
            'minuten'         => 2.5,
            'uurloon'         => 45,
            'oppakken'        => 3,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 20,
            'bestelwaarde'    => 250,
            'afhandel_pct'    => 75,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw golfschool en zie welke omzet er in gemiste telefoontjes zit. Een pro die lesgeeft neemt niet op, en een kennismakingsles die niet gepland wordt, wordt geen handicap-54-traject.',
            'deel_label'   => 'Deel daarvan dat een nieuwe leerling of clinic was',
            'waarde_label' => 'Gemiddelde waarde van zo\'n nieuwe leerling of clinic',
            'uren_tegel'   => 'per maand niet meer aan terugbellen tussen de lessen',
            'tijd_tegel'   => 'aan uren die weer naar de range gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat golfpro\'s ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn golfschool?',  'a' => 'Ja. Hij neemt op met de naam van je golfschool, legt uit wat lessen en pakketten kosten, legt les- en clinic-verzoeken vast en beantwoordt de praktische vragen van beginners.'],
        ['q' => 'Kan hij lessen inplannen?',                                   'a' => 'Nee, bewust niet. Hij noteert wanneer de leerling kan; jij bevestigt de tijd. Zo blijft je lesrooster van jou en gebeuren er geen dubbele boekingen op de range.'],
        ['q' => 'Wat doet hij met een aanvraag voor een bedrijfsclinic?',       'a' => 'Aantal personen, datum, wensen en contactpersoon vastleggen, de prijs noemen die jij hebt opgegeven, en zorgen dat jij dezelfde dag terugbelt. Dat is de aanvraag die je niet wilt missen.'],
        ['q' => 'Werkt hij samen met de club waar ik lesgeef?',                 'a' => 'Hij weet wat jij hem vertelt: waar de range is, hoe parkeren werkt, en dat baanreserveringen via de club lopen. Vragen voor de club verwijst hij door naar het nummer dat jij opgeeft.'],
        ['q' => 'Geeft hij golfadvies?',                                       'a' => 'Nee. Bij een technische vraag zegt hij dat de pro dat op de range moet zien en noteert hij een lesverzoek.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor golfscholen',       'b' => 'Pakketten, prijzen en een aanvraagknop voor kennismakingslessen, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een lesverzoek van de assistent komt op dezelfde lijst als een aanmelding via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Leerlingenportaal',              'b' => 'Leerlingen zien hun lessen en voortgang naar handicap 54 zelf, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw lespakketten',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw pakketten en de baan waar je lesgeeft, en bel je zelf alsof je nog nooit een club hebt vastgehouden.',
];
