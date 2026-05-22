<?php

namespace App\Console\Commands\AccessGuard;

use App\Jobs\AccessGuard\SyncRadarCatalogJob;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Factory as HttpFactory;

/**
 * php artisan radar:sync-catalog
 *
 * Pulls latest-stable + EOL data from wordpress.org and endoflife.date
 * into accessguard_radar_software_catalog. Schedule daily via cron.
 */
class RadarSyncCatalogCommand extends Command
{
	protected $signature = 'radar:sync-catalog';

	protected $description = 'Refresh the Radar software catalog (latest-stable + EOL data)';

	public function handle(HttpFactory $http): int
	{
		$this->info('Syncing catalog…');
		$results = (new SyncRadarCatalogJob())->handle($http);
		foreach ($results as $name => $count) {
			$this->line("  {$name}: {$count} row(s) refreshed");
		}
		return self::SUCCESS;
	}
}
