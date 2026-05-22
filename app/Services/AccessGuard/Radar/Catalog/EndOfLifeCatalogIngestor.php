<?php

namespace App\Services\AccessGuard\Radar\Catalog;

use App\Models\AccessGuard\Radar\SoftwareCatalogEntry;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Pulls latest-stable + EOL data from endoflife.date for the runtimes
 * and webservers we expect fingerprinters to find. Each "product" in
 * endoflife's parlance is a slug like `php`, `nodejs`, `nginx`, `apache`,
 * `mariadb`. We map their slug → our (vendor, product) pair.
 *
 * One HTTP call per product (~5-10 total) — well under any reasonable
 * rate limit. JSON shape:
 *   GET https://endoflife.date/api/v1/products/<slug>
 *   {
 *     result: {
 *       releases: [
 *         { name: "8.3", isMaintained: true, eolFrom: "2027-12-31",
 *           latest: { name: "8.3.10", date: "2026-04-01", ... } },
 *         ...
 *       ],
 *       links: { releasePolicy: "...", ... }
 *     }
 *   }
 */
final class EndOfLifeCatalogIngestor implements CatalogIngestor
{
	private const SOURCE = 'endoflife_date';

	/**
	 * endoflife.date slug → [vendor, product] used in our catalog. Adding
	 * a row here is the only step needed to start tracking a new runtime.
	 */
	private const PRODUCTS = [
		'php'     => ['php', 'php'],
		'nodejs'  => ['nodejs', 'nodejs'],
		'nginx'   => ['nginx', 'nginx'],
		'apache'  => ['apache', 'httpd'],
		'mariadb' => ['mariadb', 'mariadb'],
		'mysql'   => ['mysql', 'mysql'],
		'wordpress' => ['wordpress', 'wordpress'],
	];

	public function __construct(private readonly HttpFactory $http) {}

	public function name(): string
	{
		return 'endoflife.date';
	}

	public function ingest(): int
	{
		$ok = 0;
		foreach (self::PRODUCTS as $slug => [$vendor, $product]) {
			if ($this->ingestOne($slug, $vendor, $product)) {
				$ok++;
			}
			// Be polite — endoflife.date is a community resource.
			usleep(200_000);
		}
		return $ok;
	}

	private function ingestOne(string $slug, string $vendor, string $product): bool
	{
		try {
			$verify = class_exists(CaBundle::class)
				? CaBundle::getSystemCaRootBundlePath()
				: true;

			$resp = $this->http
				->withUserAgent('AccessGuardRadar/1.0 (+https://betergeregeld.nl/radar)')
				->withOptions(['verify' => $verify])
				->timeout(15)
				->get("https://endoflife.date/api/v1/products/{$slug}");

			if (! $resp->successful()) {
				$this->recordFailure($vendor, $product, "HTTP {$resp->status()} for {$slug}");
				return false;
			}

			$json = $resp->json();
			$releases = $json['result']['releases'] ?? null;
			if (! is_array($releases) || $releases === []) {
				$this->recordFailure($vendor, $product, "No releases in response for {$slug}");
				return false;
			}

			$latestStable = null;
			$eolDates = [];
			$history = [];

			foreach ($releases as $release) {
				$name = $release['name'] ?? null;
				if (! $name) continue;

				if (! empty($release['eolFrom'])) {
					$eolDates[(string) $name] = (string) $release['eolFrom'];
				}

				$latestRelease = $release['latest']['name'] ?? null;
				if ($latestRelease) {
					$history[] = ['version' => (string) $latestRelease];
					// First non-EOL release in the API's order = latest stable.
					if ($latestStable === null && (! empty($release['isMaintained']))) {
						$latestStable = (string) $latestRelease;
					}
				}
			}

			SoftwareCatalogEntry::query()->updateOrCreate(
				['vendor' => $vendor, 'product' => $product],
				[
					'latest_stable_version' => $latestStable,
					'source' => self::SOURCE,
					'release_history' => $history,
					'eol_dates' => $eolDates,
					'product_url' => "https://endoflife.date/{$slug}",
					'last_synced_at' => Carbon::now(),
					'last_sync_error' => null,
				],
			);
			return true;
		} catch (Throwable $e) {
			$this->recordFailure($vendor, $product, $e->getMessage());
			return false;
		}
	}

	private function recordFailure(string $vendor, string $product, string $message): void
	{
		SoftwareCatalogEntry::query()->updateOrCreate(
			['vendor' => $vendor, 'product' => $product],
			[
				'source' => self::SOURCE,
				'last_synced_at' => Carbon::now(),
				'last_sync_error' => $message,
			],
		);
	}
}
