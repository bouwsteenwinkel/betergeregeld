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
            'title'   => 'Een website voor je kapperszaak — klanten boeken zelf online',
            'pill'    => 'Voor kappers & barbers',
            'intro'   => 'Minder gebel, een vollere agenda: klanten maken zelf 24/7 online een afspraak en zien meteen je werk, prijzen en team. We zetten vóór ons gesprek al een voorbeeld van jouw salon-site klaar.',
            'branche' => 'kapper_beauty',
            'features' => [
                'online_booking' => 'Online afspraken maken (24/7)',
                'price_list'     => 'Prijslijst / behandelingen',
                'team'           => 'Team / kappers tonen',
                'gallery'        => 'Foto-galerij (kapsels / voor-na)',
                'reviews'        => 'Reviews',
                'opening_hours'  => 'Openingstijden + route',
                'products'       => 'Verkoop van haarproducten',
            ],
            'questions' => [
                'general' => [
                    ['key' => 'goal',  'label' => 'Wat wil je vooral bereiken (meer afspraken, nieuwe klanten, minder telefoon)?', 'type' => 'textarea'],
                    ['key' => 'style', 'label' => 'Welke uitstraling past bij je salon?', 'type' => 'select',
                        'options' => ['strak' => 'Strak & modern', 'warm' => 'Warm & gezellig', 'luxe' => 'Luxe / premium', 'stoer' => 'Stoer / barbershop']],
                    ['key' => 'has_logo', 'label' => 'Heb je al een logo / huisstijl?', 'type' => 'boolean'],
                ],
                'specific' => [
                    ['key' => 'salon_type', 'label' => 'Wat voor zaak is het?', 'type' => 'select',
                        'options' => ['dames_heren' => 'Dames & heren', 'dames' => 'Dameskapper', 'heren' => 'Heren / barbershop', 'beauty' => 'Kapper + beauty']],
                    ['key' => 'services',  'label' => 'Welke behandelingen/diensten bied je aan?', 'type' => 'textarea'],
                    ['key' => 'team_size', 'label' => 'Met hoeveel kappers werk je?', 'type' => 'text'],
                    ['key' => 'booking_tool', 'label' => 'Gebruik je al een afsprakensysteem? Zo ja, welk (bv. Salonized, Treatwell)?', 'type' => 'text'],
                    ['key' => 'walk_in', 'label' => 'Werk je op afspraak, inloop, of allebei?', 'type' => 'select',
                        'options' => ['afspraak' => 'Alleen op afspraak', 'inloop' => 'Alleen inloop', 'beide' => 'Allebei']],
                ],
            ],
        ],

        'dameskapper' => [
            'title'   => 'Een website voor je dameskapsalon — vol geboekt, online',
            'pill'    => 'Voor dameskappers',
            'intro'   => 'Laat klanten 24/7 zelf online boeken en laat je coupe- en kleurwerk zien. We zetten vóór ons gesprek al een voorbeeld van jouw salon-site klaar.',
            'branche' => 'kapper_beauty',
            'features' => [
                'online_booking' => 'Online afspraken maken (24/7)',
                'price_list'     => 'Prijslijst / behandelingen',
                'gallery'        => 'Foto-galerij (kleur & coupe)',
                'bridal'         => 'Bruids- & gelegenheidskapsels',
                'team'           => 'Team / kapsters tonen',
                'reviews'        => 'Reviews',
                'opening_hours'  => 'Openingstijden + route',
                'products'       => 'Verkoop van haarproducten',
            ],
            'questions' => [
                'general' => [
                    ['key' => 'goal',  'label' => 'Wat wil je vooral bereiken (meer afspraken, nieuwe klanten, minder telefoon)?', 'type' => 'textarea'],
                    ['key' => 'style', 'label' => 'Welke uitstraling past bij je salon?', 'type' => 'select',
                        'options' => ['elegant' => 'Elegant & verzorgd', 'modern' => 'Modern & strak', 'warm' => 'Warm & gezellig', 'luxe' => 'Luxe / premium']],
                    ['key' => 'has_logo', 'label' => 'Heb je al een logo / huisstijl?', 'type' => 'boolean'],
                ],
                'specific' => [
                    ['key' => 'services',      'label' => 'Welke behandelingen bied je aan (knippen, kleuren, highlights/balayage, föhnen, opsteken)?', 'type' => 'textarea'],
                    ['key' => 'specialties',   'label' => 'Waar ben je sterk in / specialisatie (bv. kleur, krullen, bruidskapsels)?', 'type' => 'text'],
                    ['key' => 'team_size',     'label' => 'Met hoeveel kapsters werk je?', 'type' => 'text'],
                    ['key' => 'booking_tool',  'label' => 'Gebruik je al een afsprakensysteem? Zo ja, welk (bv. Salonized, Treatwell)?', 'type' => 'text'],
                ],
            ],
        ],

    ],
];
