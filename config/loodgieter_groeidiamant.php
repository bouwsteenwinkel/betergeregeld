<?php

// Groeidiamant-pagina, branche-laag loodgieter. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/loodgieter_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je loodgietersbedrijf',
        'bedrijven' => 'loodgieters',
    ],
    'seo_titel' => 'Digitale groei voor loodgieters: website, online aanvragen, klantenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je loodgietersbedrijf stap voor stap groeit: gevonden worden op lekkage en cv in je regio, aanvragen met foto\'s online, klanten die de afspraak zien, minder overtypen en een telefoon die wordt opgenomen midden in een klus.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je loodgietersbedrijf stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je loodgietersbedrijf nu nodig heeft: gevonden worden op "lekkage" en "loodgieter" in je regio, aanvragen met foto\'s die online binnenkomen, klanten die zelf de afspraak zien, of een telefoon die wordt opgenomen terwijl jij onder een wastafel ligt.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Wie "loodgieter" plus onze plaats zoekt, vindt de concurrent',
            'b' => 'Diensten, spoedregeling, tarieven en voorrijkosten moeten meteen in beeld zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil aanvragen met foto\'s en een tijdslot binnenkrijgen',
            'b' => 'Soort klus, foto\'s en adres in één aanvraag, spoed apart.',
        ],
        'klantenportaal' => [
            't' => 'Klanten bellen om te vragen hoe laat ik kom',
            'b' => 'Afspraak, tijdvak, werkbon en factuur in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Werkbonnen en facturen typ ik \'s avonds over',
            'b' => 'Van aanvraag naar planning, werkbon en factuur zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat midden in een klus',
            'b' => 'Spoed herkend, tarieven en werkgebied beantwoord, aanvragen genoteerd; jij werkt door.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop klanten je vinden op "loodgieter" of "lekkage" plus jouw plaats, je diensten, spoedregeling en tarieven zien en direct een aanvraag doen.',
            'voorbeelden' => [
                'Gevonden op "loodgieter" en "lekkage" plus je plaats',
                'Spoedregeling en tarieven duidelijk',
                'Aanvraag met foto in één tik',
            ],
            'resultaat' => 'Meer klussen uit de eigen regio, ook bij spoed.',
            'cta' => 'Bekijk websites voor loodgieters',
        ],
        'webshop' => [
            'voor' => 'Laat klanten een klus online aanvragen met soort werk, foto\'s, adres en een tijdvak, met spoed als aparte ingang.',
            'voorbeelden' => [
                'Aanvraag met foto\'s van de lekkage of de ketel',
                'Tijdvak kiezen binnen jouw planning',
                'Spoed apart, met je spoedtarief erbij',
            ],
            'resultaat' => 'Complete aanvragen, minder terugbellen voor de basisvragen.',
            'cta' => 'Bekijk online aanvragen voor loodgieters',
            'titel' => 'Online aanvragen',
        ],
        'klantenportaal' => [
            'voor' => 'Geef klanten een eigen omgeving met de afspraak en het tijdvak, de werkbon, akkoord op meerwerk en de factuur.',
            'voorbeelden' => [
                'Tijdvak en wie er komt',
                'Werkbon en foto\'s van het werk',
                'Factuur meteen na de klus',
            ],
            'resultaat' => 'Minder "hoe laat kom je"-telefoontjes.',
            'cta' => 'Bekijk klantenportalen voor loodgieters',
        ],
        'automatisering' => [
            'voor' => 'Laat aanvragen, planning, werkbonnen, facturen en onderhoudsherinneringen (cv-ketel) automatisch lopen tussen website, planning en boekhouding, voor zover die een koppeling toelaten.',
            'voorbeelden' => [
                'Aanvraag meteen in de planning',
                'Werkbon naar factuur zonder overtypen',
                'Herinnering voor cv-onderhoud, jaarlijks vanzelf',
            ],
            'resultaat' => 'Minder avondadministratie en terugkerend onderhoudswerk.',
            'cta' => 'Bekijk automatisering voor loodgieters',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen als je midden in een klus zit: spoed herkennen en doorverbinden, tarieven en voorrijkosten noemen, werkgebied checken en een aanvraag met adres noteren.',
            'voorbeelden' => [
                'Spoed (lekkage, geen water) herkend en doorverbonden',
                'Tarieven en werkgebied uit jouw gegevens',
                'Nieuwe aanvraag met adres genoteerd',
            ],
            'resultaat' => 'Bereikbaar tijdens de klus; spoed komt altijd door.',
            'cta' => 'Bekijk AI-telefonie voor loodgieters',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een loodgieter met twee bussen:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'Het bedrijf begint met een website die gevonden wordt op "loodgieter" en "lekkage" plus de plaats, met spoedregeling en tarieven.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online aanvragen',
                'b' => 'Daarna komen aanvragen binnen met foto\'s en een tijdvak, met spoed als aparte ingang.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de werkbonnen',
                'b' => 'Vervolgens gaan werkbon en factuur vanzelf en gaat de jaarlijkse cv-onderhoudsherinnering automatisch de deur uit.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens klussen: spoed doorverbonden, tarieven en werkgebied beantwoord, aanvragen genoteerd.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd als je net onder een wastafel ligt',
        'tekst' => 'Een AI-telefoonassistent neemt op als jij midden in een klus zit. Hij herkent spoed en verbindt door, noemt tarieven en voorrijkosten, checkt of een adres in je werkgebied valt en noteert een nieuwe aanvraag. Een diagnose op afstand geeft hij niet.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je werkgebied, tarieven en spoedregeling, en de assistent neemt op tijdens de klus. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
