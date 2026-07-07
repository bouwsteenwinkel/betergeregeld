<?php

/**
 * OpenProvider-koppeling voor domeinregistratie + DNS.
 *
 * Zet de credentials/handles in .env:
 *   OPENPROVIDER_USERNAME=...        (je OpenProvider-loginnaam)
 *   OPENPROVIDER_PASSWORD=...        (wachtwoord)
 *   OPENPROVIDER_OWNER_HANDLE=AB123456-NL   (customer/handle die eigenaar wordt)
 *   CHANNEL_TARGET_IP=85.215.166.3   (publiek IP van de VPS-webserver, voor de A-records)
 *
 * Optioneel (vallen terug op de owner-handle):
 *   OPENPROVIDER_ADMIN_HANDLE, OPENPROVIDER_TECH_HANDLE, OPENPROVIDER_BILLING_HANDLE
 *   OPENPROVIDER_NS_GROUP  (standaard 'dns-openprovider' = OpenProvider-nameservers)
 *   OPENPROVIDER_PERIOD    (registratieduur in jaren, standaard 1)
 */

return [
    'base_url' => rtrim(env('OPENPROVIDER_BASE_URL', 'https://api.openprovider.eu'), '/'),

    'username' => env('OPENPROVIDER_USERNAME'),
    'password' => env('OPENPROVIDER_PASSWORD'),

    // Contact-handles voor de registratie (OpenProvider-customer-handles).
    'owner_handle'   => env('OPENPROVIDER_OWNER_HANDLE'),
    'admin_handle'   => env('OPENPROVIDER_ADMIN_HANDLE')   ?: env('OPENPROVIDER_OWNER_HANDLE'),
    'tech_handle'    => env('OPENPROVIDER_TECH_HANDLE')    ?: env('OPENPROVIDER_OWNER_HANDLE'),
    'billing_handle' => env('OPENPROVIDER_BILLING_HANDLE') ?: env('OPENPROVIDER_OWNER_HANDLE'),

    // Nameservers: OpenProvider-DNS gebruiken; de A-records (hieronder) wijzen naar de VPS.
    'ns_group' => env('OPENPROVIDER_NS_GROUP', 'dns-openprovider'),

    // Doel-IP van de VPS-webserver voor de A-records (@ en www).
    'target_ip' => env('CHANNEL_TARGET_IP'),

    // Registratieduur in jaren.
    'period' => (int) env('OPENPROVIDER_PERIOD', 1),

    // TTL voor de DNS-records (seconden).
    'ttl' => (int) env('OPENPROVIDER_TTL', 3600),
];
