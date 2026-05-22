<?php

namespace App\Services\AccessGuard\Radar\Vulnerabilities;

use App\Models\AccessGuard\Radar\Vulnerability;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pulls CISA's "Known Exploited Vulnerabilities" feed — a single JSON
 * download (~3 MB, updated several times a week). Each entry tells us
 * "this CVE is being actively exploited in the wild right now", which
 * is the strongest possible signal for risk scoring.
 *
 * Endpoint:
 *   https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json
 *
 * Per row we get: cveID, vendorProject, product, dateAdded, dueDate,
 * shortDescription. We don't get a CVSS score or affected-version
 * range — KEV is a "do this now" signal, not a comprehensive CVE
 * feed. Matching is therefore product-level (any version of vendor X's
 * product Y on the KEV list = elevated risk). OSV/NVD provide the
 * version-specific data on top.
 *
 * Idempotent on (source, source_id) where source='cisa_kev' and
 * source_id=cveID.
 */
final class CisaKevIngestor implements VulnerabilityFeedIngestor
{
	private const ENDPOINT = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';
	private const SOURCE = 'cisa_kev';

	public function __construct(
		private readonly HttpFactory $http,
		private readonly VendorProductNormalizer $normaliser,
	) {}

	public function name(): string
	{
		return 'cisa.kev';
	}

	public function ingest(): int
	{
		try {
			$verify = class_exists(CaBundle::class)
				? CaBundle::getSystemCaRootBundlePath()
				: true;

			$resp = $this->http
				->withUserAgent('AccessGuardRadar/1.0 (+https://betergeregeld.nl/radar)')
				->withOptions(['verify' => $verify])
				->timeout(60)
				->get(self::ENDPOINT);

			if (! $resp->successful()) {
				Log::warning('radar.kev.http_error', ['status' => $resp->status()]);
				return 0;
			}

			$rows = $resp->json('vulnerabilities') ?? [];
			$count = 0;
			$skipped = 0;
			foreach ($rows as $row) {
				$cveId = $row['cveID'] ?? null;
				if (! $cveId) continue;

				[$vendor, $product] = $this->normaliser->normalise(
					(string) ($row['vendorProject'] ?? ''),
					(string) ($row['product'] ?? ''),
				);

				// vendor / product columns are VARCHAR(120). Some KEV rows
				// (e.g. Qualcomm Snapdragon dumps) cram a comma-separated
				// list into product, easily blowing the limit. Truncate
				// rather than reject — the matcher hits exact strings, so
				// a truncated "snapdragon auto, snapdragon compute, …"
				// becomes a search-key we'll never collide with anyway.
				$vendor = mb_substr($vendor, 0, 120);
				$product = mb_substr($product, 0, 120);

				try {
				Vulnerability::query()->updateOrCreate(
					['source' => self::SOURCE, 'source_id' => $cveId],
					[
						'cve_id' => $cveId,
						'vendor' => $vendor,
						'product' => $product,
						// KEV doesn't ship version ranges — '*' = any version.
						// Matcher treats this as a low-confidence product-only hit.
						'affected_range' => '*',
						'fixed_version' => null,
						'cvss_score' => null,
						'severity' => 'high',
						'impact_category' => $this->guessImpact((string) ($row['shortDescription'] ?? '')),
						'exploit_available' => true,
						'actively_exploited' => true,
						'cisa_kev' => true,
						'patch_available' => ! empty($row['requiredAction']),
						'published_at' => isset($row['dateAdded']) ? Carbon::parse($row['dateAdded'])->toDateString() : null,
						'updated_at_source' => isset($row['dateAdded']) ? Carbon::parse($row['dateAdded'])->toDateString() : null,
						'description' => trim((string) ($row['shortDescription'] ?? '')),
						'references_json' => array_filter([
							$row['notes'] ?? null,
							"https://nvd.nist.gov/vuln/detail/{$cveId}",
						]),
					],
				);
				$count++;
				} catch (Throwable $rowError) {
					// Single-row failure (truncation, encoding, weird date)
					// — log + continue so the whole feed isn't aborted.
					$skipped++;
					Log::warning('radar.kev.row_skipped', [
						'cve_id' => $cveId,
						'error' => $rowError->getMessage(),
					]);
				}
			}
			Log::info('radar.kev.ingest_done', ['rows' => $count, 'skipped' => $skipped]);
			return $count;
		} catch (Throwable $e) {
			Log::warning('radar.kev.exception', ['error' => $e->getMessage()]);
			return 0;
		}
	}

	/**
	 * Crude keyword-based impact category. Real-world CVE descriptions
	 * follow predictable patterns; this catches the obvious cases and
	 * defaults to 'unknown' for anything ambiguous.
	 */
	private function guessImpact(string $description): string
	{
		$d = strtolower($description);
		return match (true) {
			str_contains($d, 'remote code execution') || str_contains($d, ' rce ') => 'rce',
			str_contains($d, 'sql injection')                                       => 'sqli',
			str_contains($d, 'cross-site scripting') || str_contains($d, ' xss ')  => 'xss',
			str_contains($d, 'authentication bypass')                              => 'auth_bypass',
			str_contains($d, 'privilege escalation')                               => 'privesc',
			str_contains($d, 'server-side request forgery') || str_contains($d, ' ssrf ') => 'ssrf',
			str_contains($d, 'denial of service') || str_contains($d, ' dos ')     => 'dos',
			str_contains($d, 'data leak') || str_contains($d, 'information disclosure') => 'data_leak',
			default                                                                => 'unknown',
		};
	}
}
