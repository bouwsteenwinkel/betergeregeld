<?php

namespace App\Services\AccessGuard\Radar\Matching;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Finding;
use App\Models\AccessGuard\Radar\SoftwareInstance;
use App\Models\AccessGuard\Radar\Vulnerability;
use Carbon\CarbonImmutable;

/**
 * Matches one SoftwareInstance against the vulnerabilities catalog and
 * upserts Finding rows. Confidence model:
 *
 *   - Wildcard '*' affected_range  → 0.50  (KEV-style: product hit only)
 *   - Specific range + version in  → 0.95  (OSV/NVD with bounds)
 *   - Specific range + no version  → 0.30  (we know there's a vuln on
 *                                            this product but can't tell
 *                                            if THIS install is affected)
 *
 * A simple risk_score (0–100) is assigned here based on CVSS + KEV +
 * known-exploit signals. The full multi-factor scorer (with asset
 * exposure, criticality, compensating controls) lives in slice 5 — we
 * leave room for it by keeping risk_factors as JSON.
 *
 * Idempotent on (asset_id, software_instance_id, vulnerability_id).
 */
final class VulnMatcher
{
	public function matchInstance(SoftwareInstance $instance, ?CarbonImmutable $now = null): int
	{
		$now ??= CarbonImmutable::now();
		if (! $instance->vendor || ! $instance->product) return 0;

		$candidates = Vulnerability::query()
			->where('vendor', $instance->vendor)
			->where('product', $instance->product)
			->get();

		$created = 0;
		foreach ($candidates as $vuln) {
			$matched = $this->matches($instance, $vuln);
			if (! $matched['hit']) continue;

			[$score, $factors] = $this->score($instance, $vuln, $matched);
			$severity = Finding::severityForScore($score);

			$finding = Finding::query()->updateOrCreate(
				[
					'asset_id' => $instance->asset_id,
					'software_instance_id' => $instance->id,
					'vulnerability_id' => $vuln->id,
				],
				[
					'tenant_id' => $instance->tenant_id,
					'risk_score' => $score,
					'risk_factors' => $factors,
					'severity' => $severity,
					'last_detected_at' => $now,
				],
			);

			// First-detection bookkeeping. updateOrCreate doesn't expose
			// "was insert vs update", so we set first_detected_at only
			// when the model is fresh (created_at == last_detected_at).
			if ($finding->wasRecentlyCreated) {
				$finding->update([
					'status' => 'new',
					'first_detected_at' => $now,
				]);
			}
			$created++;
		}
		return $created;
	}

	public function matchAsset(Asset $asset, ?CarbonImmutable $now = null): int
	{
		$total = 0;
		foreach ($asset->softwareInstances as $instance) {
			$total += $this->matchInstance($instance, $now);
		}
		return $total;
	}

	/**
	 * @return array{hit:bool, confidence:float, reason:string}
	 */
	private function matches(SoftwareInstance $instance, Vulnerability $vuln): array
	{
		$range = $vuln->affected_range ?? '*';

		// Wildcard (KEV-style): product-level hit, no version comparison.
		// Useful but lower-confidence since we can't tell if this version
		// is the one being exploited.
		if ($range === '*') {
			return ['hit' => true, 'confidence' => 0.50, 'reason' => 'product_match_no_range'];
		}

		// Specific range + we have a detected version → real comparison.
		if ($instance->version) {
			$inRange = VersionRange::contains($range, $instance->version);
			return [
				'hit' => $inRange,
				'confidence' => $inRange ? 0.95 : 0.0,
				'reason' => $inRange ? 'version_in_range' : 'version_out_of_range',
			];
		}

		// Specific range, but we don't know the installed version. Surface
		// at low confidence so it shows up in "needs_review" workflows
		// without firing alerts.
		return ['hit' => true, 'confidence' => 0.30, 'reason' => 'product_match_unknown_version'];
	}

	/**
	 * Minimal scoring for slice 4 — slice 5 layers in asset exposure,
	 * criticality, compensating controls.
	 *
	 * @return array{0:int, 1:array<int,string>}
	 */
	private function score(SoftwareInstance $instance, Vulnerability $vuln, array $matched): array
	{
		$factors = [];
		$base = $vuln->cvss_score ? (int) round((float) $vuln->cvss_score * 10) : 50;
		$factors[] = "CVSS base " . ($vuln->cvss_score ?? 'unknown') . " → {$base}";

		$score = $base;
		if ($vuln->cisa_kev) {
			$score += 15;
			$factors[] = '+15 actief misbruikt (CISA KEV)';
		}
		if ($vuln->exploit_available) {
			$score += 10;
			$factors[] = '+10 publieke exploit beschikbaar';
		}
		if ($vuln->patch_available && $vuln->fixed_version) {
			$score -= 10;
			$factors[] = "-10 patch beschikbaar (versie {$vuln->fixed_version})";
		}
		if ($matched['confidence'] < 0.7) {
			$score -= 20;
			$factors[] = '-20 lage match-confidence (' . round($matched['confidence'] * 100) . '%)';
		}

		$score = max(0, min(100, $score));
		return [$score, $factors];
	}
}
