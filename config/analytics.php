<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meting op de channel-sites
    |--------------------------------------------------------------------------
    |
    | Leeg laten = er wordt niets geladen. Zodra hier een GTM-container staat,
    | laadt die op de channel-sites MET Google Consent Mode v2: de default is
    | "denied" op alle opslag, en de CMP (resources/cmp/loader.js) stuurt pas
    | een 'consent update' zodra de bezoeker kiest.
    |
    | Waarom GTM wél direct laadt en andere tags niet: GTM zet zelf geen cookies
    | en is met Consent Mode juist de gate die bepaalt of tags mogen vuren.
    | Google Ads accepteert in de EER geen conversiedata zonder Consent Mode v2.
    | De regel blijft dus staan: hang GA4/Ads/heatmaps NOOIT los in een layout,
    | maar zet ze in GTM (met consent-checks) of als CMP-script in de admin.
    |
    | De dataLayer-events die de sites pushen (zie bgTrack) zijn onafhankelijk
    | van deze id's; ze staan er ook zonder GTM, dan luistert er alleen niemand.
    |
    */

    // bv. 'GTM-XXXXXXX'
    'gtm_id' => env('ANALYTICS_GTM_ID', ''),

    // Optioneel: GA4 rechtstreeks (alleen gebruiken als je GEEN GTM draait).
    // bv. 'G-XXXXXXXXXX'
    'ga4_id' => env('ANALYTICS_GA4_ID', ''),

    // Optioneel: Google Ads conversie-id, bv. 'AW-123456789'. Alleen nodig als
    // je de Ads-tag buiten GTM om wilt laden.
    'ads_id' => env('ANALYTICS_ADS_ID', ''),

];
