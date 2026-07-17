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
    'api_version' => env('GOOGLE_ADS_API_VERSION', 'v18'),

    'token_file' => 'google-ads.json',
];
