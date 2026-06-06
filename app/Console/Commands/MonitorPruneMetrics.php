<?php

namespace App\Console\Commands;

use App\Models\Monitor\Metric;
use Illuminate\Console\Command;

/**
 * Houdt monitor_metrics begrensd: verwijdert samples ouder dan de
 * retentieperiode (config/monitor.php → retention_days). Chunked delete zodat
 * een grote achterstand de DB niet in één query belast.
 */
class MonitorPruneMetrics extends Command
{
	protected $signature = 'monitor:prune-metrics {--days= : Overschrijf de retentieperiode in dagen}';

	protected $description = 'Verwijder monitor_metrics-samples ouder dan de retentieperiode.';

	public function handle(): int
	{
		$days = (int) ($this->option('days') ?: config('monitor.retention_days', 30));

		if ($days < 1) {
			$this->error('Retentie moet minstens 1 dag zijn.');

			return self::FAILURE;
		}

		$cutoff = now()->subDays($days);
		$total = 0;

		do {
			$deleted = Metric::where('collected_at', '<', $cutoff)->limit(5000)->delete();
			$total += $deleted;
		} while ($deleted > 0);

		$this->info("Verwijderd: {$total} samples ouder dan {$days} dagen (vóór {$cutoff->toDateTimeString()}).");

		return self::SUCCESS;
	}
}
