<?php

namespace App\Jobs\AccessGuard;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\CmpScanner;
use App\Services\Radar\CookiesScanner;
use App\Services\Radar\TlsScanner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runt TLS + cookies + CMP scanners voor één asset. Bundelt de 3 web-checks
 * van #56 in één job zodat de queue 1 dispatch per asset doet (i.p.v. 3),
 * wat tickets in Horizon en log-noise scheelt.
 *
 * Een individuele scanner-failure trekt de andere niet mee: elke check
 * staat in een eigen try/catch en de uitkomst gaat de Log in onder
 * radar.<check_type>.diff zodra er iets verandert.
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

	public function handle(TlsScanner $tls, CookiesScanner $cookies, CmpScanner $cmp): void
	{
		$asset = Asset::query()->find($this->assetId);
		if (! $asset || $asset->status !== 'active' || empty($asset->url)) {
			return;
		}

		$scanners = [
			'tls'     => $tls,
			'cookies' => $cookies,
			'cmp'     => $cmp,
		];

		foreach ($this->checks as $check) {
			if (! isset($scanners[$check])) continue;
			try {
				$result = $scanners[$check]->scan($asset);
				$diff = $result['diff'] ?? ['new' => [], 'resolved' => []];
				if (! empty($diff['new']) || ! empty($diff['resolved'])) {
					Log::info("radar.{$check}.diff", [
						'asset_id'  => $asset->id,
						'tenant_id' => $asset->tenant_id,
						'url'       => $asset->url,
						'scan_id'   => $result['scan_id'],
						'new'       => $diff['new'],
						'resolved'  => $diff['resolved'],
					]);
				}
			} catch (\Throwable $e) {
				Log::warning("radar.{$check}.error", [
					'asset_id' => $asset->id,
					'url'      => $asset->url,
					'message'  => $e->getMessage(),
				]);
			}
		}
	}
}
