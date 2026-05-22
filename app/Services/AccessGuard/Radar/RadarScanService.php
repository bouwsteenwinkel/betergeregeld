<?php

namespace App\Services\AccessGuard\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Scan;
use App\Models\AccessGuard\Radar\SoftwareInstance;
use App\Services\AccessGuard\Radar\Fingerprint\DetectedSoftware;
use App\Services\AccessGuard\Radar\Fingerprint\Fingerprinter;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use App\Services\AccessGuard\Radar\Matching\VulnMatcher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs every registered Fingerprinter against an Asset and persists the
 * results into accessguard_radar_software_instances + a single Scan row
 * for the audit trail.
 *
 * Important behaviour:
 *   - Idempotent on (asset_id, vendor, product, path): re-running a scan
 *     updates last_seen_at + version in-place rather than appending rows
 *   - Best-effort: a single fingerprinter throwing does NOT abort the
 *     scan — the error is logged into Scan.raw_output and other
 *     fingerprinters still run
 *   - Tenant-safe by construction: SoftwareInstance.tenant_id is copied
 *     from the Asset, never accepted from caller input
 *
 * NOT in scope here (next slices):
 *   - Vulnerability matching (DetectedSoftware → Vulnerability rows)
 *   - Risk scoring + Finding creation
 *   - RiskFlag escalation
 */
final class RadarScanService
{
	/** @param  Fingerprinter[]  $fingerprinters */
	public function __construct(
		private readonly HttpClient $http,
		private readonly array $fingerprinters,
		private readonly ?VulnMatcher $matcher = null,
	) {}

	public function scan(Asset $asset, string $scanType = 'http_probe', ?CarbonImmutable $now = null): Scan
	{
		$now ??= CarbonImmutable::now();

		$scan = Scan::query()->create([
			'tenant_id' => $asset->tenant_id,
			'asset_id' => $asset->id,
			'scan_type' => $scanType,
			'status' => 'running',
			'started_at' => $now,
		]);

		$detections = [];
		$errors = [];

		foreach ($this->fingerprinters as $fp) {
			$class = $fp::class;
			try {
				$found = $fp->fingerprint($asset, $this->http);
				foreach ($found as $d) {
					$detections[] = $d;
				}
			} catch (Throwable $e) {
				// One bad fingerprinter must not poison the whole scan.
				// We log structured detail for the next reviewer but keep
				// the scan as 'success' if any other fingerprinter delivered.
				$errors[$class] = $e->getMessage();
				Log::warning('radar.fingerprinter_failed', [
					'tenant_id' => $asset->tenant_id,
					'asset_id' => $asset->id,
					'fingerprinter' => $class,
					'error' => $e->getMessage(),
				]);
			}
		}

		// Persist software instances. We dedup on (vendor, product, path) so
		// two fingerprinters reporting the same WP install only create one
		// SoftwareInstance row — the higher-confidence detection wins on
		// version, and detection_method records whichever found it last.
		$persisted = $this->persist($asset, $detections, $now);

		// Match against the vulnerability catalog. Optional dependency so
		// older callers / tests that don't pass a matcher still work; in
		// production the job wires one in.
		$findingsCount = 0;
		if ($this->matcher) {
			try {
				$asset->load('softwareInstances');
				$findingsCount = $this->matcher->matchAsset($asset, $now);
			} catch (Throwable $e) {
				$errors['VulnMatcher'] = $e->getMessage();
				Log::warning('radar.matcher_failed', [
					'asset_id' => $asset->id,
					'error' => $e->getMessage(),
				]);
			}
		}

		$scan->update([
			'status' => $persisted === 0 && $errors !== [] ? 'error' : 'success',
			'detections_count' => $persisted,
			'findings_count' => $findingsCount,
			'finished_at' => CarbonImmutable::now(),
			'error_message' => $errors === [] ? null : 'See raw_output',
			'raw_output' => [
				'errors' => $errors,
				'detections' => array_map(fn (DetectedSoftware $d) => [
					'vendor' => $d->vendor,
					'product' => $d->product,
					'version' => $d->version,
					'method' => $d->detectionMethod,
					'confidence' => $d->confidence,
					'path' => $d->path,
				], $detections),
			],
		]);

		$asset->update([
			'last_scanned_at' => $now,
			'last_seen_at' => $now,
		]);

		return $scan->refresh();
	}

	/**
	 * @param  DetectedSoftware[]  $detections
	 * @return int  number of distinct software instances created or updated
	 */
	private function persist(Asset $asset, array $detections, CarbonImmutable $now): int
	{
		// Dedup with the highest-confidence (and version-bearing) detection
		// winning per (vendor, product, path) bucket. Doing this in-memory
		// avoids hammering MySQL with N updateOrCreate calls when a single
		// page references the same plugin from 5 different asset URLs.
		$byKey = [];
		foreach ($detections as $d) {
			$key = strtolower("{$d->vendor}|{$d->product}|" . ($d->path ?? ''));
			$existing = $byKey[$key] ?? null;
			if ($existing === null
				|| ($d->version !== null && $existing->version === null)
				|| ($d->confidence > $existing->confidence)
			) {
				$byKey[$key] = $d;
			}
		}

		$count = 0;
		foreach ($byKey as $d) {
			$si = SoftwareInstance::query()->firstOrNew([
				'asset_id' => $asset->id,
				'vendor' => $d->vendor,
				'product' => $d->product,
				'path' => $d->path,
			]);

			$si->tenant_id = $asset->tenant_id;
			$si->version = $d->version;
			$si->detection_method = $d->detectionMethod;
			$si->last_seen_at = $now;
			if (! $si->exists) {
				$si->first_detected_at = $now;
			}
			$si->save();
			$count++;
		}

		return $count;
	}
}
