<?php

/**
 * Branche-gestuurde intake voor "website laten maken".
 *
 * - 'common'   : vragen die we elke lead stellen (voeden de 1-page design-brief).
 * - 'branches' : per branche extra vragen + gewenste functies. De keys komen
 *                overeen met App\Models\WebsiteLead::BRANCHES.
 *
 * Vraag-types: 'text' | 'textarea' | 'select' | 'multiselect' | 'boolean'.
 * Feature = een herbruikbare functionaliteit die de klant kan willen; de keuze
 * stuurt direct de 1-page opzet (bv. online-afspraken-blok, menu, reserveren).
 */
return [

    'common' => [
        ['key' => 'goal',        'label' => 'Wat is het belangrijkste doel van je website?', 'type' => 'textarea'],
        ['key' => 'has_logo',    'label' => 'Heb je al een logo / huisstijl?', 'type' => 'boolean'],
        ['key' => 'style',       'label' => 'Welke stijl/uitstraling past bij je?', 'type' => 'select',
            'options' => ['modern' => 'Modern & strak', 'warm' => 'Warm & persoonlijk', 'speels' => 'Speels & kleurrijk', 'zakelijk' => 'Zakelijk & degelijk', 'luxe' => 'Luxe & premium']],
        ['key' => 'colors',      'label' => 'Voorkeurskleuren (of "verras me")', 'type' => 'text'],
        ['key' => 'examples',    'label' => 'Websites die je mooi vindt (links)', 'type' => 'textarea'],
        ['key' => 'has_domain',  'label' => 'Heb je al een domeinnaam?', 'type' => 'text'],
        ['key' => 'content_ready','label' => 'Kun je teksten/foto’s aanleveren, of moeten wij dat verzorgen?', 'type' => 'select',
            'options' => ['self' => 'Lever ik zelf aan', 'help' => 'Graag hulp/ontzorgen', 'mix' => 'Mix']],
    ],

    'branches' => [

        'kapper_beauty' => [
            'questions' => [
                ['key' => 'services',     'label' => 'Welke behandelingen/diensten bied je aan?', 'type' => 'textarea'],
                ['key' => 'team_size',    'label' => 'Met hoeveel mensen werk je?', 'type' => 'text'],
            ],
            'features' => [
                'online_booking' => 'Online afspraken maken',
                'price_list'     => 'Prijslijst',
                'team'           => 'Team/medewerkers tonen',
                'reviews'        => 'Reviews',
                'gallery'        => 'Foto-galerij (voor/na)',
            ],
        ],

        'horeca' => [
            'questions' => [
                ['key' => 'cuisine',      'label' => 'Wat voor zaak/keuken is het?', 'type' => 'text'],
                ['key' => 'seats',        'label' => 'Aantal couverts/plaatsen', 'type' => 'text'],
            ],
            'features' => [
                'menu'            => 'Menu online tonen',
                'reservations'    => 'Online reserveren',
                'reservation_fee' => 'Aanbetaling/reserveringskosten bij reserveren',
                'takeaway'        => 'Afhalen/bezorgen',
                'opening_hours'   => 'Openingstijden',
                'events'          => 'Events/arrangementen',
            ],
        ],

        'vastgoed' => [ // makelaar / architect
            'questions' => [
                ['key' => 'discipline',   'label' => 'Makelaar of architect? En specialisatie?', 'type' => 'text'],
            ],
            'features' => [
                'portfolio'       => 'Portfolio / eerdere projecten',
                'contact_only'    => 'Alleen een nette contactpagina',
                'about'           => 'Over ons / visie',
                'listings'        => 'Aanbod/woningen tonen',
            ],
        ],

        'bouw_installatie' => [
            'questions' => [
                ['key' => 'work_area',    'label' => 'In welke regio werk je?', 'type' => 'text'],
                ['key' => 'specialties',  'label' => 'Specialismen (bv. cv, badkamers, dakwerk)', 'type' => 'textarea'],
            ],
            'features' => [
                'quote_request'   => 'Offerte/aanvraag-formulier',
                'projects'        => 'Uitgevoerde projecten',
                'services'        => 'Diensten/specialismen',
                'reviews'         => 'Reviews',
                'emergency'       => 'Spoed/24-uurs contact',
            ],
        ],

        'detailhandel' => [
            'questions' => [
                ['key' => 'sells',        'label' => 'Wat verkoop je?', 'type' => 'textarea'],
                ['key' => 'webshop',      'label' => 'Wil je online verkopen (webshop) of etalage?', 'type' => 'select',
                    'options' => ['shop' => 'Webshop', 'showcase' => 'Etalage/showcase', 'unsure' => 'Weet ik nog niet']],
            ],
            'features' => [
                'catalog'         => 'Productoverzicht',
                'webshop'         => 'Webshop / afrekenen',
                'opening_hours'   => 'Openingstijden + route',
                'reviews'         => 'Reviews',
            ],
        ],

        'sport_fitness' => [
            'questions' => [
                ['key' => 'offer',        'label' => 'Welke lessen/abonnementen bied je?', 'type' => 'textarea'],
            ],
            'features' => [
                'schedule'        => 'Rooster/lesplanning',
                'memberships'     => 'Abonnementen tonen',
                'online_signup'   => 'Online aanmelden/proefles',
                'team'            => 'Trainers tonen',
            ],
        ],

        'zorg' => [
            'questions' => [
                ['key' => 'practice',     'label' => 'Wat voor praktijk/zorg lever je?', 'type' => 'text'],
            ],
            'features' => [
                'appointments'    => 'Afspraak aanvragen',
                'services'        => 'Behandelingen/diensten',
                'team'            => 'Team/zorgverleners',
                'practical'       => 'Praktische info (locatie, tijden, vergoeding)',
            ],
        ],

        'automotive' => [
            'questions' => [
                ['key' => 'services',     'label' => 'Welke diensten (onderhoud, APK, verkoop)?', 'type' => 'textarea'],
            ],
            'features' => [
                'appointment'     => 'APK/onderhoud-afspraak',
                'stock'           => 'Occasions/aanbod',
                'services'        => 'Diensten',
                'reviews'         => 'Reviews',
            ],
        ],

        'zzp_diensten' => [
            'questions' => [
                ['key' => 'service',      'label' => 'Welke dienst lever je en aan wie?', 'type' => 'textarea'],
            ],
            'features' => [
                'services'        => 'Diensten/aanpak',
                'portfolio'       => 'Portfolio/cases',
                'contact'         => 'Contact/aanvraag',
                'about'           => 'Over mij',
            ],
        ],

        'overig' => [
            'questions' => [],
            'features' => [
                'contact'         => 'Contact',
                'services'        => 'Diensten/aanbod',
                'about'           => 'Over ons',
                'portfolio'       => 'Portfolio',
            ],
        ],
    ],
];
