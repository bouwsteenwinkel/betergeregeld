<?php

namespace App\Services\Rankdata;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Bouwt de rapport-data voor één klant (tenant): per site SEO (30d),
 * PageSpeed (laatste meting) en uptime (7d). Leest dezelfde tabellen als het
 * klant-dashboard (RankdataDashboardController) zodat de cijfers overeenkomen.
 */
class RankdataReport
{
	public function forTenant(Tenant $tenant, int $days = 30): array
	{
		$sites = DB::table('seo_properties')
			->where('tenant_id', $tenant->id)
			->where('is_active', 1)
			->orderBy('label')
			->get();

		$out = [];
		foreach ($sites as $site) {
			$check = DB::table('monitor_checks')->where('property_id', $site->id)->first();
			$out[] = [
				'label'    => (string) $site->label,
				'site_url' => (string) $site->site_url,
				'seo'      => $this->seo((int) $site->id, $days),
				'psi'      => $this->psi((int) $site->id),
				'uptime'   => $check ? $this->uptime((string) $check->id, 7) : null,
				'quality'  => $this->quality((int) $site->id),
			];
		}

		return [
			'tenant'       => (string) $tenant->name,
			'agency'       => $tenant->agency?->name,
			'days'         => $days,
			'generated_at' => now(),
			'sites'        => $out,
		];
	}

	private function seo(int $propertyId, int $days): array
	{
		$since = now()->subDays($days)->toDateString();
		$base = DB::table('seo_query_daily')->where('property_id', $propertyId)->where('date', '>=', $since);

		$tot = (clone $base)->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')->first();
		$clicks = (int) ($tot->c ?? 0);
		$impr = (int) ($tot->i ?? 0);

		$daily = (clone $base)->selectRaw('date, SUM(clicks) c')
			->groupBy('date')->orderBy('date')->get()
			->map(fn ($r) => ['date' => $r->date, 'clicks' => (int) $r->c])->values()->all();
		$last7 = array_sum(array_column(array_slice($daily, -7), 'clicks'));
		$prev7 = array_sum(array_column(array_slice($daily, -14, 7), 'clicks'));
		$trend = $prev7 > 0 ? (int) round(($last7 - $prev7) / $prev7 * 100) : null;

		$topQueries = (clone $base)->select('query')
			->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')
			->groupBy('query')->orderByRaw('SUM(clicks) DESC, SUM(impressions) DESC')->limit(10)->get()
			->map(fn ($r) => ['query' => (string) $r->query, 'clicks' => (int) $r->c, 'impr' => (int) $r->i, 'pos' => round((float) $r->p, 1)])->all();

		$topPages = (clone $base)->select('page')
			->selectRaw('SUM(clicks) c, SUM(impressions) i')
			->groupBy('page')->orderByRaw('SUM(impressions) DESC')->limit(8)->get()
			->map(fn ($r) => ['page' => (string) $r->page, 'clicks' => (int) $r->c, 'impr' => (int) $r->i])->all();

		return [
			'clicks'      => $clicks,
			'impressions' => $impr,
			'ctr'         => $impr ? round($clicks / $impr * 100, 1) : 0.0,
			'position'    => round((float) ($tot->p ?? 0), 1),
			'trend'       => $trend,
			'has_data'    => $impr > 0 || $clicks > 0,
			'topQueries'  => $topQueries,
			'topPages'    => $topPages,
		];
	}

	private function psi(int $propertyId): array
	{
		$out = [];
		foreach (['mobile', 'desktop'] as $strategy) {
			$row = DB::table('seo_psi_daily')->where('property_id', $propertyId)->where('strategy', $strategy)
				->orderByDesc('date')->first();
			$out[$strategy] = $row ? [
				'performance'   => (int) $row->performance_score,
				'seo'           => (int) $row->seo_score,
				'accessibility' => (int) $row->accessibility_score,
				'lcp'           => $row->lcp_ms !== null ? round($row->lcp_ms / 1000, 1) : null,
				'cls'           => $row->cls !== null ? (float) $row->cls : null,
			] : null;
		}

		return $out;
	}

	private function quality(int $siteId): ?array
	{
		$pageIds = DB::table('monitored_pages')->where('site_id', $siteId)->pluck('id');
		if ($pageIds->isEmpty()) {
			return null;
		}

		$latest = DB::table('quality_scans')->whereIn('monitored_page_id', $pageIds)
			->where('status', 'completed')->whereNotNull('score')
			->orderByDesc('completed_at')->first();
		if (! $latest) {
			return null;
		}

		$counts = DB::table('quality_findings')->where('quality_scan_id', $latest->id)
			->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');

		$top = DB::table('quality_findings')->where('quality_scan_id', $latest->id)
			->whereIn('status', ['fail', 'warn'])
			->orderByRaw("FIELD(severity,'hoog','middel','laag'), FIELD(status,'fail','warn')")
			->limit(5)->get()
			->map(fn ($f) => ['status' => (string) $f->status, 'finding' => (string) $f->finding])->all();

		return [
			'score'      => (int) $latest->score,
			'pass'       => (int) ($counts['pass'] ?? 0),
			'warn'       => (int) ($counts['warn'] ?? 0),
			'fail'       => (int) ($counts['fail'] ?? 0),
			'scanned_at' => $latest->completed_at,
			'top'        => $top,
		];
	}

	private function uptime(string $checkId, int $days): ?array
	{
		$since = now()->subDays($days);
		$rows = DB::table('monitor_check_results')->where('check_id', $checkId)
			->where('checked_at', '>=', $since)->get();

		$total = $rows->count();
		if ($total === 0) {
			return null;
		}
		$up = $rows->where('status', 'up')->count();
		$latencies = $rows->where('status', 'up')->pluck('latency_ms')->filter()->all();

		return [
			'percent'     => round($up / $total * 100, 2),
			'samples'     => $total,
			'avg_latency' => $latencies ? (int) round(array_sum($latencies) / count($latencies)) : null,
		];
	}
}
