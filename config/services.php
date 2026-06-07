<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Microsoft Entra ID (Azure AD) — used by AccessGuard directory sync.
    // Register an app at https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps
    // and grant delegated scopes: User.Read.All, Directory.Read.All, offline_access.
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID'),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
        'redirect_uri' => env('MICROSOFT_REDIRECT_URI'),
        'scope' => env('MICROSOFT_SCOPE', 'User.Read.All Directory.Read.All offline_access'),
    ],

    // Anthropic (Claude) — gebruikt door AnthropicClient (blog, vertalingen,
    // quality-scan). Via config zodat de key na `config:cache` beschikbaar
    // blijft; env() buiten config-files geeft dan namelijk null.
    'anthropic' => [
        'api_key'  => env('ANTHROPIC_API_KEY'),
        'key_path' => env('ANTHROPIC_KEY_PATH'),
    ],

];
