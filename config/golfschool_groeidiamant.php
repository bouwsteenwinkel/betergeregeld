<?php

// Groeidiamant-pagina, branche-laag golfschool. Basis: config/groeidiamant_basis.php;
// samengevoegd door App\Support\GroeidiamantConfig. Voorbeeldvragen voor de AI-sectie
// komen uit config/golfschool_telefonie.php (gesprekken).
return [
    'woorden' => [
        'bedrijf' => 'je golfschool',
        'bedrijven' => 'golfscholen',
    ],
    'seo_titel' => 'Digitale groei voor golfscholen: website, lessen online boeken, leerlingenportaal & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je golfschool stap voor stap groeit: gevonden worden op golfles en handicap 54 in je regio, lessen en pakketten online boeken, leerlingen die zelf hun voortgang zien, minder administratie en een telefoon die wordt opgenomen tijdens de les.',
    'hero' => [
        'titel' => 'Van website tot AI: laat je golfschool stap voor stap digitaal groeien',
        'lead' => 'Begin met wat je golfschool nu nodig heeft: gevonden worden op "golfles" en "handicap 54" in je regio, lessen en pakketten die online worden geboekt, leerlingen die hun voortgang zelf zien, of een telefoon die wordt opgenomen terwijl jij op de range staat.',
    ],
    'situaties' => [
        'website' => [
            't' => 'Beginners weten niet wat ze nodig hebben en bellen daarvoor',
            'b' => 'Lessen, pakketten, het traject naar handicap 54 en prijzen moeten meteen duidelijk zijn.',
        ],
        'webshop' => [
            't' => 'Ik wil lessen en pakketten online laten boeken en betalen',
            'b' => 'Proefles, pakket of clinic kiezen, tijdslot prikken, vooraf betaald.',
        ],
        'klantenportaal' => [
            't' => 'Leerlingen bellen om te verzetten en om hun voortgang',
            'b' => 'Lessen verzetten, voortgang naar handicap 54 en pakketten in een eigen omgeving.',
        ],
        'automatisering' => [
            't' => 'Herinneringen en pakketadministratie doe ik met de hand',
            'b' => 'Lesherinneringen, pakketsaldo en facturen zonder handwerk.',
        ],
        'ai' => [
            't' => 'De telefoon gaat tijdens de les',
            'b' => 'Prijzen, "wat heb ik nodig" en boekingen opgevangen; jij geeft les.',
        ],
    ],
    'fasen' => [
        'website' => [
            'voor' => 'Een website waarop nieuwe golfers je vinden op "golfles" plus jouw regio, meteen zien welke lessen en pakketten er zijn en wat het traject naar handicap 54 kost, en een proefles boeken.',
            'voorbeelden' => [
                'Gevonden op "golfles" en "handicap 54" plus je regio',
                'Pakketten en prijzen duidelijk',
                'Proefles boeken in één tik',
            ],
            'resultaat' => 'Meer beginners die weten wat ze kunnen verwachten.',
            'cta' => 'Bekijk websites voor golfscholen',
        ],
        'webshop' => [
            'voor' => 'Laat leerlingen een proefles, lespakket of clinic online boeken en betalen, met een tijdslot op de range of baan waar je lesgeeft.',
            'voorbeelden' => [
                'Proefles of pakket boeken op een vrij tijdslot',
                'Vooraf betaald, geen no-show',
                'Bedrijfsclinic aanvragen met aantal deelnemers',
            ],
            'resultaat' => 'Meer boekingen vooraf en minder gedoe met betalen.',
            'cta' => 'Bekijk online boeken voor golfscholen',
            'titel' => 'Online boeken',
        ],
        'klantenportaal' => [
            'voor' => 'Geef leerlingen een eigen omgeving waarin ze lessen verzetten, hun voortgang naar handicap 54 zien, hun pakketsaldo bekijken en facturen terugvinden.',
            'voorbeelden' => [
                'Les verzetten binnen jouw afzegregels',
                'Voortgang en behaalde onderdelen',
                'Pakketsaldo en facturen',
            ],
            'resultaat' => 'Minder verzet-telefoontjes en meer betrokken leerlingen.',
            'cta' => 'Bekijk leerlingenportalen voor golfscholen',
            'titel' => 'Leerlingenportaal',
        ],
        'automatisering' => [
            'voor' => 'Laat lesherinneringen, pakketsaldo, facturen en de planning per pro automatisch lopen tussen website, agenda en administratie.',
            'voorbeelden' => [
                'Herinnering een dag voor de les',
                'Pakketsaldo loopt vanzelf af',
                'Factuur voor clinics automatisch',
            ],
            'resultaat' => 'Minder administratie na de laatste les van de dag.',
            'cta' => 'Bekijk automatisering voor golfscholen',
        ],
        'ai' => [
            'voor' => 'Laat de telefoon opnemen als je op de range staat: prijzen, wat een beginner nodig heeft, afzegregels en boekingen uit jouw gegevens. Voor een clinic of maatwerk legt hij een terugbelverzoek vast.',
            'voorbeelden' => [
                'Proefles of les geboekt of genoteerd',
                'Wat mee te nemen en waar de les is',
                'Bedrijfsclinic als terugbelverzoek met aantal deelnemers',
            ],
            'resultaat' => 'Bereikbaar tijdens de les zonder de leerling te laten wachten.',
            'cta' => 'Bekijk AI-telefonie voor golfscholen',
        ],
    ],
    'praktijk' => [
        'intro' => 'Zo kan het lopen bij een golfschool met twee pro\'s:',
        'stappen' => [
            [
                'fase' => 'website',
                't' => 'Eerst de website',
                'b' => 'De golfschool begint met een website die gevonden wordt op "golfles" in de regio, met pakketten, prijzen en een proefles-knop.',
            ],
            [
                'fase' => 'webshop',
                't' => 'Dan online boeken',
                'b' => 'Daarna boeken en betalen leerlingen hun proefles en pakketten zelf op een vrij tijdslot.',
            ],
            [
                'fase' => 'klantenportaal',
                't' => 'Dan het leerlingenportaal',
                'b' => 'Vervolgens verzetten leerlingen zelf hun lessen en zien ze hun voortgang naar handicap 54.',
            ],
            [
                'fase' => 'ai',
                't' => 'Later AI-telefonie',
                'b' => 'Later neemt de assistent op tijdens de les: prijzen, "wat heb ik nodig om te beginnen" en boekingen.',
            ],
        ],
    ],
    'telefonie' => [
        'titel' => 'De telefoon gaat altijd als je net op de range staat',
        'tekst' => 'Een AI-telefoonassistent neemt op tijdens de les. Hij weet je lessen, pakketten en prijzen, legt uit wat een beginner nodig heeft, boekt een proefles of noteert een terugbelverzoek voor een clinic. Wat hij niet weet, zegt hij eerlijk.',
    ],
    'faq' => [
        [
            'q' => 'Kan ik ook alleen AI-telefonie gebruiken?',
            'a' => 'Ja. Een nummer, je lessen, prijzen en afzegregels, en de assistent neemt op tijdens de les. Een website of portaal van ons is daarvoor niet nodig.',
        ],
    ],
];
