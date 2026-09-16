<?php

/**
 * Inhoud van /ai-telefonie-yogastudio op jouw-yogastudio-website.nl (16-09-2026).
 * Alleen het studio-eigen deel; de rest komt uit config/telefonie_basis.php.
 * Tijdens een les is de telefoon uit, en dat hoort zo. De assistent vangt op wat er
 * in die 75 minuten binnenkomt: proefles, rooster, abonnement, "kan ik nog mee vanavond".
 */

return [
    'demo_nummer'    => '',
    'kantoor_nummer' => '088-2545101',

    'woorden' => [
        'bedrijf'   => 'je yogastudio',
        'bedrijven' => 'yogastudio\'s',
        'klant'     => 'deelnemer',
        'klanten'   => 'deelnemers',
        'gegevens'  => 'lesrooster, lesvormen en niveaus, proefles en abonnementen met prijzen, wat mensen meenemen, en hoe reserveren en afmelden bij jou werkt',
    ],

    'seo_titel' => 'AI telefonie voor yogastudio\'s | AI telefoonassistent die opneemt terwijl jij lesgeeft',
    'hero' => [
        'eyebrow' => 'AI-telefonie voor yogastudio\'s',
        'title'   => 'AI-telefonie voor je yogastudio: de telefoon wordt opgenomen, ook als jij in savasana zit',
        'sub'     => 'Een telefonische assistent die opneemt met de naam van je studio, het rooster kent, uitlegt welke les bij een beginner past, een proefles als verzoek vastlegt, abonnementsvragen beantwoordt uit jouw prijslijst en zegt wat iemand mee moet nemen. Tijdens de les, in de ochtendrust en op zondag.',
        'alt'     => 'De telefonische assistent van een yogastudio',
        'usps'    => [
            'Neemt altijd op, ook als de telefoon op stil staat',
            'Proeflesverzoeken en roostervragen zonder dat je de les onderbreekt',
            'Abonnementen en prijzen uit jouw eigen lijst',
            'Na elk gesprek een samenvatting per mail',
        ],
    ],

    'wanneer_titel' => 'Wanneer de telefoon het meeste stoort',
    'wanneer' => [
        ['t' => 'Tijdens de les',                    'b' => 'De telefoon staat op stil, terecht. Maar wie om half zeven belt of hij om zeven uur nog mee kan, krijgt voicemail en blijft thuis.'],
        ['t' => '"Welke les is geschikt voor mij?"', 'b' => 'Beginners bellen liever dan dat ze een rooster met acht lesvormen lezen. Yin, hatha, vinyasa: de assistent legt het uit zoals jij dat doet.'],
        ['t' => 'Abonnementsvragen',                 'b' => '"Kan ik mijn abonnement pauzeren?", "Wat kost een tienrittenkaart?" Het staat in je voorwaarden; iemand moet het alleen zeggen.'],
        ['t' => 'Zondagochtend',                     'b' => 'De drukste les van de week en het moment waarop nieuwe mensen bellen. Er is niemand aan de balie.'],
    ],

    'gesprekken_titel' => 'Zo klinkt dat, in gewoon Nederlands',
    'gesprekken_lead'  => 'De antwoorden hieronder komen uit de gegevens die de studio zelf heeft aangeleverd. Zo praat de assistent ook over jouw studio: alleen wat jij hebt doorgegeven.',
    'gesprekken' => [
        ['vraag' => 'Ik heb nog nooit yoga gedaan, welke les moet ik nemen?',       'antwoord' => 'Dan is de hatha-les op dinsdag om half acht of de beginnersles op zaterdagochtend het fijnst: rustig tempo en veel uitleg. Een proefles kost € 10. Zal ik een proefles voor u aanvragen?'],
        ['vraag' => 'Kan ik vanavond nog mee met de les van zeven uur?',            'antwoord' => 'Reserveren doet u via de app of website; daar ziet u meteen of er plek is. Lukt dat niet, dan noteer ik uw naam en belt Anouk u voor de les terug.'],
        ['vraag' => 'Wat kost een abonnement?',                                     'antwoord' => 'Onbeperkt lessen kost € 79 per maand, één les per week € 49 per maand, en een tienrittenkaart € 135, een jaar geldig. Maandabonnementen zijn maandelijks opzegbaar.'],
        ['vraag' => 'Kan ik mijn abonnement pauzeren tijdens mijn vakantie?',        'antwoord' => 'Ja, één keer per jaar maximaal vier weken, als u het een week van tevoren doorgeeft. Ik noteer uw verzoek met de data; Anouk bevestigt het per mail.'],
        ['vraag' => 'Moet ik een eigen mat meenemen?',                              'antwoord' => 'Nee, matten, blokken en dekens zijn er. Neem makkelijke kleding en eventueel een flesje water mee. Kom een kwartier voor de les, dan kunt u rustig binnenkomen.'],
        ['vraag' => 'Ik ben zwanger, kan ik meedoen?',                              'antwoord' => 'Daar wil ik niets verkeerds over zeggen. We hebben een zwangerschapsles op donderdag; of u met een andere les mee kunt, bespreekt de docent graag met u. Ik noteer een terugbelverzoek.'],
    ],

    'kan_titel' => 'Wat de assistent afhandelt',
    'kan' => [
        ['t' => 'Rooster en lesvormen',              'b' => 'Welke les wanneer, voor wie geschikt, welke docent. Uit jouw rooster, in jouw woorden.'],
        ['t' => 'Proeflesverzoeken',                  'b' => 'Naam, ervaring, welke les, hoe gevonden. Jij bevestigt of het via je reserveringssysteem loopt.'],
        ['t' => 'Abonnementen en prijzen',            'b' => 'Wat er is, wat het kost, opzeggen, pauzeren. Uit jouw voorwaarden.'],
        ['t' => 'Praktische vragen',                  'b' => 'Wat meenemen, parkeren, hoe vroeg komen, douches, kinderen. Uit jouw gegevens.'],
        ['t' => 'Dagberichten',                       'b' => 'Les van vanavond vervalt, invaldocent, studio dicht met de feestdagen: één bericht van jou en de assistent zegt het tegen iedere beller.'],
    ],

    'niet' => [
        'Iets zeggen over blessures, zwangerschap of medische klachten. Dat is voor de docent, en hij noteert een terugbelverzoek.',
        'Een plek in een les reserveren. Dat loopt via jouw systeem; hij legt uit hoe, of noteert het verzoek.',
        'Een abonnement opzeggen of wijzigen zonder jouw bevestiging.',
    ],

    'rekenhulp' => [
        'standaard' => [
            'per_dag'         => 8,
            'dagen'           => 7,
            'minuten'         => 2,
            'uurloon'         => 30,
            'oppakken'        => 2,
            'gemist_per_week' => 8,
            'bestelling_pct'  => 20,
            'bestelwaarde'    => 200,
            'afhandel_pct'    => 75,
        ],
        'teksten' => [
            'lead'         => 'Een rekenvoorbeeld, geen belofte. Zet de schuiven op jouw studio en zie welke omzet er in gemiste telefoontjes zit. Reken een nieuwe deelnemer als een paar maanden abonnement, niet als één les.',
            'deel_label'   => 'Deel daarvan dat een nieuwe deelnemer was',
            'waarde_label' => 'Wat een nieuwe deelnemer je gemiddeld oplevert',
            'uren_tegel'   => 'per maand niet meer aan terugbellen na de les',
            'tijd_tegel'   => 'aan uren die weer naar lesgeven gaan in plaats van bellen',
        ],
    ],

    'faq_titel' => 'Wat yogadocenten ons vragen over AI-telefonie',
    'faq' => [
        ['q' => 'Kan de assistent de telefoon opnemen voor mijn yogastudio?',   'a' => 'Ja. Hij neemt op met de naam van je studio, kent het rooster en de abonnementen, legt proeflesverzoeken vast en beantwoordt de praktische vragen van nieuwe deelnemers.'],
        ['q' => 'Kan hij een plek in een les reserveren?',                        'a' => 'Niet zelf. Hij legt uit hoe reserveren bij jou werkt (app, website) en noteert anders een verzoek dat jij voor de les afhandelt. Zo blijft je bezetting kloppen.'],
        ['q' => 'Past hij bij de sfeer van mijn studio?',                          'a' => 'Hij praat in de woorden die jij aanlevert: rustig, zonder verkooppraat, en hij begroet met de naam van je studio. De stem kiezen we samen.'],
        ['q' => 'Wat zegt hij bij vragen over blessures of zwangerschap?',         'a' => 'Niets medisch. Hij noemt de les die jij daarvoor hebt (bijvoorbeeld zwangerschapsyoga) en noteert een terugbelverzoek voor de docent.'],
        ['q' => 'Ik geef ook workshops en retreats. Kan hij daarover vertellen?',   'a' => 'Ja, alles wat jij aanlevert: data, prijs, wat erin zit, hoe aanmelden. Een aanmelding legt hij vast als verzoek; betalen loopt via jouw site.'],
    ],

    'verder' => [
        ['facet' => 'website',        't' => 'Website voor yogastudio\'s',      'b' => 'Rooster, prijzen en een proeflesknop op je site, zodat de assistent minder hoeft uit te leggen.'],
        ['facet' => 'automatisering', 't' => 'Verzoeken automatisch verwerken', 'b' => 'Een proeflesverzoek van de assistent komt op dezelfde lijst als een aanmelding via je website.'],
        ['facet' => 'klantenportaal', 't' => 'Deelnemersportaal',               'b' => 'Deelnemers reserveren, pauzeren en zeggen op volgens jouw regels, zonder te bellen.'],
    ],

    'cta_titel' => 'Vraag een demo aan met jouw rooster',
    'cta_tekst' => 'Plan een gesprek van een half uur; dan richten we een proefversie in met jouw rooster en abonnementen en bel je zelf alsof je nog nooit op een mat hebt gestaan.',
];
