<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\PersonAccessItem;
use App\Services\AccessGuard\MatrixService;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatrixController extends Controller
{
	public function __construct(
		private readonly FeatureResolver $features,
		private readonly MatrixService $matrix,
	) {}

	public function index(Request $request, string $locale): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$people = Person::query()
			->where('tenant_id', $tenantId)
			->whereIn('status', ['active', 'scheduled_in', 'scheduled_out'])
			->orderBy('last_name')
			->orderBy('first_name')
			->get();

		$systems = BusinessSystem::query()
			->where('tenant_id', $tenantId)
			->where('is_active', true)
			->orderBy('name')
			->get();

		$rawCells = AccessCell::query()
			->where('tenant_id', $tenantId)
			->get(['person_id', 'system_id', 'access_state', 'last_verified_at']);

		$cells = [];
		foreach ($rawCells as $c) {
			$cells[$c->person_id][$c->system_id] = [
				'state' => $c->access_state,
				'verified_at' => $c->last_verified_at?->format('Y-m-d'),
			];
		}

		// Per-system count of active items — a system with items disables the
		// click-to-cycle on its matrix column (users drill down instead).
		$itemsBySystem = AccessItem::query()
			->where('tenant_id', $tenantId)
			->where('is_active', true)
			->selectRaw('system_id, COUNT(*) as c')
			->groupBy('system_id')
			->pluck('c', 'system_id')
			->all();

		return view('tools.accessguard.matrix', compact('people', 'systems', 'cells', 'itemsBySystem'));
	}

	/**
	 * Click-to-cycle a single cell. Only allowed on systems WITHOUT items;
	 * for systems with items the cell-state is derived from item states
	 * and must be changed via updateItemState.
	 */
	public function updateCell(Request $request, string $locale): JsonResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'person_id' => ['required', 'integer'],
			'system_id' => ['required', 'integer'],
			'state' => ['required', 'in:' . implode(',', AccessCell::STATES)],
		]);

		$tenantId = $request->user()->tenant_id;

		$person = Person::query()->where('tenant_id', $tenantId)->findOrFail($data['person_id']);
		$system = BusinessSystem::query()->where('tenant_id', $tenantId)->findOrFail($data['system_id']);

		if ($this->matrix->systemHasItems($tenantId, $system->id)) {
			return response()->json([
				'ok' => false,
				'error' => 'system_has_items',
				'message' => __('Dit systeem heeft items — ga naar de drill-down om per item te beslissen.'),
			], 422);
		}

		$now = CarbonImmutable::now();
		DB::statement(
			'INSERT INTO accessguard_access_cells
			  (tenant_id, person_id, system_id, access_state, last_verified_at, created_at, updated_at)
			 VALUES (?, ?, ?, ?, ?, ?, ?)
			 ON DUPLICATE KEY UPDATE
			   access_state = VALUES(access_state),
			   last_verified_at = VALUES(last_verified_at),
			   updated_at = VALUES(updated_at)',
			[
				$tenantId, $person->id, $system->id,
				$data['state'],
				$now->toDateTimeString(), $now->toDateTimeString(), $now->toDateTimeString(),
			],
		);

		return response()->json([
			'ok' => true,
			'state' => $data['state'],
			'verified_at' => $now->format('Y-m-d'),
		]);
	}

	/**
	 * Drill-down: show all items for person × system, with per-item
	 * cycling. The page is reached by clicking a matrix cell whose
	 * system has any active items.
	 */
	public function cellDetail(Request $request, string $locale, int $personId, int $systemId): View
	{
		$this->mustHaveAccess($request);
		$tenantId = $request->user()->tenant_id;

		$person = Person::query()->where('tenant_id', $tenantId)->findOrFail($personId);
		$system = BusinessSystem::query()->where('tenant_id', $tenantId)->findOrFail($systemId);

		$items = AccessItem::query()
			->where('tenant_id', $tenantId)
			->where('system_id', $system->id)
			->where('is_active', true)
			->orderBy('sort_order')
			->orderBy('name')
			->get();

		$rawRows = PersonAccessItem::query()
			->where('tenant_id', $tenantId)
			->where('person_id', $person->id)
			->where('system_id', $system->id)
			->get(['access_item_id', 'access_state', 'last_verified_at']);

		$itemStates = [];
		foreach ($rawRows as $r) {
			$itemStates[$r->access_item_id] = [
				'state' => $r->access_state,
				'verified_at' => $r->last_verified_at?->format('Y-m-d'),
			];
		}

		$cellState = AccessCell::query()
			->where('tenant_id', $tenantId)
			->where('person_id', $person->id)
			->where('system_id', $system->id)
			->value('access_state') ?? 'unknown';

		return view('tools.accessguard.matrix.cell', compact('person', 'system', 'items', 'itemStates', 'cellState'));
	}

	public function updateItemState(Request $request, string $locale): JsonResponse
	{
		$this->mustHaveAccess($request);

		$data = $request->validate([
			'person_id' => ['required', 'integer'],
			'access_item_id' => ['required', 'integer'],
			'state' => ['required', 'in:' . implode(',', PersonAccessItem::STATES)],
		]);

		$tenantId = $request->user()->tenant_id;

		// Scope check — person must belong to the tenant.
		Person::query()->where('tenant_id', $tenantId)->findOrFail($data['person_id']);

		$result = $this->matrix->setItemState(
			$tenantId,
			$data['person_id'],
			$data['access_item_id'],
			$data['state'],
		);

		return response()->json([
			'ok' => true,
		] + $result);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
