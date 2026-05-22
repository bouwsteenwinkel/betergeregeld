<?php

namespace App\Services\Seo;

use App\Models\Seo\SeoImportsLog;
use App\Models\Seo\SeoProperty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Idempotente per-dag import van GSC searchAnalytics-data naar
 * seo_query_daily. Patroon overgenomen van BSW V3's gsc_import_day().
 *
 * - Pagineert door rowLimit (5000) tot < rowLimit terugkomt (max 6 pages).
 * - INSERT ... ON DUPLICATE KEY UPDATE → herhaalde import ververst.
 * - Schrijft een seo_imports_log-rij per poging.
 */
class GscDailyImporter
{
	public const ROW_LIMIT = 5000;
	public const MAX_PAGES = 6; // ~30k rijen per dag voor onze schaal ruim genoeg

	public function __construct(private readonly GoogleSearchConsoleClient $gsc) {}

	/**
	 * Importeer één dag voor één property. Idempotent: bestaande rijen
	 * worden geüpdatet, niet gedupliceerd.
	 *
	 * @return array{ok:bool, rows:int, message?:string}
	 */
	public function importDay(int $propertyId, Carbon|string $date): array
	{
		$date = $date instanceof Carbon ? $date->toDateString() : (string) $date;
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			return ['ok' => false, 'rows' => 0, 'message' => 'invalid date format'];
		}

		$property = SeoProperty::find($propertyId);
		if (!$property) {
			return ['ok' => false, 'rows' => 0, 'message' => 'unknown property'];
		}

		$log = SeoImportsLog::create([
			'property_id' => $propertyId,
			'target_date' => $date,
			'status'      => 'pending',
			'started_at'  => now(),
		]);

		$total = 0;
		try {
			for ($page = 0; $page < self::MAX_PAGES; $page++) {
				$resp = $this->gsc->searchAnalyticsQuery($property->site_url, [
					'startDate'  => $date,
					'endDate'    => $date,
					'dimensions' => ['query', 'page'],
					'rowLimit'   => self::ROW_LIMIT,
					'startRow'   => $page * self::ROW_LIMIT,
					'dataState'  => 'final',
				]);

				if ($resp['status'] !== 200) {
					$err = $resp['json']['error']['message'] ?? ('HTTP ' . $resp['status']);
					$this->finishLog($log, 'failed', $total, $err);
					$property->update(['last_import_error' => mb_substr($err, 0, 500)]);
					return ['ok' => false, 'rows' => $total, 'message' => $err];
				}

				$rows = $resp['json']['rows'] ?? [];
				if (empty($rows)) {
					break;
				}

				$batch = [];
				$now = now();
				foreach ($rows as $r) {
					$keys = $r['keys'] ?? ['', ''];
					$batch[] = [
						'property_id' => $propertyId,
						'date'        => $date,
						'query'       => mb_substr((string) ($keys[0] ?? ''), 0, 500),
						'page'        => mb_substr((string) ($keys[1] ?? ''), 0, 500),
						'clicks'      => (int) ($r['clicks'] ?? 0),
						'impressions' => (int) ($r['impressions'] ?? 0),
						'ctr'         => (float) ($r['ctr'] ?? 0),
						'position'    => (float) ($r['position'] ?? 0),
						'created_at'  => $now,
					];
				}

				DB::table('seo_query_daily')->upsert(
					$batch,
					// Unique-key kan niet via Eloquent upsert; doe expliciet:
					['property_id', 'date', 'row_hash'],
					['clicks', 'impressions', 'ctr', 'position']
				);

				$total += count($rows);

				if (count($rows) < self::ROW_LIMIT) {
					break; // laatste page
				}
			}

			$this->finishLog($log, 'success', $total);
			$property->update([
				'last_imported_date' => $date,
				'last_import_error'  => null,
			]);

			return ['ok' => true, 'rows' => $total];
		} catch (Throwable $e) {
			$this->finishLog($log, 'failed', $total, $e->getMessage());
			$property->update(['last_import_error' => mb_substr($e->getMessage(), 0, 500)]);
			return ['ok' => false, 'rows' => $total, 'message' => $e->getMessage()];
		}
	}

	private function finishLog(SeoImportsLog $log, string $status, int $rows, ?string $error = null): void
	{
		$log->update([
			'status'        => $status,
			'rows_imported' => $rows,
			'error_message' => $error !== null ? mb_substr($error, 0, 500) : null,
			'finished_at'   => now(),
		]);
	}
}
