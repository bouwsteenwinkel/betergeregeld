<?php

namespace App\Console\Commands\AccessGuard;

use App\Jobs\AccessGuard\RunWebChecksScanJob;
use App\Models\AccessGuard\Radar\Asset;
use Illuminate\Console\Command;

/**
 * php artisan radar:scan-web [--asset=ID] [--tenant=UUID] [--all-active]
 *                            [--check=tls,cookies,cmp] [--queue]
 *
 * Combineert TLS / cookies / CMP scans uit taak #56. Headers-scan blijft
 * apart (radar:scan-headers) — die had al een dedicated cron-slot.
 */
class RadarScanWebCommand extends Command
{
	protected $signature = 'radar:scan-web
		{--asset= : Specific asset id to scan}
		{--tenant= : Tenant uuid; scans all that tenant\'s active assets}
		{--all-active : Scan every active asset across every tenant}
		{--check=tls,cookies,cmp : Comma-separated checks (tls,cookies,cmp)}
		{--queue : Dispatch jobs onto the queue instead of running inline}';

	protected $description = 'Run Radar web-checks (TLS + cookies + CMP) against one or more assets';

	public function handle(): int
	{
		$assets = $this->resolveAssets();
		if ($assets->isEmpty()) {
			$this->warn('No assets matched. Pass --asset, --tenant, or --all-active.');
			return self::INVALID;
		}

		$checks = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('check')))));
		$checks = array_values(array_intersect($checks, ['tls', 'cookies', 'cmp']));
		if (empty($checks)) {
			$this->error('--check must include at least one of: tls, cookies, cmp');
			return self::INVALID;
		}

		$queueMode = (bool) $this->option('queue');
		$this->info("Scanning {$assets->count()} asset(s) — checks: " . implode(',', $checks) . ($queueMode ? ' (queued)' : ' (sync)') . '…');

		$totals = ['new' => 0, 'resolved' => 0, 'errors' => 0];
		foreach ($assets as $asset) {
			if ($queueMode) {
				RunWebChecksScanJob::dispatch($asset->id, $checks);
				continue;
			}

			try {
				RunWebChecksScanJob::dispatchSync($asset->id, $checks);

				// Pak de laatste 10 scan-rows en koppel ze terug aan check via raw_output-keys.
				$recent = $asset->scans()
					->where('status', 'success')
					->orderByDesc('id')
					->limit(10)
					->get();

				$lineParts = [];
				foreach ($checks as $check) {
					$scan = $recent->first(fn ($s) => $this->scanMatchesCheck($s->raw_output ?? [], $check));
					$diff = $scan?->raw_output['diff'] ?? ['new' => [], 'resolved' => []];
					$totals['new']      += count($diff['new']);
					$totals['resolved'] += count($diff['resolved']);
					if (! empty($diff['new']) || ! empty($diff['resolved'])) {
						$lineParts[] = sprintf('%s +%d/-%d', $check, count($diff['new']), count($diff['resolved']));
					}
				}
				if (! empty($lineParts)) {
					$this->line("  [{$asset->id}] {$asset->url} — " . implode(' | ', $lineParts));
				}
			} catch (\Throwable $e) {
				$totals['errors']++;
				$this->warn("  [{$asset->id}] {$asset->url} — error: {$e->getMessage()}");
			}
		}

		$this->newLine();
		$this->info($queueMode
			? 'Jobs dispatched.'
			: "Done. Diff totals: +{$totals['new']} new, -{$totals['resolved']} resolved, {$totals['errors']} errors.");
		return self::SUCCESS;
	}

	/**
	 * Een scan-row in raw_output bevat per scanner een herkenbare key:
	 *   - TLS: host + cert
	 *   - cookies: url + status_code (geen 'cmps')
	 *   - CMP: cmps + trackers
	 */
	private function scanMatchesCheck(array $rawOutput, string $check): bool
	{
		return match ($check) {
			'tls'     => isset($rawOutput['host']),
			'cookies' => isset($rawOutput['url']) && ! isset($rawOutput['cmps']),
			'cmp'     => isset($rawOutput['cmps']),
			default   => false,
		};
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
