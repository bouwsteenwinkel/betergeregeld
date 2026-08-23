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

    /*
     * Google-accounts (e-mail) die als delegated owner aan elke channel-site-
     * GSC-property worden toegevoegd. Het service-account blijft de technische
     * eigenaar (voor de daily-import); deze mensen zien de property daarnaast in
     * hun EIGEN Search Console. Komma-gescheiden.
     *   GSC_OWNER_EMAILS=info@bouwsteenwinkel.nl,collega@voorbeeld.nl
     */
    'gsc_owner_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('GSC_OWNER_EMAILS', ''))
    ))),

    /*
     * Tenant waaronder een nieuw geprovisionde channel-site-property in ons eigen
     * dashboard (seo_properties) wordt gehangen. Leeg = de tenant van de eerste
     * bestaande property, wat voor onze eigen sites klopt maar impliciet is; zet
     * 'm expliciet zodra er meerdere klant-tenants naast elkaar draaien.
     */
    'gsc_default_tenant_id' => env('GSC_DEFAULT_TENANT_ID'),
];
