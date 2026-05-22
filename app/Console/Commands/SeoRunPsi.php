<?php

namespace App\Console\Commands;

use App\Models\Seo\SeoProperty;
use App\Models\Seo\SeoPsiDaily;
use App\Services\Seo\PageSpeedInsightsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Run PageSpeed Insights voor de top-3 most-clicked URLs per actieve
 * property, mobile + desktop. Slaat genormaliseerde metrics op in
 * seo_psi_daily. Idempotent per (property, url, strategy, date) —
 * herhaling op dezelfde dag ververst de meting.
 *
 * Bij geen GSC-clicks (zoals nu — 0 clicks afgelopen 7d) valt 'ie terug
 * op de top-3 most-impressed URLs zodat we toch een baseline meten.
 */
class SeoRunPsi extends Command
{
	protected $signature = 'seo:run-psi
		{--property= : Alleen één property-id}
		{--url= : Test één specifieke URL (skipt selectie)}
		{--top=3 : Aantal URLs per property}';

	protected $description = 'Meet Core Web Vitals + Lighthouse-scores voor de top-N URLs per property.';

	public function handle(PageSpeedInsightsClient $psi): int
	{
		$top = max(1, (int) $this->option('top'));
		$today = now()->toDateString();

		$query = SeoProperty::query()->where('is_active', true);
		if ($pid = $this->option('property')) {
			$query->where('id', (int) $pid);
		}
		$properties = $query->get();
		if ($properties->isEmpty()) {
			$this->warn('Geen actieve properties.');
			return self::SUCCESS;
		}

		$strategies = ['mobile', 'desktop'];
		$totalOk = 0;
		$totalFail = 0;

		foreach ($properties as $prop) {
			$urls = $this->selectUrls($prop->id, $top, $this->option('url'));
			if (empty($urls)) {
				$this->warn("[{$prop->label}] geen URLs te meten.");
				continue;
			}
			$this->info("[{$prop->label}] " . count($urls) . ' URL(s) × ' . count($strategies) . ' strategies');

			foreach ($urls as $url) {
				foreach ($strategies as $strategy) {
					$m = $psi->fetch($url, $strategy);
					$row = [
						'property_id'          => $prop->id,
						'url'                  => $url,
						'strategy'             => $strategy,
						'date'                 => $today,
					];
					if ($m === null) {
						$row['error_message'] = mb_substr((string) $psi->lastError, 0, 500);
						$this->error("  ✕ {$strategy} {$url}: {$psi->lastError}");
						$totalFail++;
					} else {
						$row = array_merge($row, $m);
						$this->line(sprintf(
							'  ✓ %-7s %-50s perf=%-3s lcp=%-5s cls=%s',
							$strategy, mb_strimwidth($url, 0, 50, '…'),
							$m['performance_score'] ?? '-',
							$m['lcp_ms'] ?? '-',
							$m['cls'] !== null ? number_format($m['cls'], 3) : '-'
						));
						$totalOk++;
					}

					DB::table('seo_psi_daily')->upsert(
						[$row],
						['property_id', 'url', 'strategy', 'date'],
						array_keys(array_diff_key($row, array_flip(['property_id', 'url', 'strategy', 'date'])))
					);
				}
			}
		}

		$this->newLine();
		$this->info("Klaar: {$totalOk} OK, {$totalFail} fout.");
		return $totalFail === 0 ? self::SUCCESS : self::FAILURE;
	}

	/**
	 * Top-N URLs per property: meest gekliket de laatste 30 dagen;
	 * fallback bij 0 clicks → meest geïmpressed; fallback bij 0 records
	 * → property-home (sc-domain → https://host/, prefix-property zelf).
	 */
	private function selectUrls(int $propertyId, int $top, ?string $override): array
	{
		if ($override) {
			return [$override];
		}

		$since = now()->subDays(30)->toDateString();

		$urls = DB::table('seo_query_daily')
			->select('page')
			->where('property_id', $propertyId)
			->where('date', '>=', $since)
			->groupBy('page')
			->orderByRaw('SUM(clicks) DESC, SUM(impressions) DESC')
			->limit($top)
			->pluck('page')
			->all();

		if (!empty($urls)) {
			return $urls;
		}

		// Fallback: alleen impressies
		$urls = DB::table('seo_query_daily')
			->select('page')
			->where('property_id', $propertyId)
			->where('date', '>=', $since)
			->groupBy('page')
			->orderByRaw('SUM(impressions) DESC')
			->limit($top)
			->pluck('page')
			->all();

		if (!empty($urls)) {
			return $urls;
		}

		// Laatste fallback: home van de property
		$prop = SeoProperty::find($propertyId);
		if (!$prop) {
			return [];
		}
		if (str_starts_with($prop->site_url, 'sc-domain:')) {
			$host = substr($prop->site_url, strlen('sc-domain:'));
			return ['https://' . $host . '/'];
		}
		return [rtrim($prop->site_url, '/') . '/'];
	}
}
