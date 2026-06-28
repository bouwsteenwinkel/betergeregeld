<?php

/**
 * Promotie-kanalen: elk kanaal is een eigen landingspagina (/p/{kanaal}) met
 * eigen ALGEMENE + SPECIFIEKE vragen en gewenste functies. Alle afspraken komen
 * centraal binnen als WebsiteLead, getagd met 'channel' = de kanaal-key, zodat
 * in de admin duidelijk is via welk kanaal de lead binnenkwam.
 *
 * NIEUW KANAAL TOEVOEGEN = één blok hieronder. Geen code nodig:
 *   '<kanaal-key>' => [
 *      'title'    => 'H1 op de pagina',
 *      'pill'     => 'kleine badge bovenaan',
 *      'intro'    => 'introtekst',
 *      'branche'  => 'horeca',           // koppelt de lead aan een branche (zie WebsiteLead::BRANCHES)
 *      'features' => [ 'key' => 'Label', ... ],   // gewenste functies (checkboxes)
 *      'questions' => [
 *          'general'  => [ ['key'=>..,'label'=>..,'type'=>'text|textarea|select|boolean','options'=>[..]], ... ],
 *          'specific' => [ ... ],
 *      ],
 *   ],
 *
 * Vraag-types: text | textarea | select | boolean. Antwoorden worden per key op
 * de lead opgeslagen (answers), gefilterd op exact deze definitie.
 */
return [

    'channels' => [

        'horeca' => [
            'title'   => 'Een website voor je restaurant of café',
            'pill'    => 'Speciaal voor de horeca',
            'intro'   => 'Menu, reserveren en vindbaarheid — in één strakke site. We zetten vooraf een voorbeeld klaar zodat je bij de afspraak al ziet hoe het wordt.',
            'branche' => 'horeca',
            'features' => [
                'menu'            => 'Menukaart online tonen',
                'reservations'    => 'Online reserveren',
                'reservation_fee' => 'Aanbetaling/reserveringskosten bij reserveren',
                'takeaway'        => 'Afhalen/bezorgen',
                'opening_hours'   => 'Openingstijden',
                'events'          => 'Events/arrangementen',
            ],
            'questions' => [
                'general' => [
                    ['key' => 'goal',  'label' => 'Wat moet de site vooral doen (meer gasten, reserveringen, naamsbekendheid)?', 'type' => 'textarea'],
                    ['key' => 'style', 'label' => 'Welke sfeer past bij je zaak?', 'type' => 'select',
                        'options' => ['warm' => 'Warm & gezellig', 'chique' => 'Chique', 'modern' => 'Modern & strak', 'casual' => 'Casual']],
                ],
                'specific' => [
                    ['key' => 'cuisine', 'label' => 'Wat voor keuken/zaak is het?', 'type' => 'text'],
                    ['key' => 'seats',   'label' => 'Aantal couverts/plaatsen', 'type' => 'text'],
                    ['key' => 'deposit_amount', 'label' => 'Bij aanbetaling: welk bedrag per reservering?', 'type' => 'text'],
                ],
            ],
        ],

        'kapper' => [
            'title'   => 'Een website voor je kapsalon of beautyzaak',
            'pill'    => 'Speciaal voor kappers & beauty',
            'intro'   => 'Klanten laten online een afspraak maken en zien je werk. We zetten vooraf een voorbeeld klaar voor je salon.',
            'branche' => 'kapper_beauty',
            'features' => [
                'online_booking' => 'Online afspraken maken',
                'price_list'     => 'Prijslijst',
                'team'           => 'Team/medewerkers tonen',
                'reviews'        => 'Reviews',
                'gallery'        => 'Foto-galerij (voor/na)',
            ],
            'questions' => [
                'general' => [
                    ['key' => 'goal', 'label' => 'Wat is het belangrijkste doel van de site?', 'type' => 'textarea'],
                ],
                'specific' => [
                    ['key' => 'services',  'label' => 'Welke behandelingen/diensten bied je aan?', 'type' => 'textarea'],
                    ['key' => 'team_size', 'label' => 'Met hoeveel mensen werk je?', 'type' => 'text'],
                    ['key' => 'booking_tool', 'label' => 'Gebruik je al een afsprakensysteem? Zo ja, welk?', 'type' => 'text'],
                ],
            ],
        ],

    ],
];
