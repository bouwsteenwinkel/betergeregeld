<?php

// Groeidiamant-pagina, branche-laag acupuncturist. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/acupuncturist_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je praktijk',
        'bedrijven' => 'acupunctuurpraktijken',
    ],
    'seo_titel' => 'Digitale groei voor acupuncturisten: website, online afspraken & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je acupunctuurpraktijk stap voor stap groeit: gevonden worden op klachten en vergoeding, online afspraken, patiënten die zelf verzetten, minder administratie en een telefoon die wordt opgenomen tijdens de behandeling.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je acupunctuurpraktijk stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je praktijk nu nodig heeft: gevonden worden op de klachten die je behandelt, online afspraken, patiënten die zelf verzetten, of een telefoon die wordt opgenomen terwijl jij de naalden zet. Elke stap sluit aan op de vorige.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Patiënten vinden mijn praktijk niet, of twijfelen over vergoeding',
            'b' => 'Klachten, tarieven en welke verzekeraars vergoeden moeten meteen duidelijk zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil een eerste consult online laten boeken',
            'b' => 'Intake, consult en vervolgafspraak zelf inplannen op een vrij moment.',
        ],
        'klantenportaal' => [
            't' => 'Patiënten bellen om te verzetten of hun factuur voor de verzekeraar',
            'b' => 'Afspraken verzetten, facturen en intakeformulier in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Herinneringen en intakes doe ik met de hand',
            'b' => 'Afspraakherinneringen, intakeformulier vooraf en facturen zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens de behandeling',
            'b' => 'Tarieven, vergoeding en afspraken opgevangen; medische vragen naar jou.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop patiënten je vinden op de klachten die je behandelt plus jouw plaats, zien wat een consult kost en of het vergoed wordt, en een afspraak maken.',
            'voorbeelden' => [
                'Gevonden op "acupunctuur" plus je plaats en klacht',
                'Tarieven en vergoeding per verzekeraar duidelijk',
                'Afspraak maken in één tik',
            ],
            'resultaat' => 'Meer nieuwe patiënten die vooraf al weten wat het kost.',
            'cta' => 'Bekijk websites voor acupuncturisten',
        ],
        'webshop' => [
            'voor' => 'Laat patiënten een eerste consult of vervolgbehandeling online inplannen op een vrij moment, met het intakeformulier vooraf ingevuld.',
            'voorbeelden' => [
                'Eerste consult boeken op een vrij tijdslot',
                'Intakeformulier vooraf, dus meer tijd voor de behandeling',
                'Afzegregels meteen duidelijk',
            ],
            'resultaat' => 'Volle agenda zonder telefoontjes tussen behandelingen door.',
            'cta' => 'Bekijk online afspraken voor acupuncturisten',
            'titel' => 'Online afspraken',
        ],
        'klantenportaal' => [
            'voor' => 'Geef patiënten een eigen omgeving waarin ze afspraken verzetten, facturen voor de verzekeraar terugvinden en hun gegevens bijwerken.',
            'voorbeelden' => [
                'Afspraak verzetten binnen jouw regels',
                'Factuur voor de verzekeraar zelf downloaden',
                'Eigen gegevens actueel houden',
            ],
            'resultaat' => 'Minder verzet- en factuurtelefoontjes.',
            'cta' => 'Bekijk patiëntenportalen voor acupuncturisten',
            'titel' => 'Patiëntenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat afspraakherinneringen, intakes en facturen automatisch lopen tussen website, agenda en administratie, voor zover je praktijksoftware dat toelaat.',
            'voorbeelden' => [
                'Herinnering een dag vooraf, vanzelf',
                'Intake voor het eerste consult in je dossier',
                'Factuur na de behandeling zonder overtypen',
            ],
            'resultaat' => 'Minder no-show en minder avondadministratie.',
            'cta' => 'Bekijk automatisering voor acupuncturisten',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen tijdens behandelingen: tarieven, vergoeding per verzekeraar, praktijktijden en afspraken. Over klachten en behandelingen zegt hij niets; dat is aan jou.',
            'voorbeelden' => [
                'Afspraak of terugbelverzoek genoteerd',
                'Vergoeding uit jouw gegevens, geen belofte',
                'Medische vragen altijd naar de acupuncturist',
            ],
            'resultaat' => 'Bereikbaar tijdens de behandeling, zonder de naalden te laten liggen.',
            'cta' => 'Bekijk AI-telefonie voor acupuncturisten',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een praktijk met twee behandelaars:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De praktijk begint met een website die gevonden wordt op de klachten die ze behandelen, met tarieven en per verzekeraar wat vergoed wordt.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online afspraken',
                'b' => 'Daarna boeken patiënten hun eerste consult zelf, met het intakeformulier vooraf ingevuld.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de herinneringen',
                'b' => 'Vervolgens gaan afspraakherinneringen en facturen vanzelf, gekoppeld aan de agenda.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens behandelingen: "wordt het vergoed", tarieven en afspraken, met medische vragen netjes doorgezet.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd terwijl je net de naalden zet',
        'tekst' => 'Een AI-telefoonassistent neemt op tijdens behandelingen. Hij noemt tarieven en praktijktijden, zegt uit jouw gegevens welke verzekeraars vergoeden, plant of verzet een afspraak en legt een terugbelverzoek vast. Over klachten of behandelingen doet hij geen uitspraak.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je tarieven, praktijktijden en vergoedingsregels, en de assistent neemt op tijdens behandelingen. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
