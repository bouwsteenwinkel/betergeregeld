<?php

namespace App\Services\AccessGuard\Radar\Vulnerabilities;

use App\Models\AccessGuard\Radar\SoftwareCatalogEntry;
use App\Models\AccessGuard\Radar\Vulnerability;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pulls vulnerability data from OSV.dev — Google's open vulnerability
 * database that aggregates GHSA, RustSec, distro advisories, and more.
 *
 * Strategy:
 *   - Iterate every (vendor, product) in our catalog
 *   - Map → OSV ecosystem + package-name (best effort; OSV is strong on
 *     language packages, weaker on OS-level products)
 *   - POST to https://api.osv.dev/v1/query  with package + (no version)
 *     to get all advisories that reference the package, regardless of
 *     which specific version
 *   - Per OSV vulnerability, flatten the affected[].ranges[].events[]
 *     into a single ">=A <B"-style string we can store + parse later
 *
 * OSV's coverage of WordPress / nginx / Apache core is limited — those
 * are tracked in NVD via CPE, which is a separate ingestion job for a
 * later phase. This ingestor still gives us GHSA-sourced advisories for
 * any of those that surface there (e.g. WordPress themes/plugins on
 * Packagist, nginx-related Go modules).
 *
 * Idempotent on (source='osv', source_id=osv_id).
 */
final class OsvIngestor implements VulnerabilityFeedIngestor
{
	private const ENDPOINT_QUERY = 'https://api.osv.dev/v1/query';
	private const ENDPOINT_GET   = 'https://api.osv.dev/v1/vulns/';
	private const SOURCE = 'osv';

	/**
	 * Our (vendor, product) → OSV (ecosystem, package-name).
	 * Add a row here when a new product enters the catalog.
	 */
	private const ECOSYSTEM_MAP = [
		'wordpress|wordpress' => ['Packagist', 'wordpress/wordpress'],
		// Most OS / runtime entries below are best-effort. OSV's coverage
		// for nginx/php/apache via CPE-style ecosystems is limited; rows
		// returned will mostly come from GHSA / SwiftURL.
		'php|php'             => ['SwiftURL', 'php/php-src'],
		'nginx|nginx'         => ['SwiftURL', 'nginx/nginx'],
		'nodejs|nodejs'       => ['npm', 'node'],
	];

	public function __construct(private readonly HttpFactory $http) {}

	public function name(): string
	{
		return 'osv.dev';
	}

	public function ingest(): int
	{
		$verify = class_exists(CaBundle::class)
			? CaBundle::getSystemCaRootBundlePath()
			: true;

		$total = 0;
		$products = SoftwareCatalogEntry::query()
			->orderBy('vendor')->orderBy('product')
			->get(['vendor', 'product']);

		foreach ($products as $cat) {
			$key = "{$cat->vendor}|{$cat->product}";
			if (! isset(self::ECOSYSTEM_MAP[$key])) {
				continue;
			}
			[$ecosystem, $packageName] = self::ECOSYSTEM_MAP[$key];
			$total += $this->ingestForPackage($cat->vendor, $cat->product, $ecosystem, $packageName, $verify);
			usleep(300_000);
		}
		return $total;
	}

	private function ingestForPackage(
		string $vendor,
		string $product,
		string $ecosystem,
		string $packageName,
		string|bool $verify,
	): int {
		try {
			$resp = $this->http
				->withUserAgent('AccessGuardRadar/1.0 (+https://betergeregeld.nl/radar)')
				->withOptions(['verify' => $verify])
				->timeout(20)
				->post(self::ENDPOINT_QUERY, [
					'package' => ['name' => $packageName, 'ecosystem' => $ecosystem],
				]);

			if (! $resp->successful()) {
				Log::warning('radar.osv.http_error', [
					'status' => $resp->status(), 'package' => "{$ecosystem}/{$packageName}",
				]);
				return 0;
			}

			$vulns = $resp->json('vulns') ?? [];
			$count = 0;
			foreach ($vulns as $vuln) {
				$id = $vuln['id'] ?? null;
				if (! $id) continue;

				// Each list item is a stub — fetch full record only if we
				// don't have ranges already inline. /v1/query returns
				// summaries; fields like `affected` are populated, but
				// some advisories ship full data only on /v1/vulns/{id}.
				$full = $vuln;
				if (! isset($vuln['affected']) || $vuln['affected'] === []) {
					try {
						$detail = $this->http
							->withOptions(['verify' => $verify])
							->timeout(15)
							->get(self::ENDPOINT_GET . $id);
						if ($detail->successful()) {
							$full = $detail->json() ?: $vuln;
						}
					} catch (Throwable) {
						// fall through with stub
					}
				}

				if ($this->upsert($id, $vendor, $product, $full)) {
					$count++;
				}
				usleep(150_000);
			}
			return $count;
		} catch (Throwable $e) {
			Log::warning('radar.osv.exception', [
				'error' => $e->getMessage(), 'package' => "{$ecosystem}/{$packageName}",
			]);
			return 0;
		}
	}

	private function upsert(string $osvId, string $vendor, string $product, array $vuln): bool
	{
		$cveId = $this->extractCveId($vuln);
		[$range, $fixed] = $this->extractRangeAndFix($vuln, $product);
		$severity = $this->extractSeverity($vuln);
		$cvss = $this->extractCvssScore($vuln);

		Vulnerability::query()->updateOrCreate(
			['source' => self::SOURCE, 'source_id' => $osvId],
			[
				'cve_id' => $cveId,
				'vendor' => $vendor,
				'product' => $product,
				'affected_range' => $range,
				'fixed_version' => $fixed,
				'cvss_score' => $cvss,
				'severity' => $severity,
				'impact_category' => 'unknown',
				'exploit_available' => false,
				'actively_exploited' => false,
				'cisa_kev' => false,
				'patch_available' => $fixed !== null,
				'published_at' => isset($vuln['published']) ? $this->safeDate($vuln['published']) : null,
				'updated_at_source' => isset($vuln['modified']) ? $this->safeDate($vuln['modified']) : null,
				'description' => trim((string) ($vuln['summary'] ?? $vuln['details'] ?? $osvId)),
				'references_json' => collect($vuln['references'] ?? [])
					->pluck('url')->filter()->values()->all(),
			],
		);
		return true;
	}

	private function extractCveId(array $vuln): ?string
	{
		foreach (($vuln['aliases'] ?? []) as $alias) {
			if (preg_match('~^CVE-\d{4}-\d{4,}$~', (string) $alias)) {
				return $alias;
			}
		}
		return null;
	}

	/** @return array{0:string,1:?string} [range_string, fixed_version] */
	private function extractRangeAndFix(array $vuln, string $product): array
	{
		// OSV's affected[] may have multiple package entries; pick the one
		// matching our product name (case-insensitive). Otherwise fall back
		// to the first entry — at this layer we already filtered by package
		// in the query, so multiple entries here usually = same product.
		$affected = $vuln['affected'] ?? [];
		if ($affected === []) return ['*', null];

		$picked = $affected[0];
		foreach ($affected as $a) {
			$name = $a['package']['name'] ?? '';
			if (strcasecmp($name, $product) === 0 || str_ends_with(strtolower($name), '/' . strtolower($product))) {
				$picked = $a;
				break;
			}
		}

		$introduced = null;
		$fixed = null;
		foreach ($picked['ranges'] ?? [] as $range) {
			foreach ($range['events'] ?? [] as $event) {
				if (isset($event['introduced'])) $introduced ??= $event['introduced'];
				if (isset($event['fixed']))      $fixed      ??= $event['fixed'];
			}
		}

		// Build a vers/semver-style range string. Empty introduced ('0')
		// means "from the beginning"; we encode it as ">=0".
		$pieces = [];
		$pieces[] = '>=' . ($introduced ?: '0');
		if ($fixed) $pieces[] = '<' . $fixed;

		return [implode(' ', $pieces), $fixed];
	}

	private function extractSeverity(array $vuln): string
	{
		$score = $this->extractCvssScore($vuln);
		if ($score === null) return 'medium';
		return match (true) {
			$score >= 9.0 => 'critical',
			$score >= 7.0 => 'high',
			$score >= 4.0 => 'medium',
			default       => 'low',
		};
	}

	private function extractCvssScore(array $vuln): ?float
	{
		// OSV severity[] is an array of {type, score} entries; CVSS_V3
		// is the type we want. The `score` is a vector string, not a
		// number, so we have to parse the base-score out of it.
		foreach ($vuln['severity'] ?? [] as $sev) {
			$type = $sev['type'] ?? '';
			$score = $sev['score'] ?? '';
			if (! str_starts_with($type, 'CVSS')) continue;
			// OSV stores the full vector e.g. "CVSS:3.1/AV:N/AC:L/.../E:U"
			// — we don't compute the base score here, just match a
			// numeric prefix if one is present. Otherwise null.
			if (is_numeric($score)) return (float) $score;
		}
		// Some advisories include database_specific.cwe_score etc. — skip.
		return null;
	}

	private function safeDate(string $iso): ?string
	{
		try {
			return Carbon::parse($iso)->toDateString();
		} catch (Throwable) {
			return null;
		}
	}
}
