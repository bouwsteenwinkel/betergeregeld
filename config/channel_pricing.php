<?php

/**
 * Prijzen/pakketten voor de trigger-sites, gekoppeld aan de Groeidiamant-fases.
 * Generiek (zelfde aanbod voor elke niche). De bedragen zijn INDICATIEF — pas ze
 * aan naar je echte tarieven. De funnel blijft "gratis voorbeeld → vaste prijs
 * vooraf", dus de pagina toont richtprijzen en stuurt naar de aanvraag.
 *
 * ⚠️ CONTROLEER/AANPASSEN: de bedragen hieronder zijn placeholders.
 */

return [
    'eyebrow' => 'Prijzen',
    'h1'      => 'Duidelijke prijzen, geen verrassingen',
    'intro'   => 'Je begint waar je nu staat en breidt uit wanneer je eraan toe bent — precies de Groeidiamant. Hieronder de richtprijzen per pakket. Je krijgt altijd vooraf een vaste prijs op maat, en een gratis voorbeeld van jouw bedrijf om te zien wat je krijgt.',
    'note'    => 'Alle prijzen zijn indicatief en exclusief btw. Geen lange contracten — maandelijks opzegbaar. De exacte prijs krijg je vooraf, zonder verrassingen achteraf.',

    'packages' => [
        [
            'name'     => 'Start',
            'tagline'  => 'Je professionele website',
            'price'    => 'vanaf € 39',
            'period'   => 'per maand',
            'setup'    => 'of eenmalig vanaf € 795',
            'featured' => false,
            'features' => [
                'Professionele website op maat',
                'Vindbaar in Google in je eigen regio',
                'Je vakwerk en projecten in beeld',
                'Offerteaanvragen rechtstreeks in je mailbox',
                'Hosting, onderhoud en updates inbegrepen',
            ],
        ],
        [
            'name'     => 'Groei',
            'tagline'  => 'Website + webshop of klantenportaal',
            'price'    => 'vanaf € 89',
            'period'   => 'per maand',
            'setup'    => 'of eenmalig vanaf € 1.750',
            'featured' => true,
            'features' => [
                'Alles uit Start',
                'Webshop met iDEAL, of een klantenportaal',
                'Online betalen of afspraken laten plannen',
                'Gekoppeld aan je site — geen dubbel werk',
                'Uitbreidbaar wanneer je bedrijf groeit',
            ],
        ],
        [
            'name'     => 'Compleet',
            'tagline'  => 'Automatisering & slimme AI',
            'price'    => 'op maat',
            'period'   => '',
            'setup'    => 'prijs na een korte kennismaking',
            'featured' => false,
            'features' => [
                'Alles uit Groei',
                'Offertes, facturen en planning geautomatiseerd',
                'Koppelingen met je agenda en boekhouding',
                'AI-assistent die telefoon en chat aanneemt',
                'Volledig afgestemd op jouw werkwijze',
            ],
        ],
    ],
];
