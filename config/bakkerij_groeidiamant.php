<?php

// Groeidiamant-pagina, branche-laag bakkerij (vervangt de fase-uitleg uit het oude
// channel_groeidiamant_per_branche.php van 15-09-2026).
return [
    'woorden' => ['bedrijf' => 'je bakkerij', 'bedrijven' => 'bakkerijen'],

    'seo_titel'        => 'Digitale groei voor bakkerijen: website, online bestellen & AI-telefonie | Groeidiamant',
    'seo_omschrijving' => 'Ontdek hoe je bakkerij stap voor stap groeit: gevonden worden, brood en taarten online laten bestellen, vaste klanten zelf laten regelen, en een telefoon die wordt opgenomen als jij bakt.',

    'hero' => [
        'titel' => 'Van website tot AI: laat je bakkerij stap voor stap digitaal groeien',
        'lead'  => 'Begin met wat je bakkerij nu nodig heeft: gevonden worden, online bestellen, vaste klanten die zelf hun bestelling regelen, of een telefoon die wordt opgenomen terwijl jij met deeg aan je handen staat. Elke stap sluit aan op de vorige.',
    ],

    'situaties' => [
        'website'        => ['t' => 'Klanten zoeken "bakkerij" plus onze plaats en vinden ons niet', 'b' => 'Assortiment, openingstijden en bestellen moeten meteen in beeld zijn.'],
        'webshop'        => ['t' => 'Ik wil taarten en brood online laten bestellen', 'b' => 'Met afhaaldatum en -tijd, vooraf betaald, ook als de telefoon overloopt rond de feestdagen.'],
        'klantenportaal' => ['t' => 'Horeca en kantoren bellen elke avond hun vaste bestelling door', 'b' => 'Zakelijke klanten wijzigen en herhalen hun bestelling zelf en vinden hun facturen.'],
        'automatisering' => ['t' => 'Bestellingen schrijf ik over op de productielijst', 'b' => 'Van bestelling naar bevestiging, productielijst en factuur zonder overtypen.'],
        'ai'             => ['t' => 'De telefoon gaat terwijl ik in de bakkerij sta', 'b' => 'Taartbestellingen, openingstijden en allergenenvragen opgevangen; jij bakt door.'],
    ],

    'fasen' => [
        'website' => [
            'voor'        => 'Een website waarop klanten je vinden op "bakkerij" plus jouw plaats, je assortiment en openingstijden zien, en bestellen of bellen.',
            'voorbeelden' => ['Gevonden op "bakkerij" plus je plaats', 'Assortiment en openingstijden actueel', 'Taart bestellen of bellen in één tik'],
            'resultaat'   => 'Meer klanten uit de buurt die je vinden, ook op zondagavond.',
            'cta'         => 'Bekijk websites voor bakkerijen',
        ],
        'webshop' => [
            'titel'       => 'Online bestellen',
            'voor'        => 'Brood, banket en taarten online laten bestellen met een afhaaldatum en -tijd, vooraf betaald. Ook rond de feestdagen, als de telefoon overloopt.',
            'voorbeelden' => ['Taart met tekst en foto bestellen, vooraf betaald', 'Afhaalmoment kiezen binnen jouw tijden', 'Kerst en Pasen: bestelstop op de datum die jij zet'],
            'resultaat'   => 'Meer bestellingen vooraf, minder telefoon en minder no-show.',
            'cta'         => 'Bekijk online bestellen voor bakkerijen',
        ],
        'klantenportaal' => [
            'titel'       => 'Portaal voor vaste klanten',
            'voor'        => 'Zakelijke klanten zoals horeca en kantoren bekijken, wijzigen en herhalen hun vaste bestelling zelf en vinden hun facturen. Geen avondtelefoontjes meer.',
            'voorbeelden' => ['Vaste weekbestelling zelf aanpassen', 'Facturen en leverbonnen terugvinden', 'Wijziging vóór jouw deadline, anders blijft de vaste bestelling'],
            'resultaat'   => 'Minder avondtelefoontjes en minder fouten in vaste orders.',
            'cta'         => 'Bekijk het klantenportaal voor bakkerijen',
        ],
        'automatisering' => [
            'voor'        => 'Bestellingen lopen vanzelf door naar bevestiging, productielijst en factuur. Niets drie keer overtypen, niets vergeten.',
            'voorbeelden' => ['Bestelling meteen op de productielijst van de nacht', 'Bevestiging en herinnering naar de klant', 'Factuur voor zakelijke klanten automatisch, waar de boekhouding het toelaat'],
            'resultaat'   => 'Minder overtypen en geen vergeten taart.',
            'cta'         => 'Bekijk automatisering voor bakkerijen',
        ],
        'ai' => [
            'voor'        => 'De telefoon wordt opgenomen als jij in de bakkerij staat, vragen worden beantwoord uit jouw gegevens en taartbestellingen of terugbelverzoeken vastgelegd. Ook los af te nemen.',
            'voorbeelden' => ['Taartbestelling met datum, formaat en tekst genoteerd', 'Openingstijden en assortiment uit jouw gegevens', 'Over allergenen geen uitspraak: dat checkt een mens'],
            'resultaat'   => 'Bereikbaar tijdens het bakken; bestellingen komen compleet binnen.',
            'cta'         => 'Bekijk AI-telefonie voor bakkerijen',
        ],
    ],

    'praktijk' => [
        'intro'   => 'Zo kan het lopen bij een bakkerij met twee winkels:',
        'stappen' => [
            ['fase' => 'website',        't' => 'Eerst de website',       'b' => 'De bakkerij begint met een website waarop beide winkels, het assortiment en de openingstijden staan en die gevonden wordt op "bakkerij" plus de plaats.'],
            ['fase' => 'webshop',        't' => 'Dan online bestellen',    'b' => 'Daarna kunnen klanten taarten en feestdagenbestellingen online plaatsen, vooraf betaald, met een afhaalmoment.'],
            ['fase' => 'klantenportaal', 't' => 'Dan het zakelijke portaal','b' => 'Vervolgens regelen de horecaklanten hun vaste weekbestelling zelf in een portaal en vinden ze daar hun facturen.'],
            ['fase' => 'ai',             't' => 'Later AI-telefonie',     'b' => 'Later neemt de assistent de telefoon op tijdens het bakken: openingstijden, "hebben jullie glutenvrij brood" en taartbestellingen, netjes genoteerd.'],
        ],
    ],

    'telefonie' => [
        'titel'  => 'De telefoon gaat altijd als je net met deeg aan je handen staat',
        'tekst'  => 'Een AI-telefoonassistent neemt op als jij of je winkelpersoneel niet kan. Hij weet je openingstijden en assortiment, noteert een taartbestelling met datum en formaat en legt een terugbelverzoek vast. Over allergenen doet hij geen uitspraak; dat blijft mensenwerk.',
        'vragen' => ['Tot hoe laat zijn jullie zaterdag open?', 'Kan ik een slagroomtaart voor zondag bestellen?', 'Hebben jullie glutenvrij brood?', 'Is mijn bestelling al klaar?'],
        'cta'    => 'Bekijk AI-telefonie voor bakkerijen',
    ],

    'faq' => [
        ['q' => 'Kan ik ook alleen AI-telefonie gebruiken?', 'a' => 'Ja. Een nummer, je openingstijden en assortiment, en de assistent neemt op tijdens het bakken. Een webshop of portaal van ons is daarvoor niet nodig; heb je die later wel, dan kan hij daar bestellingen in aanmaken.'],
        ['q' => 'Werkt online bestellen ook rond de feestdagen?', 'a' => 'Juist dan. Je zet zelf een bestelstop per datum en een maximum per dag, zodat de productie het aankan. Bestellingen zijn vooraf betaald, dus geen taarten die blijven staan.'],
    ],
];
