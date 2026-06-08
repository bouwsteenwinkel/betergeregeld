<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Leesbaar statistieken-dashboard per klant (tenant): SEO / Search Console,
 * PageSpeed en uptime. Toegankelijk voor de klant zelf, de bureau-beheerder van
 * dat bureau, en de platform-super-admin.
 */
class RankdataDashboardController extends Controller
{
	/** Nette entree: klant → eigen dashboard, bureau/super-admin → bureau-portal. */
	public function me(Request $request)
	{
		$u = $request->user();
		if ($u->isAgencyAdmin()) {
			return redirect('/bureau');
		}
		if ($u->isSuperAdmin()) {
			return redirect('/admin/clients');
		}
		abort_unless($u->tenant_id, 404);

		return $this->render($request, $u->tenant_id);
	}

	/**
	 * Oude front-end bureau-portal — vervangen door het afgeschermde /bureau
	 * Filament-panel. Redirect zodat bestaande links/bookmarks blijven werken.
	 */
	public function agency(Request $request)
	{
		$u = $request->user();
		if ($u->isAgencyAdmin()) {
			return redirect('/bureau');
		}
		if ($u->isSuperAdmin()) {
			return redirect('/admin/clients');
		}

		abort(403);
	}

	/**
	 * Route-entree. LET OP: onder de /{locale}-prefix bindt Laravel string-params
	 * POSITIONEEL — daarom moet $locale hier éérst staan, anders krijgt $tenant de
	 * locale ("nl") binnen i.p.v. de tenant-id. Zie [[feedback_laravel_string_params]].
	 */
	public function show(Request $request, string $locale, string $tenant)
	{
		return $this->render($request, $tenant);
	}

	/** Klant-direct: start een Mollie-checkout voor het Rankdata-abonnement van deze klant. */
	public function checkout(Request $request, string $locale, string $tenant)
	{
		$t = Tenant::with('agency')->findOrFail($tenant);
		$user = $request->user();
		$allowed = $user->isSuperAdmin()
			|| ($user->isAgencyAdmin() && $t->agency_id === $user->agency_id)
			|| $user->tenant_id === $t->id;
		abort_unless($allowed, 403);

		$intent = app(\App\Services\Billing\BillingService::class)->startRankdataCheckout($user, $t);

		return redirect()->away($intent->mollie_checkout_url);
	}

	/** Gedeelde dashboard-render (aangeroepen door show() én me()). */
	private function render(Request $request, string $tenant)
	{
		$t = Tenant::with('agency')->findOrFail($tenant);
		$user = $request->user();

		$allowed = $user->isSuperAdmin()
			|| ($user->isAgencyAdmin() && $t->agency_id === $user->agency_id)
			|| ($user->tenant_id === $t->id);
		abort_unless($allowed, 403);

		// Een klant kan meerdere sites (websites/applicaties) hebben. Toon de
		// gekozen site (?site=) of anders de eerste, met een siteswitcher.
		$sites = DB::table('seo_properties')->where('tenant_id', $t->id)->where('is_active', 1)
			->orderBy('id')->get();
		$selectedId = (int) $request->query('site', 0);
		$selected = $sites->firstWhere('id', $selectedId) ?: $sites->first();

		$seo = $psi = $uptime = $quality = null;
		$domain = null;
		if ($selected) {
			$domain = preg_replace('/^sc-domain:/', '', $selected->site_url);
			$seo = $this->seoStats((int) $selected->id);
			$psi = $this->psiStats((int) $selected->id);
			$check = DB::table('monitor_checks')->where('property_id', $selected->id)->first();
			$uptime = $check ? $this->uptimeStats($check->id) : null;
			$quality = $this->qualityStats((int) $selected->id);
		}

		$subscription = DB::table('tenant_subscriptions')
			->where('tenant_key', $t->id)
			->whereIn('status', ['active', 'trial', 'trialing'])
			->orderByDesc('current_period_ends_at')->first();
		$monthlyCost = app(\App\Services\Rankdata\RankdataBilling::class)->costForTenant($t);

		return view('rankdata.dashboard', [
			'tenant'  => $t,
			'agency'  => $t->agency,
			'brand'   => $t->agency?->brandColor() ?? '#0f766e',
			'sites'   => $sites,
			'selected' => $selected,
			'domain'  => $domain,
			'seo'     => $seo,
			'psi'     => $psi,
			'uptime'  => $uptime,
			'quality' => $quality,
			'subscription' => $subscription,
			'monthlyCost' => $monthlyCost,
			'canManageBilling' => $user->isSuperAdmin() || ($user->isAgencyAdmin() && $t->agency_id === $user->agency_id) || $user->tenant_id === $t->id,
			'canPickClient' => $user->isSuperAdmin() || $user->isAgencyAdmin(),
		]);
	}

	private function seoStats(int $propertyId): array
	{
		$since = now()->subDays(30)->toDateString();
		$base = DB::table('seo_query_daily')->where('property_id', $propertyId)->where('date', '>=', $since);

		$tot = (clone $base)->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')->first();
		$clicks = (int) ($tot->c ?? 0);
		$impr = (int) ($tot->i ?? 0);

		// Dagreeks voor de grafiek
		$daily = (clone $base)->selectRaw('date, SUM(clicks) c, SUM(impressions) i')
			->groupBy('date')->orderBy('date')->get()
			->map(fn ($r) => ['date' => $r->date, 'clicks' => (int) $r->c, 'impr' => (int) $r->i])->values()->all();

		// 7d-trend (laatste 7d vs de 7d ervoor)
		$last7 = array_slice($daily, -7);
		$prev7 = array_slice($daily, -14, 7);
		$sum = fn ($a, $k) => array_sum(array_column($a, $k));
		$prevClicks = $sum($prev7, 'clicks');
		$trend = $prevClicks > 0 ? round(($sum($last7, 'clicks') - $prevClicks) / $prevClicks * 100) : null;

		$topQueries = (clone $base)->select('query')
			->selectRaw('SUM(clicks) c, SUM(impressions) i, AVG(position) p')
			->groupBy('query')->orderByRaw('SUM(clicks) DESC, SUM(impressions) DESC')->limit(8)->get()
			->map(fn ($r) => ['query' => $r->query, 'clicks' => (int) $r->c, 'impr' => (int) $r->i, 'pos' => round($r->p, 1)])->all();

		$topPages = (clone $base)->select('page')
			->selectRaw('SUM(clicks) c, SUM(impressions) i')
			->groupBy('page')->orderByRaw('SUM(impressions) DESC')->limit(6)->get()
			->map(fn ($r) => ['page' => $r->page, 'clicks' => (int) $r->c, 'impr' => (int) $r->i])->all();

		return [
			'clicks' => $clicks,
			'impressions' => $impr,
			'ctr' => $impr ? round($clicks / $impr * 100, 1) : 0,
			'position' => round($tot->p ?? 0, 1),
			'trend' => $trend,
			'daily' => $daily,
			'topQueries' => $topQueries,
			'topPages' => $topPages,
		];
	}

	private function psiStats(int $propertyId): array
	{
		$out = [];
		foreach (['mobile', 'desktop'] as $strategy) {
			$row = DB::table('seo_psi_daily')->where('property_id', $propertyId)->where('strategy', $strategy)
				->orderByDesc('date')->first();
			$out[$strategy] = $row ? [
				'performance' => (int) $row->performance_score,
				'lcp' => round($row->lcp_ms / 1000, 1),
				'cls' => (float) $row->cls,
				'inp' => (int) $row->inp_ms,
				'seo' => (int) $row->seo_score,
				'accessibility' => (int) $row->accessibility_score,
			] : null;
		}

		return $out;
	}

	/**
	 * Laatste AI-kwaliteitsscan voor een site (over al z'n monitored_pages):
	 * score + trend t.o.v. de vorige scan + telling pass/warn/fail + de
	 * belangrijkste aandachtspunten. Null als er nog niet gescand is.
	 */
	private function qualityStats(int $siteId): ?array
	{
		$pageIds = DB::table('monitored_pages')->where('site_id', $siteId)->pluck('id');
		if ($pageIds->isEmpty()) {
			return null;
		}

		$scans = DB::table('quality_scans')
			->whereIn('monitored_page_id', $pageIds)
			->where('status', 'completed')->whereNotNull('score')
			->orderByDesc('completed_at')->limit(2)->get();
		if ($scans->isEmpty()) {
			return null;
		}

		$latest = $scans[0];
		$prev = $scans[1] ?? null;

		$counts = DB::table('quality_findings')->where('quality_scan_id', $latest->id)
			->selectRaw('status, COUNT(*) c')->groupBy('status')->pluck('c', 'status');

		$top = DB::table('quality_findings')->where('quality_scan_id', $latest->id)
			->whereIn('status', ['fail', 'warn'])
			->orderByRaw("FIELD(severity,'hoog','middel','laag'), FIELD(status,'fail','warn')")
			->limit(6)->get()
			->map(fn ($f) => ['status' => $f->status, 'finding' => (string) $f->finding])->all();

		return [
			'score'      => (int) $latest->score,
			'trend'      => $prev ? (int) $latest->score - (int) $prev->score : null,
			'scanned_at' => $latest->completed_at,
			'pass'       => (int) ($counts['pass'] ?? 0),
			'warn'       => (int) ($counts['warn'] ?? 0),
			'fail'       => (int) ($counts['fail'] ?? 0),
			'top'        => $top,
		];
	}

	private function uptimeStats(string $checkId): array
	{
		$since = now()->subDays(7);
		$rows = DB::table('monitor_check_results')->where('check_id', $checkId)
			->where('checked_at', '>=', $since)->orderBy('checked_at')->get();

		$total = $rows->count();
		$up = $rows->where('status', 'up')->count();
		$latencies = $rows->where('status', 'up')->pluck('latency_ms')->filter()->all();

		// Storingen: aaneengesloten down-reeksen
		$incidents = [];
		$open = null;
		foreach ($rows as $r) {
			if ($r->status === 'down' && ! $open) {
				$open = ['from' => $r->checked_at];
			} elseif ($r->status === 'up' && $open) {
				$open['to'] = $r->checked_at;
				$incidents[] = $open;
				$open = null;
			}
		}
		if ($open) {
			$open['to'] = null;
			$incidents[] = $open;
		}

		return [
			'percent' => $total ? round($up / $total * 100, 2) : null,
			'avg_latency' => $latencies ? (int) round(array_sum($latencies) / count($latencies)) : null,
			'current' => $rows->last()->status ?? 'unknown',
			'incidents' => array_map(fn ($i) => [
				'from' => Carbon::parse($i['from'])->format('d-m H:i'),
				'to' => $i['to'] ? Carbon::parse($i['to'])->format('H:i') : 'nu',
			], $incidents),
		];
	}
}
