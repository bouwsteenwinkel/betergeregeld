<?php

return [
    // Paginacache voor de kanaalsites (app/Http/Middleware/CacheChannelPage.php).
    // Uit te zetten zonder deploy via CHANNEL_CACHE=false in de .env.
    'enabled' => (bool) env('CHANNEL_CACHE', true),

    // Hoe lang een gerenderde pagina blijft staan. Een uur is een afweging: lang
    // genoeg om de crawler snel te bedienen, kort genoeg dat een tekstwijziging
    // vanzelf doorkomt zonder dat iemand eraan denkt. Direct verversen kan met
    // `php artisan channel:cache-clear`.
    'ttl' => (int) env('CHANNEL_CACHE_TTL', 3600),
];
