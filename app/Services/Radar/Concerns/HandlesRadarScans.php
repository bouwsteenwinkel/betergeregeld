<?php

namespace App\Services\Radar\Concerns;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Finding;
use App\Models\AccessGuard\Radar\Scan;
use Illuminate\Support\Carbon;

/**
 * Gedeelde scan-orchestratie voor Radar-scanners (TLS, cookies, CMP, ...).
 *
 * Subklasse levert per scanner:
 *   - getCheckType(): 'tls' | 'cookies' | 'cmp' | …
 *   - getScanType():  'http_probe' | 'agent_pull' | …
 *
 * En roept binnen scan():
 *   $scan        = $this->openScan($asset);
 *   $keysBefore  = $this->snapshotKeysBefore($asset);
 *   …eigen analyse + verzamel failingKeys/details…
 *   [$count, $diff] = $this->persistFindingsWithDiff(
 *       $asset, $scan, $failingKeys, $keysBefore
 *   );
 *   $this->closeScan($scan, $asset, $result + ['diff' => $diff], $count);
 *
 * Bij error: $this->failScan($scan, $e); throw $e;
 *
 * SecurityHeadersScanner gebruikt deze trait niet — die heeft 'n eigen
 * (oudere) implementatie die identiek is, maar refactoren is out-of-scope
 * voor #56.
 */
trait HandlesRadarScans
{
	abstract protected function getCheckType(): string;

	protected function getScanType(): string
	{
		return 'http_probe';
	}

	protected function openScan(Asset $asset): Scan
	{
		return Scan::create([
			'tenant_id'  => $asset->tenant_id,
			'asset_id'   => $asset->id,
			'scan_type'  => $this->getScanType(),
			'status'     => 'running',
			'started_at' => Carbon::now(),
		]);
	}

	/** @return string[] */
	protected function snapshotKeysBefore(Asset $asset): array
	{
		return Finding::query()
			->where('asset_id', $asset->id)
			->where('check_type', $this->getCheckType())
			->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
			->pluck('finding_key')
			->all();
	}

	/**
	 * Upsert findings voor de huidige failing keys, auto-resolve oude die nu pass zijn,
	 * en lever diff (new/resolved/unchanged) terug t.o.v. snapshot.
	 *
	 * Elk failingKeys-element moet:
	 *   ['finding_key' => str, 'title' => str, 'detail' => str,
	 *    'severity' => 'low|medium|high|critical',
	 *    'risk_factors' => array]
	 *
	 * @param  array<string, array{finding_key:string,title:string,detail:string,severity:string,risk_factors:array}>  $failingKeys
	 *         Keyed op finding_key (zodat dedup binnen één scan triviaal is)
	 * @param  string[]  $keysBefore
	 * @return array{0:int,1:array{new:string[],resolved:string[],unchanged:string[]}}
	 */
	protected function persistFindingsWithDiff(Asset $asset, Scan $scan, array $failingKeys, array $keysBefore): array
	{
		$now = Carbon::now();
		$checkType = $this->getCheckType();
		$activeKeysAfter = [];

		foreach ($failingKeys as $key => $f) {
			$severity  = $f['severity'] ?? 'low';
			$riskScore = $this->severityToScore($severity);

			$existing = Finding::query()
				->where('asset_id', $asset->id)
				->where('check_type', $checkType)
				->where('finding_key', $key)
				->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
				->first();

			if ($existing) {
				$existing->update([
					'last_detected_at' => $now,
					'risk_score'       => $riskScore,
					'severity'         => $severity,
					'title'            => $f['title'],
					'detail'           => $f['detail'],
					'risk_factors'     => $f['risk_factors'] ?? [],
				]);
			} else {
				Finding::create([
					'tenant_id'         => $asset->tenant_id,
					'asset_id'          => $asset->id,
					'check_type'        => $checkType,
					'finding_key'       => $key,
					'title'             => $f['title'],
					'detail'            => $f['detail'],
					'severity'          => $severity,
					'risk_score'        => $riskScore,
					'risk_factors'      => $f['risk_factors'] ?? [],
					'status'            => 'new',
					'first_detected_at' => $now,
					'last_detected_at'  => $now,
				]);
			}
			$activeKeysAfter[$key] = true;
		}

		Finding::query()
			->where('asset_id', $asset->id)
			->where('check_type', $checkType)
			->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
			->whereNotIn('finding_key', array_keys($activeKeysAfter))
			->update([
				'status'           => 'resolved',
				'resolved_at'      => $now,
				'resolution_notes' => "Auto-resolved: {$checkType} check passes nu (scan #{$scan->id})",
			]);

		$keysBeforeSet = array_fill_keys($keysBefore, true);
		$diff = [
			'new'       => array_values(array_diff(array_keys($activeKeysAfter), array_keys($keysBeforeSet))),
			'resolved'  => array_values(array_diff(array_keys($keysBeforeSet), array_keys($activeKeysAfter))),
			'unchanged' => array_values(array_intersect(array_keys($keysBeforeSet), array_keys($activeKeysAfter))),
		];

		return [count($activeKeysAfter), $diff];
	}

	protected function closeScan(Scan $scan, Asset $asset, array $result, int $findingsCount): void
	{
		$scan->update([
			'status'           => 'success',
			'finished_at'      => Carbon::now(),
			'detections_count' => count($result['details'] ?? []),
			'findings_count'   => $findingsCount,
			'raw_output'       => $result,
		]);
		$asset->update(['last_scanned_at' => Carbon::now()]);
	}

	protected function failScan(Scan $scan, \Throwable $e): void
	{
		$scan->update([
			'status'        => 'error',
			'finished_at'   => Carbon::now(),
			'error_message' => $e->getMessage(),
		]);
	}

	protected function severityToScore(string $severity): int
	{
		return match ($severity) {
			'critical' => 90,
			'high'     => 70,
			'medium'   => 45,
			'low'      => 20,
			default    => 10,
		};
	}
}
