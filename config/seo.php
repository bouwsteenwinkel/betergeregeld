<?php

return [
    /*
     * Aantal dagen zonder succesvolle GSC-import waarna monitor:check-alerts
     * een "import staat stil"-alert stuurt (naar config('monitor.alert_email')).
     * GSC finaliseert data 2-3 dagen later, dus 3 voorkomt vals-alarm.
     */
    'freshness_alert_days' => (int) env('SEO_FRESHNESS_ALERT_DAYS', 3),

    /*
     * Google PageSpeed Insights API-key. Zonder key werkt PSI ook, maar met
     * een lage gedeelde dagquota (snel "quota bereikt"). Met een eigen key
     * (Google Cloud Console → PageSpeed Insights API inschakelen → API-key)
     * is de quota ruim genoeg voor meerdere klanten. Via config zodat de key
     * na `config:cache` blijft werken (kale env() geeft dan null).
     */
    'psi_api_key' => env('PSI_API_KEY'),
];
