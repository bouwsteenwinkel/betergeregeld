<?php

// Groeidiamant-pagina, branche-laag autogarage. De assistent geeft geen technisch oordeel
// ("kan ik nog rijden met piepende remmen?"): dat is de monteur. Zie autogarage_telefonie.php.
return [
    'woorden' => ['bedrijf' => 'je garage', 'bedrijven' => 'garages'],

    'seo_titel'        => 'Digitale groei voor garages: website, online afspraken & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je garage stap voor stap groeit: gevonden worden op APK en onderhoud, online afspraken, klanten die hun auto volgen, minder overtypen en een telefoon die wordt opgenomen als jij onder een auto ligt.',

    'hero' => [
        'titel' => 'Van website tot AI: laat je garage stap voor stap digitaal groeien',
        'lead'  => 'Begin met wat je garage nu nodig heeft: gevonden worden op APK en onderhoud, online afspraken, klanten die zelf zien of hun auto klaar is, of een telefoon die wordt opgenomen terwijl jij onder de brug staat. Elke stap sluit aan op de vorige.',
    ],

    'situaties' => [
        'website'        => ['t' => 'Wie "APK" plus onze plaats zoekt, vindt de concurrent', 'b' => 'Prijzen, merken en een afspraakknop moeten meteen in beeld zijn.'],
        'webshop'        => ['t' => 'Ik wil APK en onderhoud online laten inplannen', 'b' => 'Kenteken, klacht en een tijdslot, zonder telefoontje.'],
        'klantenportaal' => ['t' => 'Klanten bellen om te vragen of de auto al klaar is', 'b' => 'Status, werkorder, foto\'s en factuur in een eigen omgeving.'],
        'automatisering' => ['t' => 'Afspraken en werkorders typ ik twee keer over', 'b' => 'Van afspraak naar planning en werkorder zonder handwerk, met APK-herinneringen erbij.'],
        'ai'             => ['t' => 'De telefoon gaat terwijl ik onder een auto lig', 'b' => 'Prijzen, status en afspraken opgevangen; technische vragen naar de monteur.'],
    ],

    'fasen' => [
        'website' => [
            'voor'        => 'Een website waarop klanten je vinden op "APK" of "garage" plus jouw plaats, zien welke merken en werkzaamheden je doet en direct een afspraak maken.',
            'voorbeelden' => ['Gevonden op "APK" en "onderhoud" plus je plaats', 'Prijzen van APK en beurten meteen zichtbaar', 'Afspraakknop met kenteken'],
            'resultaat'   => 'Meer APK\'s en beurten uit de eigen regio.',
            'cta'         => 'Bekijk websites voor garages',
        ],
        'webshop' => [
            'titel'       => 'Online afspraken en diensten',
            'voor'        => 'Laat klanten APK, onderhoud en banden online inplannen met kenteken en klacht, en waar passend vooraf betalen.',
            'voorbeelden' => ['APK of beurt inplannen op een vrij tijdslot', 'Kenteken erbij, dus het juiste model staat al klaar', 'Bandenwissel of ruitreparatie als losse dienst'],
            'resultaat'   => 'Volle planning zonder telefoontjes ertussendoor.',
            'cta'         => 'Bekijk online afspraken voor garages',
        ],
        'klantenportaal' => [
            'voor'        => 'Geef klanten een eigen omgeving waarin ze zien of de auto klaar is, de werkorder en foto\'s bekijken, akkoord geven op meerwerk en facturen terugvinden.',
            'voorbeelden' => ['"Is mijn auto klaar?" zien ze zelf', 'Akkoord op meerwerk met een foto erbij', 'Facturen en APK-historie per kenteken'],
            'resultaat'   => 'Minder statusvragen aan de balie en sneller akkoord op meerwerk.',
            'cta'         => 'Bekijk klantenportalen voor garages',
        ],
        'automatisering' => [
            'voor'        => 'Laat afspraken, werkorders, APK-herinneringen en facturen automatisch doorlopen tussen website, planning en garagesoftware, voor zover dat pakket een koppeling toelaat.',
            'voorbeelden' => ['Online afspraak meteen in de planning', 'APK-herinnering een maand vooraf, vanzelf', 'Werkorder naar factuur zonder overtypen'],
            'resultaat'   => 'Minder overtypen en geen vergeten APK-herinnering.',
            'cta'         => 'Bekijk automatisering voor garages',
        ],
        'ai' => [
            'voor'        => 'Laat de telefoon opnemen als iedereen in de werkplaats staat: prijzen, openingstijden, "is mijn auto klaar" en afspraken. Technische vragen gaan naar de monteur.',
            'voorbeelden' => ['Kenteken en klacht genoteerd voor de afspraak', 'Status van de auto uit jouw gegevens', 'Geen technisch oordeel: dat geeft de monteur'],
            'resultaat'   => 'Bereikbaar tijdens het sleutelen; afspraken komen compleet binnen.',
            'cta'         => 'Bekijk AI-telefonie voor garages',
        ],
    ],

    'praktijk' => [
        'intro'   => 'Zo kan het lopen bij een garage met vier bruggen:',
        'stappen' => [
            ['fase' => 'website',        't' => 'Eerst de website',      'b' => 'De garage begint met een website die gevonden wordt op "APK" en "onderhoud" plus de plaats, met prijzen en een afspraakknop met kenteken.'],
            ['fase' => 'webshop',        't' => 'Dan online inplannen',   'b' => 'Daarna plannen klanten APK en beurten zelf in op een vrij tijdslot; de klacht en het kenteken komen erbij.'],
            ['fase' => 'automatisering', 't' => 'Dan de koppeling',      'b' => 'Vervolgens gaan afspraken rechtstreeks de planning en de werkorder in, en gaan APK-herinneringen vanzelf de deur uit.'],
            ['fase' => 'ai',             't' => 'Later AI-telefonie',    'b' => 'Later neemt de assistent op als iedereen onder een auto staat: "is mijn auto klaar", prijzen en afspraken. Piepende remmen? Die gaan naar de monteur.'],
        ],
    ],

    'telefonie' => [
        'titel'  => 'De telefoon gaat altijd als je net onder een auto ligt',
        'tekst'  => 'Een AI-telefoonassistent neemt op als de werkplaats vol staat. Hij weet je prijzen en openingstijden, ziet aan het kenteken welke auto het is, zegt of hij klaar is en plant een afspraak in. Of iemand nog veilig kan rijden, zegt hij niet; dat is aan de monteur.',
        'vragen' => ['Is mijn auto al klaar? Kenteken 12-KLM-3', 'Wat kost een APK bij jullie?', 'Kan ik morgen komen voor een piepend geluid bij het remmen?', 'Doen jullie ook banden wisselen?'],
        'cta'    => 'Bekijk AI-telefonie voor garages',
    ],

    'faq' => [
        ['q' => 'Kan ik ook alleen AI-telefonie gebruiken?', 'a' => 'Ja. Een nummer, je prijzen en openingstijden, en de assistent neemt op tijdens het sleutelen. Koppel je later je planning, dan kan hij ook zien of een auto klaar is en afspraken inplannen.'],
        ['q' => 'Kunnen jullie koppelen met mijn garagesoftware?', 'a' => 'Dat hangt van het pakket af. Sommige garagepakketten hebben een koppeling voor afspraken en kentekens, andere niet. We bekijken dat vooraf en zeggen eerlijk wat kan; wat niet kan, gaat via een export of blijft handwerk.'],
    ],
];
