<?php

namespace App\Console\Commands\AccessGuard;

use App\Jobs\AccessGuard\RunSecurityHeadersScanJob;
use App\Models\AccessGuard\Radar\Asset;
use Illuminate\Console\Command;

/**
 * php artisan radar:scan-headers [--asset=ID] [--tenant=UUID] [--all-active] [--queue]
 *
 * Spiegelt radar:scan, maar voor de security-headers probe. Scheduler draait
 * 'm dagelijks --all-active --queue; ad-hoc sync mode is handig voor debug.
 */
class RadarScanHeadersCommand extends Command
{
	protected $signature = 'radar:scan-headers
		{--asset= : Specific asset id to scan}
		{--tenant= : Tenant uuid; scans all that tenant\'s active assets}
		{--all-active : Scan every active asset across every tenant}
		{--queue : Dispatch jobs onto the queue instead of running inline}';

	protected $description = 'Run a security-headers scan against one or more Radar assets';

	public function handle(): int
	{
		$assets = $this->resolveAssets();
		if ($assets->isEmpty()) {
			$this->warn('No assets matched. Pass --asset, --tenant, or --all-active.');
			return self::INVALID;
		}

		$queueMode = (bool) $this->option('queue');
		$this->info("Scanning {$assets->count()} asset(s)" . ($queueMode ? ' (queued)' : ' (sync)') . '…');

		$totals = ['new' => 0, 'resolved' => 0];
		foreach ($assets as $asset) {
			if ($queueMode) {
				RunSecurityHeadersScanJob::dispatch($asset->id);
				continue;
			}

			try {
				RunSecurityHeadersScanJob::dispatchSync($asset->id);
				// In sync mode willen we de diff zien zonder logs te moeten lezen.
				$scan = $asset->fresh()->scans()->orderByDesc('id')->first();
				$diff = $scan?->raw_output['diff'] ?? ['new' => [], 'resolved' => []];
				if (! empty($diff['new']) || ! empty($diff['resolved'])) {
					$totals['new']      += count($diff['new']);
					$totals['resolved'] += count($diff['resolved']);
					$this->line(sprintf(
						'  [%d] %s — +%d new, -%d resolved',
						$asset->id,
						$asset->url,
						count($diff['new']),
						count($diff['resolved']),
					));
				}
			} catch (\Throwable $e) {
				$this->warn("  [{$asset->id}] {$asset->url} — error: {$e->getMessage()}");
			}
		}

		$this->newLine();
		$this->info($queueMode
			? 'Jobs dispatched.'
			: "Done. Diff totals: +{$totals['new']} new, -{$totals['resolved']} resolved.");
		return self::SUCCESS;
	}

	/** @return \Illuminate\Support\Collection<int, Asset> */
	private function resolveAssets()
	{
		$query = Asset::query()->where('status', 'active')->whereNotNull('url');

		if ($this->option('asset')) {
			return Asset::query()->where('id', (int) $this->option('asset'))->get();
		}
		if ($this->option('tenant')) {
			return $query->where('tenant_id', (string) $this->option('tenant'))->get();
		}
		if ($this->option('all-active')) {
			return $query->get();
		}
		return collect();
	}
}
