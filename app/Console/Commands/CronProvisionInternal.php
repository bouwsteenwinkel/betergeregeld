<?php

namespace App\Console\Commands;

use App\Services\Monitor\CronMonitorPinger;
use Illuminate\Console\Command;

/**
 * Provisioned (idempotent) een cron-monitor voor elke interne scheduler-job uit
 * config('monitor.internal_crons'), zodat ze meteen in de admin zichtbaar zijn
 * — ook vóór hun eerste run. Bestaande monitors blijven ongemoeid (firstOrCreate),
 * dus handmatige aanpassingen aan periode/alerts blijven behouden.
 */
class CronProvisionInternal extends Command
{
	protected $signature = 'cron:provision-internal';

	protected $description = 'Maak cron-monitors aan voor de interne Laravel-scheduler-jobs (idempotent).';

	public function handle(): int
	{
		$defs = config('monitor.internal_crons', []);
		$created = 0;

		foreach ($defs as $def) {
			$monitor = CronMonitorPinger::ensure($def['key']);

			if ($monitor && $monitor->wasRecentlyCreated) {
				$created++;
				$this->info("Aangemaakt: {$def['key']} → {$monitor->name}");
			}
		}

		$this->info('Klaar — ' . count($defs) . ' interne jobs, ' . $created . ' nieuw aangemaakt.');

		return self::SUCCESS;
	}
}
