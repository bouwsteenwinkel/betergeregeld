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

    'organizer_name' => 'Betergeregeld ICT',

    // Het adres dat de klant terugmailt (Reply-To) en dat als ORGANIZER in de
    // .ics-bijlage staat. Moet een echt postvak zijn: de bevestigings- en
    // herinneringsmails nodigen expliciet uit om te antwoorden.
    'organizer_email' => env('SCHEDULING_ORGANIZER_EMAIL', 'info@bouwsteenwinkel.nl'),

    // Waar de interne "nieuwe afspraak"-melding en de storingsalarmen heen gaan.
    // Losgetrokken van MAIL_FROM_ADDRESS: dat is een afzender, geen postbus die
    // iemand leest, en op de voorbeeldwaarde (hello@example.com) verdween de
    // melding geruisloos.
    'notify_email' => env('SCHEDULING_NOTIFY_EMAIL', env('MONITOR_ALERT_EMAIL', 'info@bouwsteenwinkel.nl')),

    /**
     * Herinneringen. Twee momenten, elk met een eigen reden:
     * - lead_days dagen vooraf: vroeg genoeg om nog te kúnnen verzetten (een verzette
     *   afspraak is geen verloren lead).
     * - de ochtend van de afspraak zelf: vangt wie het gewoon vergeten is.
     *
     * day_of_hour is een lokaal klokuur, geen "x uur vooraf". Voor een afspraak die
     * zelf vóór dat uur + min_lead_minutes begint schuift de mail naar voren, anders
     * zou hij ná de afspraak vertrekken of zo kort ervoor dat hij niets meer toevoegt.
     */
    'reminders' => [
        'lead_days'         => (int) env('SCHEDULING_REMINDER_LEAD_DAYS', 2),
        'day_of_hour'       => (int) env('SCHEDULING_REMINDER_DAY_OF_HOUR', 8),
        'min_lead_minutes'  => 60,
    ],

    // Google-koppeling (fase 1b). Zolang leeg → stub-gateway (geen echte Meet-links).
    'google' => [
        'enabled'       => env('GOOGLE_CALENDAR_ENABLED', false),
        // 'primary' = de hoofdagenda van het gekoppelde account. Een agenda-id mag
        // ook een e-mailadres zijn; laat dit op 'primary' staan tenzij je bewust een
        // gedeelde agenda gebruikt die niet de hoofdagenda is.
        'calendar_id'   => env('GOOGLE_CALENDAR_ID', 'primary'),
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),

        // Het account dat gekoppeld MOET zijn. Koppelen met een ander Google-account
        // wordt geweigerd: dat is de fout die je pas maanden later ontdekt, wanneer
        // blijkt dat alle afspraken in een privé-agenda staan.
        'expected_account' => env('GOOGLE_CALENDAR_ACCOUNT', 'info@bouwsteenwinkel.nl'),

        // Nodigt Google de klant zelf uit (en zegt het af bij annuleren/verzetten)?
        'send_updates' => env('GOOGLE_CALENDAR_SEND_UPDATES', 'all'),
    ],
];
