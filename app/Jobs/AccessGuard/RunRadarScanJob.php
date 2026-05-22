<?php

namespace App\Jobs\AccessGuard;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Fingerprint\GeneratorMetaFingerprinter;
use App\Services\AccessGuard\Radar\Fingerprint\HttpHeaderFingerprinter;
use App\Services\AccessGuard\Radar\Fingerprint\WordPressFingerprinter;
use App\Services\AccessGuard\Radar\Http\SafeHttpClient;
use App\Services\AccessGuard\Radar\Http\SsrfGuard;
use App\Services\AccessGuard\Radar\Matching\VulnMatcher;
use App\Services\AccessGuard\Radar\RadarScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatchable wrapper for a single asset scan. Per-asset jobs (rather
 * than per-tenant batches) so a slow target can't block every other
 * scan in the same window — Laravel's default queue worker concurrency
 * handles fan-out.
 *
 * Wired with default fingerprinters in handle() so callers don't need
 * to know the strategy list — the matcher slice can later swap this
 * for a config-driven discovery (Tagged services).
 */
final class RunRadarScanJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 1;
	public int $timeout = 120;

	public function __construct(
		public readonly int $assetId,
		public readonly string $scanType = 'http_probe',
	) {}

	public function handle(HttpFactory $httpFactory): void
	{
		$asset = Asset::query()->find($this->assetId);
		if (! $asset || $asset->status !== 'active') {
			return;
		}

		$service = new RadarScanService(
			http: new SafeHttpClient($httpFactory, new SsrfGuard()),
			fingerprinters: [
				new HttpHeaderFingerprinter(),
				new GeneratorMetaFingerprinter(),
				new WordPressFingerprinter(),
			],
			matcher: new VulnMatcher(),
		);

		$service->scan($asset, $this->scanType);
	}
}
