<?php

namespace App\Jobs\AccessGuard;

use App\Mail\AccessGuardRadarWebChecksComplete;
use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use App\Models\User;
use App\Services\Features\FeatureResolver;
use App\Services\Radar\CmpScanner;
use App\Services\Radar\CookiesScanner;
use App\Services\Radar\TlsScanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Runt TLS + cookies + CMP scanners voor één asset. Bundelt de 3 web-checks
 * van #56 in één job zodat de queue 1 dispatch per asset doet (i.p.v. 3),
 * wat tickets in Horizon en log-noise scheelt.
 *
 * Een individuele scanner-failure trekt de andere niet mee: elke check
 * staat in een eigen try/catch en de uitkomst gaat de Log in onder
 * radar.<check_type>.diff zodra er iets verandert.
 *
 * Na alle 3 checks wordt — als ergens new/resolved findings zijn — ÉÉN
 * geaggregeerde AccessGuardRadarWebChecksComplete mail gedispatched (geen
 * 3 losse mails). Plan-gate via FeatureResolver op tool.radar.enabled.
 */
final class RunWebChecksScanJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 1;
	public int $timeout = 90;

	/** @param  string[]  $checks  subset van ['tls','cookies','cmp'] */
	public function __construct(
		public readonly int $assetId,
		public readonly array $checks = ['tls', 'cookies', 'cmp'],
	) {}

	public function handle(
		TlsScanner $tls,
		CookiesScanner $cookies,
		CmpScanner $cmp,
		FeatureResolver $features,
	): void {
		$asset = Asset::query()->find($this->assetId);
		if (! $asset || $asset->status !== 'active' || empty($asset->url)) {
			return;
		}

		$scanners = [
			'tls'     => $tls,
			'cookies' => $cookies,
			'cmp'     => $cmp,
		];

		$perCheck = [];
		$anyDiff = false;

		foreach ($this->checks as $check) {
			if (! isset($scanners[$check])) continue;
			try {
				$result = $scanners[$check]->scan($asset);
				$diff = $result['diff'] ?? ['new' => [], 'resolved' => []];
				if (! empty($diff['new']) || ! empty($diff['resolved'])) {
					$anyDiff = true;
					Log::info("radar.{$check}.diff", [
						'asset_id'  => $asset->id,
						'tenant_id' => $asset->tenant_id,
						'url'       => $asset->url,
						'scan_id'   => $result['scan_id'],
						'new'       => $diff['new'],
						'resolved'  => $diff['resolved'],
					]);
				}
				$scan = Scan::query()->find($result['scan_id']);
				if ($scan) {
					$perCheck[$check] = ['scan' => $scan, 'diff' => $diff];
				}
			} catch (Throwable $e) {
				Log::warning("radar.{$check}.error", [
					'asset_id' => $asset->id,
					'url'      => $asset->url,
					'message'  => $e->getMessage(),
				]);
			}
		}

		if ($anyDiff && ! empty($perCheck)) {
			$this->maybeDispatchMail($asset, $perCheck, $features);
		}
	}

	/** @param  array<string,array{scan:Scan,diff:array}>  $perCheck */
	private function maybeDispatchMail(Asset $asset, array $perCheck, FeatureResolver $features): void
	{
		$recipient = $this->resolveRecipientUser($asset);
		if (! $recipient) {
			return;
		}

		if (! $features->forUser($recipient)->bool('tool.radar.enabled')) {
			return;
		}

		try {
			Mail::to($recipient->email)->send(new AccessGuardRadarWebChecksComplete($asset, $perCheck));
		} catch (Throwable $e) {
			Log::warning('radar.web_checks.mail_failed', [
				'asset_id' => $asset->id,
				'to'       => $recipient->email,
				'error'    => $e->getMessage(),
			]);
		}
	}

	private function resolveRecipientUser(Asset $asset): ?User
	{
		if ($asset->owner_user_id) {
			$owner = User::query()->find($asset->owner_user_id);
			if ($owner?->email) {
				return $owner;
			}
		}
		return User::query()
			->where('tenant_id', $asset->tenant_id)
			->where('role', 'admin')
			->where('is_active', true)
			->first();
	}
}
