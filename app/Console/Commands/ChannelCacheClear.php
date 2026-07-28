<?php

namespace App\Console\Commands;

use App\Http\Middleware\CacheChannelPage;
use Illuminate\Console\Command;

/**
 * Gooit de paginacache van de kanaalsites weg.
 *
 * Nodig na een tekstwijziging die meteen zichtbaar moet zijn; anders staat een
 * pagina hooguit een uur oud (config/channel_cache.php). Werkt door het
 * volgnummer in de cachesleutels op te hogen, dus zonder de rest van de cache
 * (config, routes, andere features) aan te raken.
 */
class ChannelCacheClear extends Command
{
    protected $signature = 'channel:cache-clear';

    protected $description = 'Paginacache van de kanaalsites leegmaken';

    public function handle(): int
    {
        $versie = CacheChannelPage::flush();
        $this->info('Paginacache geleegd — nieuwe versie: ' . $versie);

        return self::SUCCESS;
    }
}
