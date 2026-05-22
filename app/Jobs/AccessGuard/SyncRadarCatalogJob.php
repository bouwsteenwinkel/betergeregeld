<?php

namespace App\Jobs\AccessGuard;

use App\Services\AccessGuard\Radar\Catalog\CatalogIngestor;
use App\Services\AccessGuard\Radar\Catalog\EndOfLifeCatalogIngestor;
use App\Services\AccessGuard\Radar\Catalog\WordPressCoreCatalogIngestor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs every catalog ingestor in sequence. Cheap enough to be the
 * scheduled "daily catalog refresh" entry-point AND the on-demand
 * action triggered from the admin UI.
 *
 * One ingestor failing must NOT stop later ones — the per-ingestor
 * `ingest()` already catches its own throwables and records into
 * last_sync_error, but we still wrap each call defensively.
 */
final class SyncRadarCatalogJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 1;
	public int $timeout = 180;

	public function handle(HttpFactory $httpFactory): array
	{
		$ingestors = [
			new EndOfLifeCatalogIngestor($httpFactory),
			new WordPressCoreCatalogIngestor($httpFactory),
		];

		$results = [];
		foreach ($ingestors as $ing) {
			$results[$ing->name()] = $this->runOne($ing);
		}

		Log::info('radar.catalog.sync', $results);
		return $results;
	}

	private function runOne(CatalogIngestor $ing): int
	{
		try {
			return $ing->ingest();
		} catch (\Throwable $e) {
			Log::warning('radar.catalog.ingestor_failed', [
				'ingestor' => $ing->name(),
				'error' => $e->getMessage(),
			]);
			return 0;
		}
	}
}
