<?php

// Pakketteksten op /prijzen per branche (array_replace_recursive over channel_pricing.php).
// Prijzen zelf ongewijzigd; alleen de features in branchetaal. 15-09-2026: bakkerij.
return [
    'bakkerij' => [
        'packages' => [
            0 => ['features' => [
                'Professionele website op maat',
                'Vindbaar in Google op "bakkerij" plus jouw plaats',
                'Assortiment, openingstijden en taarten in beeld',
                'Bestellen of bellen met één knop, ook op mobiel',
                'Hosting, onderhoud en updates inbegrepen',
            ]],
            1 => ['features' => [
                'Alles uit Start',
                'Webshop met iDEAL, afhaaldatum en -tijd',
                'Of een klantenportaal voor zakelijke klanten',
                'Gekoppeld aan je site, geen dubbel werk',
                'Uitbreidbaar wanneer je bakkerij groeit',
            ]],
            2 => ['tagline' => 'Automatisering & AI-telefonie', 'features' => [
                'Alles uit Groei',
                'Bestellingen automatisch op productielijst en factuur',
                'Koppelingen met kassa of boekhouding waar dat kan',
                'AI-telefonie die opneemt als jij bakt (ook los: zie hieronder)',
                'Volledig afgestemd op jouw werkwijze',
            ]],
        ],
    ],
];
