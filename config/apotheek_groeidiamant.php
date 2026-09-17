<?php

// Groeidiamant-pagina, branche-laag apotheek. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/apotheek_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je apotheek',
        'bedrijven' => 'apotheken',
    ],
    'seo_titel' => 'Digitale groei voor apotheken: website, herhaalrecepten online & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je apotheek stap voor stap groeit: gevonden worden op openingstijden en dienst, herhaalrecepten en bezorging online, patiënten die zelf hun status zien, minder handwerk en een telefoon die wordt opgenomen op maandagochtend.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je apotheek stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je apotheek nu nodig heeft: gevonden worden op openingstijden en dienstapotheek, herhaalrecepten en bezorging online aanvragen, patiënten die zelf zien of hun recept klaarligt, of een telefoon die wordt opgenomen terwijl de balie vol staat.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Patiënten bellen voor openingstijden en de dienstapotheek',
            'b' => 'Openingstijden, dienst, bezorging en herhaalrecepten moeten meteen te vinden zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil herhaalrecepten en bezorging online laten aanvragen',
            'b' => 'Herhaalrecept, bezorgafspraak en zelfzorg zonder telefoontje.',
        ],
        'klantenportaal' => [
            't' => '"Ligt mijn recept klaar?" is de vraag van de dag',
            'b' => 'Status van recepten, bezorging en medicatieoverzicht in een eigen omgeving, binnen de privacyregels.',
        ],
        'automatisering' => [
            't' => 'Bezorgroutes en berichten regel ik met de hand',
            'b' => 'Klaarmeldingen, bezorgplanning en herinneringen zonder handwerk, gekoppeld aan het AIS waar dat kan.',
        ],
        'ai' => [
            't' => 'De telefoon gaat terwijl de balie vol staat',
            'b' => 'Openingstijden, "ligt mijn recept klaar" en herhaalrecepten opgevangen; medicatievragen naar de apotheker.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop patiënten je vinden op "apotheek" plus jouw plaats, meteen de openingstijden, dienstapotheek en bezorgregels zien en een herhaalrecept aanvragen.',
            'voorbeelden' => [
                'Openingstijden en dienstapotheek bovenaan',
                'Herhaalrecept aanvragen in één tik',
                'Bezorgen en afhalen uitgelegd',
            ],
            'resultaat' => 'Minder telefoontjes over openingstijden en dienst.',
            'cta' => 'Bekijk websites voor apotheken',
        ],
        'webshop' => [
            'voor' => 'Laat patiënten herhaalrecepten, bezorging en zelfzorgproducten online aanvragen, met de regels die jouw apotheek hanteert.',
            'voorbeelden' => [
                'Herhaalrecept aanvragen met verzekerings- en geboortegegevens',
                'Bezorgmoment kiezen binnen jouw routes',
                'Zelfzorg bestellen voor afhalen',
            ],
            'resultaat' => 'Meer aanvragen digitaal, minder wachtrij aan de balie.',
            'cta' => 'Bekijk online aanvragen voor apotheken',
            'titel' => 'Online aanvragen',
        ],
        'klantenportaal' => [
            'voor' => 'Geef patiënten een beveiligde omgeving waarin ze zien of een recept klaarligt, bezorging volgen en hun gegevens bijwerken. Wat erin komt richten we in binnen de privacyregels en wat het apotheeksysteem toelaat.',
            'voorbeelden' => [
                '"Ligt mijn recept klaar?" zien ze zelf',
                'Bezorging volgen',
                'Gegevens en voorkeuren bijwerken',
            ],
            'resultaat' => 'Minder statusvragen aan de telefoon.',
            'cta' => 'Bekijk patiëntenportalen voor apotheken',
            'titel' => 'Patiëntenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat klaarmeldingen, bezorgplanning en herinneringen automatisch lopen tussen website, portaal en apotheeksysteem, voor zover dat systeem een koppeling toelaat.',
            'voorbeelden' => [
                'Klaarmelding per sms of mail, vanzelf',
                'Bezorgroute uit de aanvragen van de dag',
                'Herinnering voor een herhaalrecept dat afloopt',
            ],
            'resultaat' => 'Minder handwerk aan de balie en minder gemiste bezorgingen.',
            'cta' => 'Bekijk automatisering voor apotheken',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen als de balie vol staat: openingstijden, dienstapotheek, "ligt mijn recept klaar" en herhaalrecepten aannemen. Vragen over medicijnen gaan altijd naar de apotheker.',
            'voorbeelden' => [
                'Herhaalrecept met geboortedatum en postcode genoteerd',
                'Status van een recept uit jouw gegevens',
                'Medicatievragen altijd naar de apotheker',
            ],
            'resultaat' => 'Bereikbaar op maandagochtend zonder dat de balie stilvalt.',
            'cta' => 'Bekijk AI-telefonie voor apotheken',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een apotheek met twee vestigingen:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De apotheek begint met een website met openingstijden, dienstregeling en een knop voor herhaalrecepten bovenaan.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online aanvragen',
                'b' => 'Daarna vragen patiënten herhaalrecepten en bezorging online aan, met de regels van de apotheek erbij.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de klaarmeldingen',
                'b' => 'Vervolgens gaan klaarmeldingen en de bezorgplanning vanzelf, gekoppeld aan het apotheeksysteem waar dat kan.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op als de balie vol staat: openingstijden, "ligt mijn recept klaar" en herhaalrecepten, met medicatievragen naar de apotheker.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'Maandagochtend: de balie staat vol en de telefoon gaat door',
        'tekst' => 'Een AI-telefoonassistent neemt op als de balie vol staat. Hij weet openingstijden en dienstapotheek, zegt of een recept klaarligt, neemt een herhaalrecept aan met geboortedatum en postcode en legt een terugbelverzoek vast. Vragen over medicijnen gaan altijd naar de apotheker.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je openingstijden, dienstregeling en herhaalregels, en de assistent neemt op als de balie vol staat. Medicatievragen geeft hij altijd door aan de apotheker; dat leggen we bij de inrichting vast.',
        ],
    ],
];
