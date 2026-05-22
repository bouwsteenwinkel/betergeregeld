<?php

namespace App\Http\Controllers\Tools\Radar;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Finding;
use App\Models\AccessGuard\Radar\Scan;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadarDashboardController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$features = RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$assetsTotal = Asset::query()->where('tenant_id', $tenantId)->where('status', '!=', 'deleted')->count();
		$assetsActive = Asset::query()->where('tenant_id', $tenantId)->where('status', 'active')->count();

		$findingsBySeverity = Finding::query()
			->where('tenant_id', $tenantId)
			->whereIn('check_type', ['security_headers', 'tls', 'cookies', 'cmp'])
			->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
			->selectRaw('severity, COUNT(*) as c')
			->groupBy('severity')
			->pluck('c', 'severity')
			->all();

		$findings = [
			'critical' => $findingsBySeverity['critical'] ?? 0,
			'high'     => $findingsBySeverity['high'] ?? 0,
			'medium'   => $findingsBySeverity['medium'] ?? 0,
			'low'      => $findingsBySeverity['low'] ?? 0,
		];
		$findings['total'] = array_sum($findings);

		$recentFindings = Finding::query()
			->where('tenant_id', $tenantId)
			->whereIn('check_type', ['security_headers', 'tls', 'cookies', 'cmp'])
			->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
			->with('asset:id,name,url')
			->orderByDesc('risk_score')
			->orderByDesc('last_detected_at')
			->limit(10)
			->get();

		$recentScans = Scan::query()
			->whereIn('asset_id', Asset::query()->where('tenant_id', $tenantId)->pluck('id'))
			->with('asset:id,name,url')
			->orderByDesc('id')
			->limit(8)
			->get();

		$planLimits = [
			'max_assets'      => $features->raw('tool.radar.max_assets', '0'),
			'checks_allowed'  => array_filter(explode(',', (string) $features->raw('tool.radar.checks_allowed', ''))),
			'scans_per_day'   => $features->limit('tool.radar.scans_per_day') ?? 0,
			'plan_name'       => $features->plan->name,
		];

		return view('tools.radar.index', compact(
			'assetsTotal', 'assetsActive', 'findings',
			'recentFindings', 'recentScans', 'planLimits',
		));
	}
}
