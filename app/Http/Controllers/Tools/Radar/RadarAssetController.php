<?php

namespace App\Http\Controllers\Tools\Radar;

use App\Http\Controllers\Controller;
use App\Jobs\AccessGuard\RunSecurityHeadersScanJob;
use App\Jobs\AccessGuard\RunWebChecksScanJob;
use App\Models\AccessGuard\Radar\Asset;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RadarAssetController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$assets = Asset::query()
			->where('tenant_id', $tenantId)
			->where('status', '!=', 'deleted')
			->withCount([
				'findings as open_findings_count' => fn ($q) => $q
					->whereIn('check_type', ['security_headers', 'tls', 'cookies', 'cmp'])
					->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk']),
			])
			->orderBy('name')
			->get();

		return view('tools.radar.assets.index', compact('assets'));
	}

	public function create(Request $request, string $locale): View
	{
		$features = RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$max = $features->limit('tool.radar.max_assets');
		$current = Asset::query()->where('tenant_id', $tenantId)->where('status', '!=', 'deleted')->count();
		$canAdd = $max === null || $current < $max;

		return view('tools.radar.assets.create', [
			'max'      => $max,
			'current'  => $current,
			'canAdd'   => $canAdd,
			'planName' => $features->plan->name,
		]);
	}

	public function store(Request $request, string $locale): RedirectResponse
	{
		$features = RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$max = $features->limit('tool.radar.max_assets');
		$current = Asset::query()->where('tenant_id', $tenantId)->where('status', '!=', 'deleted')->count();
		if ($max !== null && $current >= $max) {
			return back()->withErrors(['_general' => __('Je hebt het maximum van :n assets bereikt. Upgrade je plan om meer toe te voegen.', ['n' => $max])]);
		}

		$data = $request->validate([
			'name'        => 'required|string|max:120',
			'url'         => 'required|url|max:500',
			'criticality' => 'required|in:low,medium,high,critical',
		]);

		$host = parse_url($data['url'], PHP_URL_HOST) ?: null;

		Asset::create([
			'tenant_id'    => $tenantId,
			'kind'         => 'website',
			'name'         => $data['name'],
			'url'          => $data['url'],
			'hostname'     => $host,
			'environment'  => 'production',
			'is_public'    => 'yes',
			'auth_required'=> 'no',
			'criticality'  => $data['criticality'],
			'status'       => 'active',
			'discovered_at'=> Carbon::now(),
			'last_seen_at' => Carbon::now(),
			'owner_user_id'=> $request->user()->id,
		]);

		return redirect()
			->route('tools.radar.assets.index', ['locale' => $locale])
			->with('status', __('Asset toegevoegd. Klik op "Scan nu" om de eerste scan te starten.'));
	}

	public function show(Request $request, string $locale, int $id): View
	{
		RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$asset = Asset::query()
			->where('tenant_id', $tenantId)
			->where('id', $id)
			->firstOrFail();

		$findings = $asset->findings()
			->whereIn('check_type', ['security_headers', 'tls', 'cookies', 'cmp'])
			->orderByRaw("FIELD(status, 'new','confirmed','reopened','in_progress','planned','accepted_risk','patched','mitigated','ignored','false_positive','resolved')")
			->orderByDesc('risk_score')
			->orderByDesc('last_detected_at')
			->get()
			->groupBy('status');

		$scans = $asset->scans()->orderByDesc('id')->limit(20)->get();

		return view('tools.radar.assets.show', compact('asset', 'findings', 'scans'));
	}

	public function destroy(Request $request, string $locale, int $id): RedirectResponse
	{
		RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;

		$asset = Asset::query()->where('tenant_id', $tenantId)->where('id', $id)->firstOrFail();
		// Soft-delete via status zodat scan-historie + findings bewaard blijven; staff
		// kan altijd terugzetten via Filament admin.
		$asset->update(['status' => 'deleted']);

		return redirect()
			->route('tools.radar.assets.index', ['locale' => $locale])
			->with('status', __('Asset verwijderd.'));
	}

	public function scan(Request $request, string $locale, int $id): RedirectResponse
	{
		$features = RadarAccess::require($request, $this->features);
		$tenantId = $request->user()->tenant_id;
		$userId   = $request->user()->id;

		$asset = Asset::query()->where('tenant_id', $tenantId)->where('id', $id)->where('status', 'active')->firstOrFail();

		// Per-asset cooldown — niet meer dan 1 scan per 60s (anders zou je 'm 50x kunnen klikken).
		$lock = Cache::lock("radar:scan:asset:{$asset->id}", 60);
		if (! $lock->get()) {
			return back()->withErrors(['_general' => __('Er loopt al een scan voor dit asset. Probeer over een minuut opnieuw.')]);
		}

		// Daglimit per user op basis van plan.
		$dailyCap = $features->limit('tool.radar.scans_per_day');
		if ($dailyCap !== null) {
			$dayKey = 'radar:scans:user:' . $userId . ':' . Carbon::today()->toDateString();
			$used = (int) Cache::get($dayKey, 0);
			if ($used >= $dailyCap) {
				$lock->release();
				return back()->withErrors(['_general' => __('Je dagelijkse scan-limit (:n) is bereikt. Probeer morgen opnieuw of upgrade je plan.', ['n' => $dailyCap])]);
			}
			Cache::put($dayKey, $used + 1, Carbon::tomorrow());
		}

		$checks = array_values(array_filter(explode(',', (string) $features->raw('tool.radar.checks_allowed', ''))));

		// Headers-scan zit nog in z'n eigen job; TLS/cookies/CMP zit in WebChecks-job.
		// We dispatchen wat in checks_allowed staat — strict gefilterd.
		if (in_array('headers', $checks, true)) {
			RunSecurityHeadersScanJob::dispatch($asset->id);
		}
		$webChecks = array_values(array_intersect($checks, ['tls', 'cookies', 'cmp']));
		if (! empty($webChecks)) {
			RunWebChecksScanJob::dispatch($asset->id, $webChecks);
		}

		return back()->with('status', __('Scan gestart. Resultaten verschijnen zodra de queue klaar is (refresh deze pagina over een paar seconden).'));
	}
}
