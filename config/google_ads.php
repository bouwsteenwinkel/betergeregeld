<?php

// Google Ads API-koppeling. Hergebruikt dezelfde Google-OAuth-app als de
// Agenda-koppeling (GOOGLE_CLIENT_ID/SECRET); alleen de scope (adwords) en het
// refresh-token (storage/app/google-ads.json) zijn apart.
return [
    'client_id'     => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),

    // Uit het API-centrum van het manager-account (geheim → alleen in .env).
    'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),

    // Cijfers zonder streepjes. login = manager-account (MCC), customer = het
    // advertentie-account waar de campagnes op draaien.
    'login_customer_id' => preg_replace('/\D/', '', (string) env('GOOGLE_ADS_LOGIN_CUSTOMER_ID', '')),
    'customer_id'       => preg_replace('/\D/', '', (string) env('GOOGLE_ADS_CUSTOMER_ID', '')),

    // Moet EXACT zo als toegestane redirect-URI in de Google Cloud OAuth-app staan.
    'redirect_uri' => env('GOOGLE_ADS_REDIRECT_URI', rtrim((string) env('APP_URL', ''), '/') . '/admin/ads/oauth/callback'),

    // Bump wanneer Google een versie uitfaseert (zie de Ads API release-notes).
    // Een uitgefaseerde versie geeft HTTP 404 met een HTML-pagina, op élke aanroep.
    // Gemeten 10-09-2026: v21 geeft 404, v22 en v23 antwoorden. Bewust v22 en niet
    // v23: de mutate-payloads (campagnes aanmaken) zijn niet tegen v23 getest.
    'api_version' => env('GOOGLE_ADS_API_VERSION', 'v22'),

    'token_file' => 'google-ads.json',

    // Conversie-actie-ID (alleen cijfers) voor "Nieuw abonnement", gebruikt als
    // productDestinationId bij de Data Manager-import. Gemaakt via
    // `php artisan ads:conversion-action`.
    'conversion_abo' => preg_replace('/\D/', '', (string) env('GOOGLE_ADS_CONVERSION_ABO', '7690743819')),
];
