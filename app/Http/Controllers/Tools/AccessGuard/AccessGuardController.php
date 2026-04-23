<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessGuardController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$peopleCount = Person::query()->where('tenant_id', $tenantId)->count();
		$activePeopleCount = Person::query()
			->where('tenant_id', $tenantId)
			->whereIn('status', ['active', 'scheduled_in', 'scheduled_out'])
			->count();

		$systemsCount = BusinessSystem::query()->where('tenant_id', $tenantId)->count();
		$activeSystemsCount = BusinessSystem::query()
			->where('tenant_id', $tenantId)
			->where('is_active', true)
			->count();

		$byState = AccessCell::query()
			->where('tenant_id', $tenantId)
			->selectRaw('access_state, COUNT(*) as c')
			->groupBy('access_state')
			->pluck('c', 'access_state')
			->all();

		$totalCells = $activePeopleCount * $activeSystemsCount;
		$recordedCells = array_sum($byState);

		return view('tools.accessguard.index', [
			'peopleCount' => $peopleCount,
			'activePeopleCount' => $activePeopleCount,
			'systemsCount' => $systemsCount,
			'activeSystemsCount' => $activeSystemsCount,
			'byState' => $byState,
			'totalCells' => $totalCells,
			'recordedCells' => $recordedCells,
			'needsReview' => (int) ($byState['needs_review'] ?? 0),
			'hasAccess' => (int) ($byState['has_access'] ?? 0),
			'noAccess' => (int) ($byState['no_access'] ?? 0),
			'unknown' => (int) ($byState['unknown'] ?? 0),
		]);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
