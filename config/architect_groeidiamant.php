<?php

// Groeidiamant-pagina, branche-laag architect. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/architect_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je bureau',
        'bedrijven' => 'architectenbureaus',
    ],
    'seo_titel' => 'Digitale groei voor architecten: website, projectportaal, automatisering & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je architectenbureau stap voor stap groeit: gevonden worden op je soort opdrachten, een eerste gesprek online aanvragen, opdrachtgevers die tekeningen en planning volgen, minder handwerk en een telefoon die wordt opgenomen tijdens de bouwvergadering.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je architectenbureau stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je bureau nu nodig heeft: gevonden worden op de opdrachten die je wilt doen, een eerste gesprek dat online wordt aangevraagd, opdrachtgevers die tekeningen en planning zelf volgen, of een telefoon die wordt opgenomen terwijl jij in de bouwvergadering zit.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Opdrachtgevers zien ons werk niet, of niet het juiste werk',
            'b' => 'Portfolio, soorten opdrachten en werkgebied moeten meteen overtuigen.',
        ],
        'webshop' => [
            't' => 'Ik wil een eerste gesprek en een haalbaarheidscheck online laten aanvragen',
            'b' => 'Soort project, locatie en budgetindicatie in één aanvraag.',
        ],
        'klantenportaal' => [
            't' => 'Opdrachtgevers en aannemers bellen om de laatste tekening',
            'b' => 'Tekeningen, planning, vergunningstatus en berichten in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Aanvragen en fases houd ik bij in losse lijstjes',
            'b' => 'Van aanvraag naar project, fasefacturen en herinneringen zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens de bouwvergadering',
            'b' => 'Aanvragen, "wat kost een architect" en detailvragen van de aannemer opgevangen; ontwerpvragen naar jou.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop opdrachtgevers je vinden op de soort opdrachten die je wilt doen (verbouwing, nieuwbouw, utiliteit) plus jouw regio, je portfolio zien en een eerste gesprek aanvragen.',
            'voorbeelden' => [
                'Gevonden op "architect" plus je soort opdracht en regio',
                'Portfolio dat het juiste werk laat zien',
                'Eerste gesprek aanvragen in één tik',
            ],
            'resultaat' => 'Meer aanvragen voor het werk dat je wilt doen.',
            'cta' => 'Bekijk websites voor architecten',
        ],
        'webshop' => [
            'voor' => 'Laat opdrachtgevers een eerste gesprek, haalbaarheidscheck of vaste-prijsdienst (bijvoorbeeld een vergunningtekening) online aanvragen met locatie, wensen en budgetindicatie.',
            'voorbeelden' => [
                'Eerste gesprek inplannen met projectgegevens',
                'Haalbaarheidscheck als vaste-prijsdienst',
                'Budgetindicatie vooraf, dus minder mismatch',
            ],
            'resultaat' => 'Aanvragen komen compleet binnen en passen bij je bureau.',
            'cta' => 'Bekijk online aanvragen voor architecten',
            'titel' => 'Online aanvragen',
        ],
        'klantenportaal' => [
            'voor' => 'Geef opdrachtgevers en de aannemer een eigen omgeving met de laatste tekeningen, planning, vergunningstatus, keuzes en berichten.',
            'voorbeelden' => [
                'Altijd de laatste tekening, geen mailversies',
                'Planning en vergunningstatus in beeld',
                'Keuzes en akkoorden vastgelegd',
            ],
            'resultaat' => 'Minder "welke versie is het" en minder telefoon van de bouw.',
            'cta' => 'Bekijk projectportalen voor architecten',
            'titel' => 'Projectportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat aanvragen, projectfases, fasefacturen en herinneringen automatisch lopen tussen website, portaal en je projectadministratie, voor zover die een koppeling toelaat.',
            'voorbeelden' => [
                'Aanvraag meteen als project aangemaakt',
                'Fasefactuur bij afronding van een fase',
                'Herinnering voor keuzes van de opdrachtgever',
            ],
            'resultaat' => 'Minder handwerk per project en minder vergeten facturen.',
            'cta' => 'Bekijk automatisering voor architecten',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen tijdens bouwvergaderingen: nieuwe aanvragen uitvragen, "wat kost een architect" beantwoorden uit jouw gegevens, detailvragen van de aannemer als terugbelverzoek bij de juiste collega leggen.',
            'voorbeelden' => [
                'Nieuwe aanvraag met soort project en locatie genoteerd',
                'Tarieven en werkwijze uit jouw gegevens',
                'Ontwerp- en vergunningsvragen altijd naar de architect',
            ],
            'resultaat' => 'Bereikbaar op de bouw en in de vergadering.',
            'cta' => 'Bekijk AI-telefonie voor architecten',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een bureau met drie architecten:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'Het bureau begint met een website die gevonden wordt op verbouwingen en nieuwbouw in de regio, met een portfolio dat het juiste werk laat zien.',
            ],
            [
                'fase' => 'klantenportaal',
                't' => 'Dan het projectportaal',
                'b' => 'Daarna vinden opdrachtgevers en aannemers de laatste tekeningen, planning en vergunningstatus in een portaal.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de fasefacturen',
                'b' => 'Vervolgens worden aanvragen als project aangemaakt en gaan fasefacturen bij afronding vanzelf de deur uit.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens bouwvergaderingen: nieuwe aanvragen, "wat kost een architect" en detailvragen van de aannemer als terugbelverzoek.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd tijdens de bouwvergadering',
        'tekst' => 'Een AI-telefoonassistent neemt op als het bureau in vergadering zit of op de bouw staat. Hij vraagt een nieuwe aanvraag uit, noemt tarieven en werkwijze uit jouw gegevens en legt detailvragen van de aannemer als terugbelverzoek bij de juiste collega. Over het ontwerp of een vergunning zegt hij niets.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je soorten opdrachten, tarieven en wie welk project doet, en de assistent neemt op. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
