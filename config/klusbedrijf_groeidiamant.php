<?php

// Groeidiamant-pagina, branche-laag klusbedrijf. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/klusbedrijf_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je klusbedrijf',
        'bedrijven' => 'klusbedrijven',
    ],
    'seo_titel' => 'Digitale groei voor klusbedrijven: website, aanvragen, klantenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je klusbedrijf stap voor stap groeit: gevonden worden op klussen in je regio, aanvragen met foto\'s online, klanten die de planning zien, minder overtypen en een telefoon die wordt opgenomen als jij alleen op een klus staat.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je klusbedrijf stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je klusbedrijf nu nodig heeft: gevonden worden op de klussen die je doet in je regio, aanvragen met foto\'s die online binnenkomen, klanten die zelf de planning zien, of een telefoon die wordt opgenomen terwijl jij alleen op een klus staat.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Wie "klusbedrijf" plus onze plaats zoekt, vindt ons niet',
            'b' => 'Wat je doet, wat je niet doet, uurtarief en foto\'s moeten meteen in beeld zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil aanvragen met foto\'s binnenkrijgen in plaats van vage telefoontjes',
            'b' => 'Soort klus, foto\'s en adres in één aanvraag; jij belt terug met een prijs.',
        ],
        'klantenportaal' => [
            't' => 'Klanten bellen wanneer ik kom en wat het wordt',
            'b' => 'Planning, afspraken, meerwerk en facturen in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Aanvragen, planning en facturen doe ik \'s avonds in de bus',
            'b' => 'Van aanvraag naar planning en factuur zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat terwijl ik alleen op een klus sta',
            'b' => 'Uurtarief, werkgebied en aanvragen opgevangen; klussen die je niet doet netjes afgewezen.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop klanten je vinden op "klusbedrijf" of "klusjesman" plus jouw plaats, zien wat je wel en niet doet met uurtarief en foto\'s, en een aanvraag doen.',
            'voorbeelden' => [
                'Gevonden op "klusbedrijf" plus je plaats',
                'Wat je doet en niet doet, met uurtarief',
                'Aanvraag met foto\'s in één tik',
            ],
            'resultaat' => 'Meer passende aanvragen uit de eigen buurt.',
            'cta' => 'Bekijk websites voor klusbedrijven',
        ],
        'webshop' => [
            'voor' => 'Laat klanten een klus online aanvragen met soort werk, foto\'s, adres en gewenste periode, zodat je terugbelt met een prijs in plaats van eerst te gaan kijken.',
            'voorbeelden' => [
                'Aanvraag met foto\'s en maten',
                'Alleen de klussen die jij doet',
                'Gewenste periode erbij, dus meteen inplannen',
            ],
            'resultaat' => 'Minder kijkafspraken, meer prijs in één keer.',
            'cta' => 'Bekijk online aanvragen voor klusbedrijven',
            'titel' => 'Online aanvragen',
        ],
        'klantenportaal' => [
            'voor' => 'Geef klanten een eigen omgeving met de afspraak, wat er gedaan wordt, akkoord op meerwerk en de factuur.',
            'voorbeelden' => [
                'Wanneer je komt en wat je doet',
                'Meerwerk akkoord met een foto erbij',
                'Factuur meteen na de klus',
            ],
            'resultaat' => 'Minder "hoe laat kom je"-telefoontjes.',
            'cta' => 'Bekijk klantenportalen voor klusbedrijven',
        ],
        'automatisering' => [
            'voor' => 'Laat aanvragen, planning, facturen en herinneringen automatisch lopen tussen website, agenda en boekhouding, voor zover die een koppeling toelaten.',
            'voorbeelden' => [
                'Aanvraag meteen in je agenda-voorstel',
                'Factuur na de klus zonder overtypen',
                'Herinnering aan de klant een dag vooraf',
            ],
            'resultaat' => 'Minder avondadministratie in de bus.',
            'cta' => 'Bekijk automatisering voor klusbedrijven',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen als je alleen op een klus staat: uurtarief en voorrijkosten, werkgebied, wanneer je bereikbaar bent, en een nieuwe aanvraag met adres genoteerd. Klussen die je niet doet wijst hij netjes af.',
            'voorbeelden' => [
                'Nieuwe aanvraag met adres en soort klus genoteerd',
                'Uurtarief en werkgebied uit jouw gegevens',
                'Wat je niet doet, netjes afgewezen',
            ],
            'resultaat' => 'Bereikbaar op de klus zonder de boor neer te leggen.',
            'cta' => 'Bekijk AI-telefonie voor klusbedrijven',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een klusbedrijf van één man en een bus:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'Het klusbedrijf begint met een website die gevonden wordt op "klusbedrijf" plus de plaats, met wat hij wel en niet doet en het uurtarief.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online aanvragen',
                'b' => 'Daarna komen aanvragen binnen met foto\'s en adres, zodat hij terugbelt met een prijs in plaats van eerst te gaan kijken.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de facturen',
                'b' => 'Vervolgens gaan factuur en herinnering vanzelf, gekoppeld aan de boekhouding.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op als hij alleen op een klus staat: uurtarief, werkgebied en nieuwe aanvragen, met klussen die hij niet doet netjes afgewezen.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'Je bent alleen, je handen zijn vol en de telefoon gaat',
        'tekst' => 'Een AI-telefoonassistent neemt op als jij op een klus staat. Hij noemt je uurtarief en voorrijkosten, zegt of een klus in je werkgebied valt en of jij dat werk doet, noteert een nieuwe aanvraag met adres en legt een terugbelverzoek vast. Een prijs voor de klus geeft hij niet; dat doe jij.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je uurtarief, werkgebied en wat je wel en niet doet, en de assistent neemt op tijdens de klus. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
