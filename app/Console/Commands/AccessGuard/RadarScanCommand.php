<?php

namespace App\Console\Commands\AccessGuard;

use App\Jobs\AccessGuard\RunRadarScanJob;
use App\Mail\AccessGuardRadarScanComplete;
use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * php artisan radar:scan [--asset=ID] [--tenant=UUID] [--all-active] [--queue]
 *
 * Examples:
 *   radar:scan --asset=42                  Scan one specific asset (sync)
 *   radar:scan --tenant=019dc4...          Scan all active assets for one tenant
 *   radar:scan --all-active                Scan every active asset, every tenant
 *   radar:scan --all-active --queue        Same, but dispatch jobs onto the queue
 *
 * Without --queue, scans run synchronously in the current PHP process —
 * fine for cron, fine for ad-hoc invocation. With --queue, the work is
 * pushed onto the configured queue (sync today, redis tomorrow).
 */
class RadarScanCommand extends Command
{
	protected $signature = 'radar:scan
		{--asset= : Specific asset id to scan}
		{--tenant= : Tenant uuid; scans all that tenant\'s active assets}
		{--all-active : Scan every active asset across every tenant}
		{--queue : Dispatch jobs onto the queue instead of running inline}
		{--no-mail : Skip the post-scan summary email}';

	protected $description = 'Run a Vulnerability Radar scan against one or more assets';

	public function handle(): int
	{
		$assets = $this->resolveAssets();
		if ($assets->isEmpty()) {
			$this->warn('No assets matched. Pass --asset, --tenant, or --all-active.');
			return self::INVALID;
		}

		$this->info("Scanning {$assets->count()} asset(s)…");
		$bar = $this->output->createProgressBar($assets->count());
		$bar->start();

		$queueMode = (bool) $this->option('queue');
		$skipMail = (bool) $this->option('no-mail');

		foreach ($assets as $asset) {
			if ($queueMode) {
				RunRadarScanJob::dispatch($asset->id);
				// Mail dispatch happens inside the job in queue mode
				// — out of scope for this slice; skip silently.
			} else {
				RunRadarScanJob::dispatchSync($asset->id);
				if (! $skipMail) {
					$this->emailScanSummary($asset->fresh());
				}
			}
			$bar->advance();
		}
		$bar->finish();
		$this->newLine(2);
		$this->info($queueMode ? 'Jobs dispatched.' : 'Scans complete.');
		return self::SUCCESS;
	}

	private function emailScanSummary(Asset $asset): void
	{
		$scan = Scan::query()
			->where('asset_id', $asset->id)
			->orderByDesc('id')
			->first();
		if (! $scan) return;

		$recipient = $this->resolveRecipient($asset);
		if (! $recipient) {
			$this->warn("  No recipient for asset {$asset->id} — email skipped");
			return;
		}

		try {
			Mail::to($recipient)->send(new AccessGuardRadarScanComplete($asset, $scan));
		} catch (\Throwable $e) {
			// Log but don't abort the rest of the scan loop.
			$this->warn("  Mail to {$recipient} failed: {$e->getMessage()}");
		}
	}

	private function resolveRecipient(Asset $asset): ?string
	{
		// 1. Asset's named owner if set
		if ($asset->owner_user_id) {
			$owner = User::query()->find($asset->owner_user_id);
			if ($owner?->email) return $owner->email;
		}
		// 2. First active admin in the tenant
		$tenantAdmin = User::query()
			->where('tenant_id', $asset->tenant_id)
			->where('role', 'admin')
			->where('is_active', true)
			->first();
		return $tenantAdmin?->email;
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
