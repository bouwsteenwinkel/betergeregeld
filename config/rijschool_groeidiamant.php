<?php

// Groeidiamant-pagina, branche-laag rijschool. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/rijschool_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je rijschool',
        'bedrijven' => 'rijscholen',
    ],
    'seo_titel' => 'Digitale groei voor rijscholen: website, proefles online, leerlingenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je rijschool stap voor stap groeit: gevonden worden op rijles in je plaats, proeflessen en pakketten online, leerlingen die zelf plannen en hun voortgang zien, minder administratie en een telefoon die wordt opgenomen tijdens de les.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je rijschool stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je rijschool nu nodig heeft: gevonden worden op "rijles" plus je plaats, proeflessen en pakketten die online worden geboekt, leerlingen die zelf hun lessen plannen, of een telefoon die wordt opgenomen terwijl jij naast een leerling zit.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Wie "rijschool" plus onze plaats zoekt, vindt ons niet',
            'b' => 'Pakketten, prijzen, lesauto\'s en wachttijd moeten meteen duidelijk zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil proeflessen en pakketten online laten boeken en betalen',
            'b' => 'Proefles of pakket kiezen, gegevens invullen, vooraf betaald.',
        ],
        'klantenportaal' => [
            't' => 'Leerlingen en ouders bellen om lessen en voortgang',
            'b' => 'Lessen plannen, verzetten, voortgang en pakketsaldo in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Herinneringen, examens en pakketadministratie doe ik met de hand',
            'b' => 'Lesherinneringen, examenaanvragen en facturen zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens de les',
            'b' => 'Prijzen, wachttijd en proeflessen opgevangen; jij houdt je ogen op de weg.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop leerlingen (en hun ouders) je vinden op "rijschool" of "rijles" plus jouw plaats, pakketten en prijzen zien, en een proefles boeken.',
            'voorbeelden' => [
                'Gevonden op "rijles" plus je plaats',
                'Pakketten, prijzen en lesauto\'s duidelijk',
                'Proefles boeken in één tik',
            ],
            'resultaat' => 'Meer nieuwe leerlingen uit de eigen plaats.',
            'cta' => 'Bekijk websites voor rijscholen',
        ],
        'webshop' => [
            'voor' => 'Laat leerlingen een proefles of lespakket online boeken en betalen, met hun gegevens en beschikbaarheid erbij.',
            'voorbeelden' => [
                'Proefles of pakket boeken en betalen',
                'Beschikbaarheid van de leerling vooraf',
                'Wachttijd eerlijk vermeld',
            ],
            'resultaat' => 'Meer boekingen vooraf, minder gedoe met betalen.',
            'cta' => 'Bekijk online boeken voor rijscholen',
            'titel' => 'Online boeken',
        ],
        'klantenportaal' => [
            'voor' => 'Geef leerlingen een eigen omgeving waarin ze lessen plannen en verzetten, hun voortgang per onderdeel zien, het pakketsaldo bekijken en ouders kunnen meekijken.',
            'voorbeelden' => [
                'Lessen plannen en verzetten binnen jouw regels',
                'Voortgang per onderdeel',
                'Pakketsaldo en facturen, ook voor ouders',
            ],
            'resultaat' => 'Minder plan-telefoontjes en minder "hoe gaat het".',
            'cta' => 'Bekijk leerlingenportalen voor rijscholen',
            'titel' => 'Leerlingenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat lesherinneringen, examenaanvragen, pakketsaldo en facturen automatisch lopen tussen website, planning en administratie, voor zover je planpakket dat toelaat.',
            'voorbeelden' => [
                'Herinnering een dag voor de les',
                'Examen aangevraagd zodra de leerling er klaar voor is',
                'Pakketsaldo loopt vanzelf af',
            ],
            'resultaat' => 'Minder administratie na de laatste les.',
            'cta' => 'Bekijk automatisering voor rijscholen',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen tijdens de les: pakketprijzen, wachttijd, hoe een proefles loopt en afzegregels uit jouw gegevens. Ouders die willen weten hoe het gaat, krijgen een terugbelverzoek bij de instructeur.',
            'voorbeelden' => [
                'Proefles geboekt of genoteerd',
                'Prijzen en wachttijd uit jouw gegevens',
                'Vragen over een leerling als terugbelverzoek',
            ],
            'resultaat' => 'Bereikbaar tijdens de les zonder afgeleid te raken.',
            'cta' => 'Bekijk AI-telefonie voor rijscholen',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een rijschool met drie instructeurs:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De rijschool begint met een website die gevonden wordt op "rijles" plus de plaats, met pakketten, prijzen en een proefles-knop.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online boeken',
                'b' => 'Daarna boeken en betalen leerlingen hun proefles en pakket zelf.',
            ],
            [
                'fase' => 'klantenportaal',
                't' => 'Dan het leerlingenportaal',
                'b' => 'Vervolgens plannen leerlingen zelf hun lessen en zien zij en hun ouders de voortgang.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens de les: prijzen, wachttijd en proeflessen, met vragen over een leerling als terugbelverzoek.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd terwijl je naast een leerling zit',
        'tekst' => 'Een AI-telefoonassistent neemt op tijdens de les. Hij noemt pakketten en prijzen, de wachttijd voor nieuwe leerlingen en hoe een proefles loopt, boekt of noteert een proefles en legt vragen van ouders als terugbelverzoek bij de instructeur.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je pakketten, prijzen en afzegregels, en de assistent neemt op tijdens de les. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
