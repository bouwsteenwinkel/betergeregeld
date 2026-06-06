<?php

return [
    /*
     * Aantal dagen zonder succesvolle GSC-import waarna monitor:check-alerts
     * een "import staat stil"-alert stuurt (naar config('monitor.alert_email')).
     * GSC finaliseert data 2-3 dagen later, dus 3 voorkomt vals-alarm.
     */
    'freshness_alert_days' => (int) env('SEO_FRESHNESS_ALERT_DAYS', 3),
];
