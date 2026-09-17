<?php

// Groeidiamant-pagina, branche-laag dietist. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/dietist_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je praktijk',
        'bedrijven' => 'diëtistenpraktijken',
    ],
    'seo_titel' => 'Digitale groei voor diëtisten: website, online afspraken, cliëntenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je diëtistenpraktijk stap voor stap groeit: gevonden worden op vergoeding en verwijzing, consulten online boeken, cliënten die zelf verzetten en hun plan zien, minder administratie en een telefoon die wordt opgenomen tijdens het consult.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je diëtistenpraktijk stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je praktijk nu nodig heeft: gevonden worden op de vragen die cliënten hebben over vergoeding en verwijzing, consulten die online worden geboekt, cliënten die zelf verzetten, of een telefoon die wordt opgenomen terwijl jij in consult zit.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Cliënten weten niet of het vergoed wordt en bellen daarvoor',
            'b' => 'Vergoeding uit de basisverzekering, verwijzing en tarieven moeten meteen duidelijk zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil een eerste consult online laten boeken',
            'b' => 'Intake, consult en vervolgafspraak zelf inplannen, op locatie of online.',
        ],
        'klantenportaal' => [
            't' => 'Cliënten bellen om te verzetten of hun voedingsplan',
            'b' => 'Afspraken verzetten, plan en facturen in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Herinneringen en verwijzingen verwerk ik met de hand',
            'b' => 'Afspraakherinneringen, verwijzingen en declaraties zonder handwerk, waar het systeem het toelaat.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens het consult',
            'b' => 'Vergoeding, verwijzing en afspraken opgevangen; voedingsvragen naar jou.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop cliënten je vinden op "diëtist" plus jouw plaats, meteen zien wat vergoed wordt en of een verwijzing nodig is, en een afspraak maken.',
            'voorbeelden' => [
                'Gevonden op "diëtist" plus je plaats',
                'Vergoeding en verwijzing helder uitgelegd',
                'Afspraak maken in één tik',
            ],
            'resultaat' => 'Meer nieuwe cliënten die al weten hoe de vergoeding werkt.',
            'cta' => 'Bekijk websites voor diëtisten',
        ],
        'webshop' => [
            'voor' => 'Laat cliënten een eerste consult of vervolgafspraak online inplannen, op locatie of via beeldbellen, met het intakeformulier vooraf.',
            'voorbeelden' => [
                'Eerste consult boeken op een vrij moment',
                'Intake vooraf ingevuld',
                'Keuze tussen praktijk en online',
            ],
            'resultaat' => 'Volle agenda zonder telefoontjes tussen consulten door.',
            'cta' => 'Bekijk online afspraken voor diëtisten',
            'titel' => 'Online afspraken',
        ],
        'klantenportaal' => [
            'voor' => 'Geef cliënten een eigen omgeving waarin ze afspraken verzetten, hun voedingsplan en afspraken terugvinden en facturen voor de verzekeraar downloaden.',
            'voorbeelden' => [
                'Afspraak verzetten binnen jouw regels',
                'Plan en documenten terugvinden',
                'Factuur voor de verzekeraar zelf downloaden',
            ],
            'resultaat' => 'Minder verzet- en factuurtelefoontjes.',
            'cta' => 'Bekijk cliëntenportalen voor diëtisten',
            'titel' => 'Cliëntenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat afspraakherinneringen, verwijzingen en declaraties automatisch lopen tussen website, agenda en je praktijksoftware, voor zover die een koppeling toelaat.',
            'voorbeelden' => [
                'Herinnering een dag vooraf, vanzelf',
                'Verwijzing van de huisarts in het dossier',
                'Declaratie zonder overtypen',
            ],
            'resultaat' => 'Minder no-show en minder avondadministratie.',
            'cta' => 'Bekijk automatisering voor diëtisten',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen tijdens consulten: vergoeding, verwijzing, praktijktijden en afspraken uit jouw gegevens. Over voeding of een behandelplan zegt hij niets; dat is aan jou.',
            'voorbeelden' => [
                'Afspraak of terugbelverzoek genoteerd',
                'Vergoedingsregels uit jouw gegevens, geen belofte',
                'Voedingsvragen altijd naar de diëtist',
            ],
            'resultaat' => 'Bereikbaar tijdens het consult zonder het te onderbreken.',
            'cta' => 'Bekijk AI-telefonie voor diëtisten',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een praktijk op twee locaties:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De praktijk begint met een website die vergoeding en verwijzing helder uitlegt en gevonden wordt op "diëtist" plus de plaats.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online afspraken',
                'b' => 'Daarna boeken cliënten hun eerste consult zelf, met het intakeformulier vooraf.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de herinneringen',
                'b' => 'Vervolgens gaan afspraakherinneringen en declaraties vanzelf, gekoppeld aan de praktijksoftware waar dat kan.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens consulten: "wordt het vergoed", verwijzingen en afspraken, met voedingsvragen naar de diëtist.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd tijdens het consult',
        'tekst' => 'Een AI-telefoonassistent neemt op als jij in consult zit. Hij legt uit hoe vergoeding en verwijzing bij jou werken, noemt tarieven en praktijktijden, plant of verzet een afspraak en legt een terugbelverzoek vast. Over voeding of een behandelplan doet hij geen uitspraak.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je tarieven, praktijktijden en vergoedingsregels, en de assistent neemt op tijdens consulten. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
