<?php

/**
 * Plesk-koppeling (REST API) voor: een niche-domein als DOMEIN-ALIAS van
 * betergeregeld.com toevoegen en het Let's Encrypt-certificaat (her)uitgeven.
 *
 * De app draait op dezelfde VPS als Plesk, dus de API is bereikbaar op
 * https://127.0.0.1:8443 (self-signed paneel-cert → verify standaard uit).
 *
 * Zet in .env:
 *   PLESK_BASE_URL=https://127.0.0.1:8443      (paneel-URL; op de VPS = localhost)
 *   PLESK_API_KEY=...                          (secret key, zie hieronder — aanbevolen)
 *   PLESK_PARENT_DOMAIN=betergeregeld.com      (domein waar de aliassen onder komen)
 *
 * Óf, i.p.v. een API-key, admin-basic-auth (minder netjes):
 *   PLESK_ADMIN_LOGIN=admin
 *   PLESK_ADMIN_PASSWORD=...
 *
 * Een secret key maak je op de VPS aan (Windows Plesk):
 *   "%plesk_bin%\secret_key.exe" --create -ip-address 127.0.0.1 -description "betergeregeld app"
 * of via REST met admin-creds:
 *   POST https://127.0.0.1:8443/api/v2/auth/keys   (basic auth admin)  → { "key": "..." }
 */

return [
    'base_url' => rtrim(env('PLESK_BASE_URL', 'https://127.0.0.1:8443'), '/'),

    // Voorkeur: secret key (header X-API-Key). Val terug op admin basic-auth.
    'api_key'        => env('PLESK_API_KEY'),
    'admin_login'    => env('PLESK_ADMIN_LOGIN', 'admin'),
    'admin_password' => env('PLESK_ADMIN_PASSWORD'),

    // Hoofddomein/subscription waar de niche-domeinen als alias onder komen.
    'parent_domain' => env('PLESK_PARENT_DOMAIN', 'betergeregeld.com'),

    // Paneel-cert op :8443 is doorgaans self-signed → verificatie standaard uit.
    // Op productie (localhost) is dat veilig; zet op true met een geldige cert.
    'verify_ssl' => (bool) env('PLESK_VERIFY_SSL', false),

    // Let's Encrypt: ook www-alias meenemen bij het (her)uitgeven.
    'letsencrypt_include_www' => (bool) env('PLESK_LE_INCLUDE_WWW', true),
];
