<?php

namespace App\Console\Commands\AccessGuard;

use App\Jobs\AccessGuard\SyncRadarVulnerabilitiesJob;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Factory as HttpFactory;

/**
 * php artisan radar:sync-vulns
 *
 * Pulls CISA KEV + OSV.dev advisories into accessguard_radar_vulnerabilities.
 * Schedule daily (or twice-daily) — KEV updates several times a week,
 * OSV continuously.
 */
class RadarSyncVulnsCommand extends Command
{
	protected $signature = 'radar:sync-vulns';

	protected $description = 'Refresh the Radar vulnerability catalog (CISA KEV + OSV.dev)';

	public function handle(HttpFactory $http): int
	{
		$this->info('Syncing vulnerability feeds…');
		$results = (new SyncRadarVulnerabilitiesJob())->handle($http);
		foreach ($results as $name => $count) {
			$this->line("  {$name}: {$count} row(s) refreshed");
		}
		return self::SUCCESS;
	}
}
