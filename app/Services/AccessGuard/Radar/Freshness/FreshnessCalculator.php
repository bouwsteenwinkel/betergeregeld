<?php

namespace App\Services\AccessGuard\Radar\Freshness;

use App\Models\AccessGuard\Radar\SoftwareCatalogEntry;
use App\Models\AccessGuard\Radar\SoftwareInstance;
use Carbon\CarbonImmutable;

/**
 * Compares a SoftwareInstance (what we found on the asset) against the
 * SoftwareCatalogEntry (what the world considers current) and returns a
 * FreshnessVerdict. Pure function over its inputs — vulnerability data
 * is NOT consulted here, that's the matcher's job in slice 4. The
 * `vulnerable` and `outdated_vulnerable` cases are reserved so the
 * matcher can layer on top without changing the enum.
 *
 * Version semantics:
 *   - We treat versions as dot-separated integer tuples ("6.4.2" → [6,4,2]).
 *   - Pre-release suffixes (`-rc1`, `-beta.2`, `-alpha`) flip the verdict
 *     to Prerelease regardless of how they compare to stable.
 *   - "Versions behind" counts entries in release_history newer than the
 *     detected version, capped at 99.
 */
final class FreshnessCalculator
{
	/**
	 * Pure: no DB I/O, no relationship access. Caller is responsible for
	 * loading the catalog entry AND telling us whether open findings
	 * exist — see `forInstance()` for the convenience helper that does
	 * both lookups.
	 *
	 * @param  bool  $hasOpenFinding   true if there's at least one
	 *                                 non-resolved/non-false-positive
	 *                                 Finding linked to this instance
	 */
	public function calculate(
		SoftwareInstance $instance,
		?SoftwareCatalogEntry $catalog,
		bool $hasOpenFinding = false,
	): FreshnessVerdict {
		// No detected version → can't compare. Header said "nginx" but
		// not "nginx/1.24.0" — surface as Unknown rather than guessing.
		if (! $instance->version) {
			return new FreshnessVerdict(
				status: FreshnessStatus::Unknown,
				reason: 'No version detected on the asset',
			);
		}

		// Pre-release detection runs even without a catalog entry —
		// running an RC in production is suspicious regardless.
		if ($this->isPrerelease($instance->version)) {
			return new FreshnessVerdict(
				status: FreshnessStatus::Prerelease,
				reason: "Pre-release {$instance->version} detected",
				latestStable: $catalog?->latest_stable_version,
			);
		}

		// EOL check: if the major.minor of the detected version is past
		// its EOL date, it's a hard "no longer supported" verdict —
		// outranks "outdated" because patches simply aren't coming.
		if ($catalog && ($eolDate = $this->eolDateFor($instance->version, $catalog->eol_dates ?? []))) {
			if ($eolDate->isPast()) {
				return new FreshnessVerdict(
					status: FreshnessStatus::Eol,
					reason: "Version {$instance->version} reached EOL on {$eolDate->format('Y-m-d')}",
					latestStable: $catalog->latest_stable_version,
					eolDate: $eolDate,
				);
			}
		}

		if (! $catalog || ! $catalog->latest_stable_version) {
			return new FreshnessVerdict(
				status: FreshnessStatus::Unknown,
				reason: 'No catalog entry — sync the catalog to compare against the latest stable release',
			);
		}

		$cmp = $this->compareVersions($instance->version, $catalog->latest_stable_version);

		if ($cmp === 0) {
			// Even up-to-date software can have unfixed CVEs (0-day, vendor
			// dragging feet). Open finding on the latest version → Vulnerable.
			if ($hasOpenFinding) {
				return new FreshnessVerdict(
					status: FreshnessStatus::Vulnerable,
					reason: "Latest stable {$catalog->latest_stable_version} but has open vulnerability finding(s)",
					latestStable: $catalog->latest_stable_version,
					versionsBehind: 0,
				);
			}
			return new FreshnessVerdict(
				status: FreshnessStatus::UpToDate,
				reason: "Matches latest stable {$catalog->latest_stable_version}",
				latestStable: $catalog->latest_stable_version,
				versionsBehind: 0,
			);
		}

		if ($cmp > 0) {
			// Detected newer than catalog. Could be: catalog is stale, or
			// the customer runs a vendor-patched build. Treat as up-to-date
			// rather than scaring the user — a dedicated "ahead of stable"
			// state could be added later if it turns out to be common.
			return new FreshnessVerdict(
				status: FreshnessStatus::UpToDate,
				reason: "Detected {$instance->version} ahead of catalog's {$catalog->latest_stable_version}",
				latestStable: $catalog->latest_stable_version,
				versionsBehind: 0,
			);
		}

		$behind = $this->countVersionsBehind($instance->version, $catalog->release_history ?? []);
		// Outdated + open finding → escalate the colour. Otherwise the
		// user thinks "I'm just a few versions behind, no big deal".
		$status = $hasOpenFinding ? FreshnessStatus::OutdatedVulnerable : FreshnessStatus::OutdatedSafe;
		$reasonSuffix = $hasOpenFinding ? ' — open vulnerability finding(s)' : '';
		return new FreshnessVerdict(
			status: $status,
			reason: ($behind !== null
				? "{$behind} version(s) behind — latest is {$catalog->latest_stable_version}"
				: "Older than current stable {$catalog->latest_stable_version}")
				. $reasonSuffix,
			latestStable: $catalog->latest_stable_version,
			versionsBehind: $behind,
		);
	}

	/**
	 * Convenience entry point: look up the catalog row + check for open
	 * findings, then dispatch into calculate(). Use this from controllers
	 * and Filament columns. The two queries are kept here (not inside
	 * calculate()) so calculate() stays test-pure.
	 */
	public function forInstance(SoftwareInstance $instance): FreshnessVerdict
	{
		$catalog = SoftwareCatalogEntry::query()
			->where('vendor', $instance->vendor)
			->where('product', $instance->product)
			->first();

		$hasOpenFinding = $instance->findings()
			->whereNotIn('status', ['resolved', 'false_positive', 'patched'])
			->exists();

		return $this->calculate($instance, $catalog, $hasOpenFinding);
	}

	private function isPrerelease(string $version): bool
	{
		return (bool) preg_match('~-(?:alpha|beta|rc|dev|nightly|snapshot)~i', $version);
	}

	/** @return int -1 if a<b, 0 if equal, 1 if a>b */
	private function compareVersions(string $a, string $b): int
	{
		$pa = $this->parse($a);
		$pb = $this->parse($b);
		$len = max(count($pa), count($pb));
		for ($i = 0; $i < $len; $i++) {
			$x = $pa[$i] ?? 0;
			$y = $pb[$i] ?? 0;
			if ($x !== $y) return $x < $y ? -1 : 1;
		}
		return 0;
	}

	/** @return int[] */
	private function parse(string $version): array
	{
		// Strip any pre-release / build suffix for comparison ("1.2.3-rc1" → "1.2.3")
		$core = preg_replace('~[-+].*$~', '', $version) ?? $version;
		$parts = explode('.', $core);
		$out = [];
		foreach ($parts as $p) {
			$out[] = is_numeric($p) ? (int) $p : 0;
		}
		return $out;
	}

	/** @param  array<string,string>  $eolDates  major.minor → ISO date */
	private function eolDateFor(string $version, array $eolDates): ?CarbonImmutable
	{
		if ($eolDates === []) return null;
		$parts = $this->parse($version);
		// Try MAJOR.MINOR first, then MAJOR alone — endoflife.date entries
		// for nginx are major-only, for PHP/Node are major.minor.
		$candidates = [];
		if (count($parts) >= 2) {
			$candidates[] = "{$parts[0]}.{$parts[1]}";
		}
		$candidates[] = (string) ($parts[0] ?? '');

		foreach ($candidates as $key) {
			if (isset($eolDates[$key])) {
				try {
					return CarbonImmutable::parse($eolDates[$key]);
				} catch (\Throwable) {
					return null;
				}
			}
		}
		return null;
	}

	/**
	 * Count how many entries in release_history (newest-first) come
	 * BEFORE the detected version. Returns null if history is empty
	 * or the detected version isn't in the list (we can't reliably count).
	 *
	 * @param  array<int,array{version:string,released?:string}>  $history
	 */
	private function countVersionsBehind(string $detected, array $history): ?int
	{
		if ($history === []) return null;
		$count = 0;
		foreach ($history as $entry) {
			if (! isset($entry['version'])) continue;
			$cmp = $this->compareVersions($entry['version'], $detected);
			if ($cmp > 0) {
				$count++;
				if ($count >= 99) return 99;
			}
			if ($cmp === 0) return $count;
		}
		// Detected version not found in history — likely older than the
		// oldest entry we tracked. Return what we counted as a lower bound.
		return $count;
	}
}
