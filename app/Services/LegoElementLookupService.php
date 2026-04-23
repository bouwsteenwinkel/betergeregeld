<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Looks up a LEGO element by its Element ID (the number printed on
 * LEGO Pick-a-Brick slips). Results are cached in lego_element_map;
 * on a cache miss or stale row the service enriches via Rebrickable.
 *
 * Rebrickable reference: GET /api/v3/lego/elements/{element_id}/
 *
 * A negative cache row (part_num=null, rb_cached_at set) is written
 * for 404s so repeated lookups for junk IDs don't hit the API.
 */
class LegoElementLookupService
{
	private const TABLE = 'lego_element_map';

	public function __construct() {}

	/**
	 * @return array{found:bool, element_id:string, part_num:?string, name:?string,
	 *   color_id:?int, color_name:?string, color_rgb:?string,
	 *   img_url:?string, rebrickable_url:?string, bricklink_url:string,
	 *   from_cache:bool, cached_at:?string}
	 */
	public function lookup(string $rawInput): array
	{
		$elementId = $this->normalize($rawInput);
		if ($elementId === '') {
			return $this->notFound('');
		}

		$row = DB::table(self::TABLE)->where('element_id', $elementId)->first();
		$fresh = $this->isFresh($row);

		if (! $fresh) {
			try {
				$row = $this->enrichFromRebrickable($elementId, $row) ?? $row;
			} catch (Throwable $e) {
				// On API failure we fall back to whatever we have in DB,
				// even if stale — so a dead API doesn't break the tool.
				Log::warning('lego-lookup: rebrickable enrichment failed', [
					'element_id' => $elementId,
					'error' => $e->getMessage(),
				]);
			}
		}

		return $this->formatResult($elementId, $row, fromCache: $fresh);
	}

	private function normalize(string $v): string
	{
		return preg_replace('/\D+/', '', $v) ?? '';
	}

	private function isFresh(?object $row): bool
	{
		if ($row === null || empty($row->rb_cached_at)) {
			return false;
		}
		$cacheDays = (int) config('lego.lookup.cache_days', 30);
		$cached = CarbonImmutable::parse($row->rb_cached_at);
		return $cached->greaterThan(CarbonImmutable::now()->subDays($cacheDays));
	}

	private function enrichFromRebrickable(string $elementId, ?object $existing): ?object
	{
		$apiKey = config('lego.rebrickable.api_key');
		if (! $apiKey) {
			return null;
		}

		$baseUrl = rtrim((string) config('lego.rebrickable.base_url'), '/');
		$timeout = (int) config('lego.lookup.timeout', 8);

		$resp = Http::timeout($timeout)
			->withHeaders([
				'Accept' => 'application/json',
				'Authorization' => 'key ' . $apiKey,
				'User-Agent' => 'BeterGeregeldICT/2.0',
			])
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->get($baseUrl . '/api/v3/lego/elements/' . rawurlencode($elementId) . '/');

		if ($resp->status() === 404) {
			$this->writeNegativeCache($elementId);
			return (object) [
				'element_id' => (int) $elementId,
				'part_num' => null,
				'rb_part_num' => null,
				'rb_color_id' => null,
				'color_name' => null,
				'rb_name' => null,
				'rb_part_img_url' => null,
				'rb_part_img_local' => null,
				'rb_cached_at' => CarbonImmutable::now()->toDateTimeString(),
				'rb_color_name' => null,
			];
		}

		if (! $resp->successful()) {
			return $existing;
		}

		$json = $resp->json();
		$part = $json['part'] ?? [];
		$color = $json['color'] ?? [];

		$payload = [
			'element_id' => (int) $elementId,
			'part_num' => $part['part_num'] ?? null,
			'rb_part_num' => $part['part_num'] ?? null,
			'rb_color_id' => isset($color['id']) ? (int) $color['id'] : null,
			'color_name' => $color['name'] ?? null,
			'rb_color_name' => $color['name'] ?? null,
			'rb_name' => $part['name'] ?? null,
			'rb_part_img_url' => $json['element_img_url'] ?? ($part['part_img_url'] ?? null),
			'rb_cached_at' => CarbonImmutable::now()->toDateTimeString(),
			'updated_at' => CarbonImmutable::now()->toDateTimeString(),
		];

		if ($existing) {
			DB::table(self::TABLE)->where('element_id', $elementId)->update($payload);
		} else {
			$payload['created_at'] = CarbonImmutable::now()->toDateTimeString();
			DB::table(self::TABLE)->insert($payload);
		}

		return (object) array_merge((array) ($existing ?? new \stdClass), $payload);
	}

	private function writeNegativeCache(string $elementId): void
	{
		$now = CarbonImmutable::now()->toDateTimeString();
		DB::table(self::TABLE)->updateOrInsert(
			['element_id' => $elementId],
			['rb_cached_at' => $now, 'updated_at' => $now],
		);
	}

	private function formatResult(string $elementId, ?object $row, bool $fromCache): array
	{
		$found = $row && ! empty($row->rb_name);

		return [
			'found' => $found,
			'element_id' => $elementId,
			'part_num' => $row->part_num ?? null,
			'name' => $row->rb_name ?? null,
			'color_id' => isset($row->rb_color_id) ? (int) $row->rb_color_id : null,
			'color_name' => $row->color_name ?? ($row->rb_color_name ?? null),
			'color_rgb' => null,
			'img_url' => $row->rb_part_img_url ?? null,
			'rebrickable_url' => ! empty($row->rb_part_num)
				? 'https://rebrickable.com/parts/' . rawurlencode((string) $row->rb_part_num) . '/'
				: null,
			'bricklink_url' => 'https://www.bricklink.com/v2/catalog/catalogitem.page?E=' . rawurlencode($elementId),
			'from_cache' => $fromCache,
			'cached_at' => $row->rb_cached_at ?? null,
		];
	}

	private function notFound(string $elementId): array
	{
		return [
			'found' => false,
			'element_id' => $elementId,
			'part_num' => null,
			'name' => null,
			'color_id' => null,
			'color_name' => null,
			'color_rgb' => null,
			'img_url' => null,
			'rebrickable_url' => null,
			'bricklink_url' => $elementId
				? 'https://www.bricklink.com/v2/catalog/catalogitem.page?E=' . rawurlencode($elementId)
				: '',
			'from_cache' => false,
			'cached_at' => null,
		];
	}
}
