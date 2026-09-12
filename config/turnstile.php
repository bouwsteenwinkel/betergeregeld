<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile
    |--------------------------------------------------------------------------
    |
    | Mensencheck op het contactformulier. Zolang site_key + secret leeg zijn staat
    | Turnstile UIT en verandert er niets (lokaal en in tests draait alles gewoon door).
    | Vul beide in de .env op de server om de challenge te activeren.
    |
    | Aanmaken: Cloudflare-dashboard → Turnstile → widget toevoegen voor betergeregeld.com.
    | Zelfde opzet als in Studloop (config/turnstile.php daar), zodat er één patroon is.
    |
    */
    'site_key' => env('TURNSTILE_SITE_KEY', ''),
    'secret' => env('TURNSTILE_SECRET', ''),

    'verify_url' => env('TURNSTILE_VERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),
];
