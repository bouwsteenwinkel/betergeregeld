<?php

// Groeidiamant-pagina, branche-laag yogastudio. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/yogastudio_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je yogastudio',
        'bedrijven' => 'yogastudio\'s',
    ],
    'seo_titel' => 'Digitale groei voor yogastudio\'s: website, lessen online reserveren, ledenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je yogastudio stap voor stap groeit: gevonden worden op yoga in je plaats, proefles en abonnement online, deelnemers die zelf reserveren en afmelden, minder administratie en een telefoon die wordt opgenomen tijdens de les.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je yogastudio stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je studio nu nodig heeft: gevonden worden op "yoga" plus je plaats, proeflessen en abonnementen die online worden afgesloten, deelnemers die zelf reserveren en afmelden, of een telefoon die wordt opgenomen terwijl jij lesgeeft.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Nieuwe mensen weten niet welke les bij ze past',
            'b' => 'Lesrooster, lesvormen, niveaus en prijzen moeten meteen duidelijk zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil proeflessen en abonnementen online laten afsluiten',
            'b' => 'Proefles, rittenkaart of abonnement kiezen en betalen, plek reserveren.',
        ],
        'klantenportaal' => [
            't' => 'Deelnemers bellen om te reserveren of af te melden',
            'b' => 'Reserveren, afmelden, abonnement en facturen in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Herinneringen, wachtlijsten en incasso doe ik met de hand',
            'b' => 'Lesherinneringen, wachtlijst en abonnementsadministratie zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens de les',
            'b' => '"Welke les is geschikt voor mij", rooster en proeflessen opgevangen; jij geeft les.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop mensen je vinden op "yoga" plus jouw plaats, het lesrooster, lesvormen en niveaus zien, en een proefles reserveren.',
            'voorbeelden' => [
                'Gevonden op "yoga" plus je plaats',
                'Rooster, lesvormen en niveaus duidelijk',
                'Proefles reserveren in één tik',
            ],
            'resultaat' => 'Meer nieuwe deelnemers die weten welke les bij ze past.',
            'cta' => 'Bekijk websites voor yogastudio\'s',
        ],
        'webshop' => [
            'voor' => 'Laat deelnemers een proefles, rittenkaart of abonnement online afsluiten en betalen, en meteen een plek in een les reserveren.',
            'voorbeelden' => [
                'Proefles of abonnement kiezen en betalen',
                'Plek reserveren met max. aantal per les',
                'Wachtlijst als de les vol is',
            ],
            'resultaat' => 'Meer abonnementen vooraf en volle lessen zonder overboeking.',
            'cta' => 'Bekijk online reserveren voor yogastudio\'s',
            'titel' => 'Online reserveren',
        ],
        'klantenportaal' => [
            'voor' => 'Geef deelnemers een eigen omgeving waarin ze lessen reserveren en afmelden, hun abonnement of rittenkaart zien en facturen terugvinden.',
            'voorbeelden' => [
                'Reserveren en afmelden binnen jouw regels',
                'Abonnement en resterende ritten',
                'Facturen terugvinden',
            ],
            'resultaat' => 'Minder reserveer- en afmeldtelefoontjes.',
            'cta' => 'Bekijk ledenportalen voor yogastudio\'s',
            'titel' => 'Ledenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat lesherinneringen, wachtlijsten, abonnementsverlenging en incasso automatisch lopen tussen website, rooster en administratie.',
            'voorbeelden' => [
                'Herinnering voor de les, vanzelf',
                'Wachtlijst schuift door bij afmelding',
                'Abonnement verlengt en incasseert automatisch',
            ],
            'resultaat' => 'Minder administratie tussen de lessen door.',
            'cta' => 'Bekijk automatisering voor yogastudio\'s',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen tijdens de les: welke les geschikt is voor een beginner, het rooster, prijzen en hoe reserveren en afmelden werkt, uit jouw gegevens. Een proefles noteert hij meteen.',
            'voorbeelden' => [
                'Proefles genoteerd met naam en gewenste les',
                'Rooster en prijzen uit jouw gegevens',
                'Wat mee te nemen en waar de studio is',
            ],
            'resultaat' => 'Bereikbaar tijdens de les zonder de rust te verstoren.',
            'cta' => 'Bekijk AI-telefonie voor yogastudio\'s',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een studio met vier docenten:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De studio begint met een website die gevonden wordt op "yoga" plus de plaats, met rooster, lesvormen en een proefles-knop.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online reserveren',
                'b' => 'Daarna sluiten deelnemers hun proefles en abonnement online af en reserveren ze een plek per les.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de wachtlijst',
                'b' => 'Vervolgens gaan lesherinneringen, de wachtlijst en de abonnementsverlenging vanzelf.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens de les: "welke les is geschikt voor mij", het rooster en proeflessen.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd tijdens de les',
        'tekst' => 'Een AI-telefoonassistent neemt op terwijl jij lesgeeft. Hij weet het rooster, de lesvormen en niveaus, legt uit welke les geschikt is voor een beginner, noemt prijzen en abonnementen en noteert een proefles. Wat hij niet weet, zegt hij eerlijk.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je rooster, lesvormen en prijzen, en de assistent neemt op tijdens de les. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
