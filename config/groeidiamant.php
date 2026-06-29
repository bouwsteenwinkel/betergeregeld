<?php

/**
 * DE GROEIDIAMANT — de groeifasen ("facetten") waarlangs een klant groeit.
 *
 * Een niche-site (branche) kan op meerdere facetten binnenkomen via SEO: wat de
 * bezoeker in Google aanklikte bepaalt de instap. Bv. "webshop toevoegen op mijn
 * kapperswebsite" → facet 'webshop' i.p.v. 'website'. De funnel/landing en de
 * intake-vragen worden per facet samengesteld uit bouwblokken; de lead wordt
 * getagd met het facet zodat in de admin meteen duidelijk is wát de klant wil.
 *
 * LET OP: dit is een START-set. Pas labels/volgorde/keys gerust aan zodra de
 * definitieve Groeidiamant vaststaat — de structuur (facet-key → blokken/vragen)
 * blijft hetzelfde.
 *
 * Per facet:
 *   nr       volgnummer in de diamant
 *   label    weergavenaam (admin + pagina)
 *   tagline  korte pitch (voor hero/CTA-blokken)
 *   icon     emoji/teken voor blokken
 */
return [

    'default' => 'website',

    'facets' => [
        'website' => [
            'nr'      => 1,
            'label'   => 'Website',
            'tagline' => 'Een professionele site die klanten oplevert',
            'icon'    => '🌐',
        ],
        'webshop' => [
            'nr'      => 2,
            'label'   => 'Webshop',
            'tagline' => 'Verkoop online, gekoppeld aan je site',
            'icon'    => '🛍️',
        ],
        'vindbaarheid' => [
            'nr'      => 3,
            'label'   => 'Vindbaarheid / SEO',
            'tagline' => 'Beter gevonden worden in Google',
            'icon'    => '🔎',
        ],
        'marketing' => [
            'nr'      => 4,
            'label'   => 'Marketing / automatisering',
            'tagline' => 'Meer uit elke bezoeker halen',
            'icon'    => '📣',
        ],
        'onderhoud' => [
            'nr'      => 5,
            'label'   => 'Onderhoud / beveiliging',
            'tagline' => 'Veilig, snel en up-to-date blijven',
            'icon'    => '🛡️',
        ],
    ],
];
