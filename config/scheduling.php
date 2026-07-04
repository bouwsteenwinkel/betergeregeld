<?php

/**
 * Afsprakenplanner (platform-breed, gedeeld door alle trigger-sites).
 * Fase 1: alleen online afspraken via Google Meet, één centrale agenda.
 *
 * Werktijden/uitzonderingen zijn te beheren in de admin (availability_rules /
 * availability_exceptions); de default_hours hieronder is de fallback wanneer er
 * nog geen regels in de DB staan.
 */

return [
    'timezone'         => 'Europe/Amsterdam',
    'slot_minutes'     => 60,     // rasterstap tussen sloten (hele uren)
    'meeting_minutes'  => 60,     // duur van een afspraak (1 uur)
    'min_notice_hours' => 4,      // niet boekbaar binnen zoveel uur
    'horizon_days'     => 21,     // hoever vooruit boekbaar
    'buffer_minutes'   => 0,      // buffer voor/na een afspraak
    'hold_minutes'     => 10,     // reservering tijdens het invullen

    // Fallback-werktijden (ISO-weekdag 1=ma .. 7=zo) als er geen regels in de DB staan.
    'default_hours' => [
        1 => [['09:00', '17:00']],
        2 => [['09:00', '17:00']],
        3 => [['09:00', '17:00']],
        4 => [['09:00', '17:00']],
        5 => [['09:00', '17:00']],
    ],

    'organizer_name'  => 'Betergeregeld ICT',
    'organizer_email' => 'info@betergeregeld.com',

    // Google-koppeling (fase 1b). Zolang leeg → stub-gateway (geen echte Meet-links).
    'google' => [
        'enabled'       => env('GOOGLE_CALENDAR_ENABLED', false),
        'calendar_id'   => env('GOOGLE_CALENDAR_ID', 'primary'),
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],
];
