<?php

/**
 * Inhoud van /ai-telefonie-loodgieter op jouw-loodgieter-website.nl (16-09-2026).
 * Alleen het loodgieter-eigen deel; de rest komt uit config/telefonie_basis.php
 * (zie App\Support\TelefonieConfig). Wat de assistent bij een lekkage zegt komt uit de
 * spoedinstructie die de loodgieter zelf aanlevert -- hij verzint geen handelingen.
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je loodgietersbedrijf',
        'bedrijven' => 'loodgieters',
        'klant'     => 'klant',
        'klanten'   => 'klanten',
        'gegevens'  => 'werkgebied, diensten, spoedregeling, tarieven per uur en voorrijkosten',
    ],

    'seo_titel' => 'AI telefonie voor loodgieters | AI telefoonassistent die opneemt als jij op de klus staat',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor loodgieters',
        'title'   => 'AI-telefonie voor je loodgietersbedrijf: de telefoon wordt opgenomen, ook als jij onder een wasbak ligt',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je bedrijf, weet of de beller in je werkgebied zit, een lekkage van een druppelende kraan onderscheidt, spoed doorverbindt en de rest als terugbelverzoek klaarzet. Op de klus, in de auto en buiten werktijd.',
        'alt'     => 'De telefonische assistent van een loodgietersbedrijf',
        'usps'    => [
            'Neemt altijd op, ook als je met beide handen bezig bent',
            'Spoed gaat door, de rest wordt netjes genoteerd',
            'Antwoordt uit jouw werkgebied en tarieven, niet uit het internet',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Midden in een klus',          'b' => 'Je zit met een doorgesneden leiding en de telefoon gaat. Opnemen kan niet; niet opnemen betekent dat de beller de volgende loodgieter uit de lijst belt.'],
        ['t' => 'In de auto',                   'b' => 'Tussen twee adressen bel je terug wie je gemist hebt. De helft neemt niet meer op, want die heeft inmiddels iemand anders.'],
        ['t' => 'Lekkage om elf uur \'s avonds', 'b' => 'Wie een nat plafond ziet belt meteen. Krijgt hij voicemail, dan is het adres morgen van een ander.'],
        ['t' => 'Vragen die geen loodgieter nodig hebben', 'b' => '"Komen jullie ook in Baarn?", "Wat kost een voorrijbeurt?", "Wanneer komt de monteur?" Tien keer per dag dezelfde vraag, elke keer een onderbreking.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die het loodgietersbedrijf zelf heeft aangeleverd. Zo praat de assistent ook over jouw bedrijf: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Ik heb een lekkage onder de keuken, er komt water uit het kastje.', 'antwoord' => 'Dat is spoed. Ik verbind u nu door met de monteur van dienst. Lukt dat niet binnen een minuut, dan noteer ik uw adres en nummer en belt hij u binnen een kwartier terug. Kunt u de kraan onder de gootsteen alvast dichtdraaien?'],
        ['vraag' => 'Komen jullie ook in Baarn?',                                   'antwoord' => 'Ja, Baarn hoort bij ons werkgebied. Waar kan ik u mee helpen?'],
        ['vraag' => 'Wat kost het als jullie langskomen?',                          'antwoord' => 'We rekenen € 45 voorrijkosten en € 68 per uur, exclusief materiaal en btw. Een vaste prijs voor een grotere klus krijgt u na een bezoek. Wilt u dat ik een afspraakverzoek noteer?'],
        ['vraag' => 'Mijn cv doet het niet, is dat ook iets voor jullie?',          'antwoord' => 'Storingen aan de cv-ketel doen we, onderhoud en nieuwe ketels ook. Om welk merk gaat het, en is er een foutcode op het display?'],
        ['vraag' => 'Wanneer komt de monteur vandaag?',                             'antwoord' => 'Ik zie uw afspraak niet in mijn gegevens, dus ik wil geen tijd noemen die niet klopt. Ik geef uw vraag door en de planning belt u terug met een tijdvak. Klopt het nummer 06-1234 5678?'],
        ['vraag' => 'Ik wil een offerte voor een nieuwe badkamerleiding.',           'antwoord' => 'Dan noteer ik een offerteverzoek. Wat is het adres, en wanneer kan iemand komen kijken? Ik lees het even terug: Kerkstraat 12 in Soest, het liefst een ochtend, op naam van Van Dijk. U hoort morgen van ons.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Spoed herkennen',                'b' => 'Lekkage, gesprongen leiding, geen water: hij verbindt door naar de monteur van dienst en geeft de instructie die jij hebt vastgelegd, zoals de hoofdkraan dichtdraaien.'],
        ['t' => 'Werkgebied en diensten',          'b' => 'Of je in die plaats komt, wat je wel en niet doet (cv, riool, dakgoot, badkamer), en wat je liever doorverwijst.'],
        ['t' => 'Tarieven en voorrijkosten',       'b' => 'Uurtarief, voorrijkosten, spoedtoeslag buiten werktijd: precies zoals jij ze doorgeeft, en verder niets.'],
        ['t' => 'Afspraak- en offerteverzoeken',   'b' => 'Adres, wat er aan de hand is, wanneer het uitkomt. Hij leest het terug en jij plant het in.'],
        ['t' => 'Dagberichten',                    'b' => 'Vandaag vol, monteur ziek, storing bij de waterleiding in de wijk: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Een aankomsttijd beloven of een afspraak inplannen. Hij legt het verzoek vast; jij bevestigt met een tijdvak.',
        'Handelingen adviseren die niet in jouw spoedinstructie staan. "Zet de hoofdkraan dicht" mag als jij dat zo hebt vastgelegd; zelf sleutelen aan een ketel adviseren nooit.',
        'Een vaste prijs noemen voor werk dat eerst bekeken moet worden.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 10,
            'dagen'           => 5,
            'minuten'         => 2,
            'uurloon'         => 45,
            'oppakken'        => 5,
            'gemist_per_week' => 6,
            'bestelling_pct'  => 40,
            'bestelwaarde'    => 150,
            'afhandel_pct'    => 60,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw bedrijf en zie wat de telefoon je nu per maand kost aan onderbroken klussen, en welke omzet er in gemiste telefoontjes zit. Bij een loodgieter is dat laatste het grootste getal: wie niet opneemt, wordt niet teruggebeld.',
            'deel_label'   => 'Deel daarvan dat een klus was geworden',
            'waarde_label' => 'Gemiddelde klus die je zo misloopt',
            'uren_tegel'   => 'per maand niet meer aan de telefoon of uit een klus gehaald',
            'tijd_tegel'   => 'aan uren die weer naar sleutelen gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat loodgieters ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn loodgietersbedrijf?', 'a' => 'Ja. Hij neemt op met de naam van je bedrijf, weet je werkgebied en tarieven, herkent spoed en legt de rest vast als afspraak- of terugbelverzoek.'],
        ['q' => 'Wat doet hij bij een lekkage?',                                     'a' => 'Spoed verbindt hij direct door naar het nummer dat jij daarvoor opgeeft, bijvoorbeeld je mobiel. Neem je niet op, dan noteert hij adres en nummer en zegt hij dat je binnen een afgesproken tijd terugbelt. Hij geeft alleen de instructie die jij hebt vastgelegd, zoals de hoofdkraan dichtdraaien.'],
        ['q' => 'Kan hij zeggen hoe laat de monteur komt?',                          'a' => 'Alleen als jouw planning gekoppeld is. Zonder koppeling zegt hij eerlijk dat hij het niet weet en zorgt hij dat de planning terugbelt. Liever dat dan een tijd die niet klopt.'],
        ['q' => 'Noemt hij prijzen?',                                                'a' => 'Uurtarief, voorrijkosten en toeslagen die jij hebt doorgegeven wel. Een vaste prijs voor een klus niet; daarvoor legt hij een offerteverzoek vast.'],
        ['q' => 'Werkt hij ook voor cv-storingen en riool?',                          'a' => 'Hij handelt af wat jij als dienst opgeeft. Doe je geen riool, dan zegt hij dat en verwijst hij door als jij een collega hebt opgegeven.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor loodgieters',        'b' => 'Werkgebied, tarieven en een storingsknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een afspraakverzoek van de assistent komt op dezelfde planning als een aanvraag via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Klantenportaal voor vaste klanten', 'b' => 'VvE\'s en verhuurders die nu bellen voor de status van een melding, kijken zelf.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw werkgebied',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw werkgebied, tarieven en spoedregeling en bel je zelf om te horen hoe hij reageert op een lekkage.',
];
