<?php

namespace App\Console\Commands;

use App\Models\Monitor\CronPing;
use App\Models\Monitor\Metric;
use App\Models\Monitor\SocketLabsEvent;
use Illuminate\Console\Command;

/**
 * Houdt monitor_metrics én cron_pings begrensd: verwijdert rijen ouder dan de
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

		$pings = 0;
		do {
			$deleted = CronPing::where('received_at', '<', $cutoff)->limit(5000)->delete();
			$pings += $deleted;
		} while ($deleted > 0);

		// SocketLabs-events hebben een eigen retentie (config/socketlabs.php).
		$slDays = (int) ($this->option('days') ?: config('socketlabs.retention_days', 30));
		$slCutoff = now()->subDays(max(1, $slDays));
		$slEvents = 0;
		do {
			$deleted = SocketLabsEvent::where('occurred_at', '<', $slCutoff)->limit(5000)->delete();
			$slEvents += $deleted;
		} while ($deleted > 0);

		$this->info("Verwijderd: {$total} metric-samples + {$pings} cron-pings ouder dan {$days} dagen + {$slEvents} SocketLabs-events ouder dan {$slDays} dagen.");

		return self::SUCCESS;
	}
}
