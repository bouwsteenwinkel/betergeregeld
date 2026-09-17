<?php

// Groeidiamant-pagina, branche-laag aannemer. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/aannemer_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je aannemersbedrijf',
        'bedrijven' => 'aannemers',
    ],
    'seo_titel' => 'Digitale groei voor aannemers: website, offertes, portaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je aannemersbedrijf stap voor stap groeit: gevonden worden op verbouwingen in je regio, offertes online aanvragen, opdrachtgevers die hun project volgen, minder overtypen en een telefoon die wordt opgenomen als jij op de steiger staat.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je aannemersbedrijf stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je aannemersbedrijf nu nodig heeft: gevonden worden op verbouwen en aanbouwen in je regio, offertes die online binnenkomen, opdrachtgevers die zelf de planning zien, of een telefoon die wordt opgenomen terwijl jij op de steiger staat.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Wie "aannemer" plus onze plaats zoekt, vindt ons niet',
            'b' => 'Soorten werk, foto\'s van projecten en een offerteknop moeten meteen in beeld zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil offerteaanvragen compleet binnenkrijgen',
            'b' => 'Soort klus, foto\'s, adres en planning in één aanvraag, zonder terugbellen voor de basisvragen.',
        ],
        'klantenportaal' => [
            't' => 'Opdrachtgevers bellen om de planning en het meerwerk',
            'b' => 'Planning, tekeningen, meerwerk-akkoord en facturen in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Aanvragen typ ik over in mijn offerteprogramma',
            'b' => 'Van aanvraag naar offerte, planning en factuur zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat terwijl ik op de steiger sta',
            'b' => 'Aanvragen, leveranciers en "wanneer komt de stukadoor" opgevangen; jij werkt door.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop opdrachtgevers je vinden op "aannemer" of "verbouwing" plus jouw regio, je soorten werk en afgeronde projecten zien en een offerte aanvragen.',
            'voorbeelden' => [
                'Gevonden op "aannemer" en "verbouwen" plus je regio',
                'Projectfoto\'s die vertrouwen geven',
                'Offerteknop met foto-upload',
            ],
            'resultaat' => 'Meer serieuze offerteaanvragen uit de eigen regio.',
            'cta' => 'Bekijk websites voor aannemers',
        ],
        'webshop' => [
            'voor' => 'Laat opdrachtgevers een offerte of een adviesgesprek online aanvragen met soort klus, foto\'s, adres en gewenste planning, zodat je niet terugbelt voor de basisvragen.',
            'voorbeelden' => [
                'Aanvraag met foto\'s en maten',
                'Keuze uit soorten werk die jij doet',
                'Adviesgesprek op locatie inplannen',
            ],
            'resultaat' => 'Complete aanvragen, minder heen-en-weer.',
            'cta' => 'Bekijk online offerteaanvragen voor aannemers',
            'titel' => 'Online offertes en aanvragen',
        ],
        'klantenportaal' => [
            'voor' => 'Geef opdrachtgevers een eigen omgeving met planning, tekeningen, foto\'s van de voortgang, akkoord op meerwerk en facturen.',
            'voorbeelden' => [
                'Planning en wie wanneer komt',
                'Meerwerk akkoord geven met foto erbij',
                'Termijnfacturen op één plek',
            ],
            'resultaat' => 'Minder "wanneer komt de stukadoor"-telefoontjes en sneller akkoord.',
            'cta' => 'Bekijk projectportalen voor aannemers',
            'titel' => 'Projectportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat aanvragen, offertes, planning, termijnfacturen en herinneringen automatisch doorlopen tussen website, offerteprogramma en boekhouding, voor zover die een koppeling toelaten.',
            'voorbeelden' => [
                'Aanvraag meteen in je offerteprogramma',
                'Termijnfactuur bij een mijlpaal',
                'Herinnering aan opdrachtgever voor keuzes (tegels, kleuren)',
            ],
            'resultaat' => 'Minder overtypen en minder vergeten termijnen.',
            'cta' => 'Bekijk automatisering voor aannemers',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen als je op de steiger staat: nieuwe aanvragen uitvragen, leveranciers doorverbinden, opdrachtgevers vertellen wat je hebt doorgegeven en terugbelverzoeken vastleggen.',
            'voorbeelden' => [
                'Nieuwe aanvraag met adres en soort werk genoteerd',
                'Leverancier of onderaannemer doorverbonden bij spoed',
                'Geen prijs of datum beloofd: dat doe jij',
            ],
            'resultaat' => 'Bereikbaar op de bouw; aanvragen komen compleet binnen.',
            'cta' => 'Bekijk AI-telefonie voor aannemers',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een aannemer met zes man personeel:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'Het bedrijf begint met een website die gevonden wordt op "aannemer" en "verbouwing" plus de regio, met projectfoto\'s en een offerteknop met foto-upload.',
            ],
            [
                'fase' => 'klantenportaal',
                't' => 'Dan het projectportaal',
                'b' => 'Daarna zien opdrachtgevers in een portaal de planning, tekeningen en foto\'s van de voortgang en geven ze daar akkoord op meerwerk.',
            ],
            [
                'fase' => 'automatisering',
                't' => 'Dan de koppeling',
                'b' => 'Vervolgens gaan aanvragen rechtstreeks het offerteprogramma in en gaan termijnfacturen bij een mijlpaal vanzelf de deur uit.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op als iedereen op de bouw staat: nieuwe aanvragen, leveranciers en "wanneer komt de stukadoor".',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd als je net op de steiger staat',
        'tekst' => 'Een AI-telefoonassistent neemt op als jij en je mannen op de bouw staan. Hij vraagt een nieuwe aanvraag uit (adres, soort werk, hoe gevonden), verbindt een leverancier door bij spoed en vertelt een opdrachtgever wat jij hebt doorgegeven. Prijzen of data belooft hij niet.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je werkgebied en soorten werk, en de assistent neemt op als je op de bouw staat. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
