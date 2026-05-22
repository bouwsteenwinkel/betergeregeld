<?php

namespace App\Services\AccessGuard\Radar\Catalog;

use App\Models\AccessGuard\Radar\SoftwareCatalogEntry;
use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Pulls the current WordPress core release from wordpress.org's public
 * version-check endpoint:
 *   https://api.wordpress.org/core/version-check/1.7/
 *
 * That JSON response includes the current stable in `offers[0].current`,
 * plus a list of "previous" / "secure" versions we use to populate
 * release_history.
 *
 * No auth, no rate-limit signalling — the endpoint is hit by every WP
 * install in the world many times a day, so a daily sync from us is fine.
 */
final class WordPressCoreCatalogIngestor implements CatalogIngestor
{
	private const ENDPOINT = 'https://api.wordpress.org/core/version-check/1.7/';
	private const VENDOR = 'wordpress';
	private const PRODUCT = 'wordpress';
	private const SOURCE = 'wp_api';

	public function __construct(private readonly HttpFactory $http) {}

	public function name(): string
	{
		return 'wordpress.core';
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
				->timeout(15)
				->get(self::ENDPOINT);

			if (! $resp->successful()) {
				return $this->recordFailure("HTTP {$resp->status()} from wordpress.org");
			}

			$json = $resp->json();
			$offers = $json['offers'] ?? [];
			if ($offers === []) {
				return $this->recordFailure('wordpress.org returned no offers');
			}

			$latest = $offers[0]['current'] ?? null;
			if (! $latest) {
				return $this->recordFailure('No current version in offers[0]');
			}

			// The 1.7 endpoint also lists older "secure" versions per branch
			// in offers[1..n]; collect them as the release history. wordpress.org
			// orders newest-first which matches the contract on the catalog row.
			$history = [];
			foreach ($offers as $offer) {
				if (! empty($offer['current'])) {
					$history[] = ['version' => (string) $offer['current']];
				}
			}

			SoftwareCatalogEntry::query()->updateOrCreate(
				['vendor' => self::VENDOR, 'product' => self::PRODUCT],
				[
					'latest_stable_version' => $latest,
					'source' => self::SOURCE,
					'release_history' => $history,
					'product_url' => 'https://wordpress.org/download/releases/',
					'last_synced_at' => Carbon::now(),
					'last_sync_error' => null,
				],
			);
			return 1;
		} catch (Throwable $e) {
			return $this->recordFailure($e->getMessage());
		}
	}

	private function recordFailure(string $message): int
	{
		// We still touch last_sync_error so the catalog page can show
		// "tried at HH:MM, failed because X" rather than going silent.
		SoftwareCatalogEntry::query()->updateOrCreate(
			['vendor' => self::VENDOR, 'product' => self::PRODUCT],
			[
				'source' => self::SOURCE,
				'last_synced_at' => Carbon::now(),
				'last_sync_error' => $message,
			],
		);
		return 0;
	}
}
