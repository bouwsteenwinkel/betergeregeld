<?php

namespace App\Jobs\AccessGuard;

use App\Services\AccessGuard\Radar\Vulnerabilities\CisaKevIngestor;
use App\Services\AccessGuard\Radar\Vulnerabilities\OsvIngestor;
use App\Services\AccessGuard\Radar\Vulnerabilities\VendorProductNormalizer;
use App\Services\AccessGuard\Radar\Vulnerabilities\VulnerabilityFeedIngestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Drives all vulnerability feed ingestors. CISA KEV first (small, gives
 * us KEV signals immediately), then OSV.dev (per-product API calls,
 * heavier). Each ingestor is wrapped — one source failing must not
 * abort the others.
 */
final class SyncRadarVulnerabilitiesJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 1;
	public int $timeout = 600;

	public function handle(HttpFactory $httpFactory): array
	{
		$normaliser = new VendorProductNormalizer();
		$ingestors = [
			new CisaKevIngestor($httpFactory, $normaliser),
			new OsvIngestor($httpFactory),
		];

		$results = [];
		foreach ($ingestors as $ing) {
			$results[$ing->name()] = $this->runOne($ing);
		}
		Log::info('radar.vulns.sync', $results);
		return $results;
	}

	private function runOne(VulnerabilityFeedIngestor $ing): int
	{
		try {
			return $ing->ingest();
		} catch (\Throwable $e) {
			Log::warning('radar.vulns.ingestor_failed', [
				'ingestor' => $ing->name(), 'error' => $e->getMessage(),
			]);
			return 0;
		}
	}
}
