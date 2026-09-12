<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    |
    | Mensencheck op de publieke formulieren. Zolang er geen sleutels zijn staat
    | Turnstile UIT en verandert er niets (lokaal en in tests draait alles door).
    |
    | Aanmaken: Cloudflare-dashboard → Turnstile → widget toevoegen. Zelfde opzet
    | als in Studloop en Bouwsteenwinkel v3 (includes/turnstile.php daar), zodat er
    | één patroon is.
    |
    */
    'site_key' => env('TURNSTILE_SITE_KEY', ''),
    'secret' => env('TURNSTILE_SECRET', ''),

    'verify_url' => env('TURNSTILE_VERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),

    /*
    |--------------------------------------------------------------------------
    | Widgets per host
    |--------------------------------------------------------------------------
    |
    | WAAROM DIT BESTAAT. Een Turnstile-widget werkt ALLEEN op de hostnames die je
    | er in Cloudflare bij zet, en dat zijn er maximaal 10 ("10 out of 10 available
    | hostnames"). Deze app bedient betergeregeld.com plus 17 live kanaaldomeinen,
    | dus één widget kan dat nooit dekken. Zou je de sleutels van betergeregeld.com
    | op een kanaalsite gebruiken, dan weigert Cloudflare het token en breekt het
    | formulier — en omdat de controle fail-closed is, breekt het STIL: de bezoeker
    | krijgt alleen "probeer het opnieuw". Precies dezelfde val als in
    | Bouwsteenwinkel v3, waar het is opgelost met widgets_by_host.
    |
    | Hier gegroepeerd PER WIDGET in plaats van per host: met 17 domeinen is een
    | lijst per host onleesbaar en gaan afwijkingen tussen de twee lijsten
    | ongemerkt mis. De service pakt de eerste groep waar de aangevraagde host in
    | staat (met en zonder www) en valt anders terug op de sleutels hierboven.
    |
    | Staat een groep leeg (geen sleutels in de .env), dan is Turnstile op die
    | hosts UIT. Dat is bewust: liever een formulier zonder mensencheck dan een
    | formulier dat niemand meer kan versturen.
    |
    */
    'widgets' => [

        // Kanaalsites, deel 1. Widget "Betergeregeld kanalen 1" in Cloudflare.
        'kanalen1' => [
            'site_key' => env('TURNSTILE_KANALEN1_SITE_KEY', ''),
            'secret' => env('TURNSTILE_KANALEN1_SECRET', ''),
            'hosts' => [
                'jouw-aannemer-website.nl',
                'jouw-acupuncturist-website.nl',
                'jouw-administratiekantoor-website.nl',
                'jouw-advocaat-website.nl',
                'jouw-apotheek-website.nl',
                'jouw-architect-website.nl',
                'jouw-autogarage-website.nl',
                'jouw-badkamerspecialist-website.nl',
                'jouw-bakkerij-website.nl',
            ],
        ],

        // Kanaalsites, deel 2. Widget "Betergeregeld kanalen 2" in Cloudflare.
        'kanalen2' => [
            'site_key' => env('TURNSTILE_KANALEN2_SITE_KEY', ''),
            'secret' => env('TURNSTILE_KANALEN2_SECRET', ''),
            'hosts' => [
                'jouw-bedrijfswebsite.nl',
                'jouw-dietist-website.nl',
                'jouw-golfschool-website.nl',
                'jouw-klusbedrijf-website.nl',
                'jouw-loodgieter-website.nl',
                'jouw-rijschool-website.nl',
                'jouw-uitlaat-remmen-website.nl',
                'jouw-yogastudio-website.nl',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Lokaal overslaan
    |--------------------------------------------------------------------------
    |
    | Cloudflare staat localhost/.test niet op een widget toe, dus daar zou de
    | check ALTIJD falen en elk formulier lokaal onbruikbaar maken. Overgenomen uit
    | Bouwsteenwinkel v3 (turnstile_is_local_host).
    |
    */
    'skip_local' => (bool) env('TURNSTILE_SKIP_LOCAL', true),
];
