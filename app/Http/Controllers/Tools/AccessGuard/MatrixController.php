<?php

namespace App\Http\Controllers\Tools\AccessGuard;

use App\Http\Controllers\Controller;
use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MatrixController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

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

		return view('tools.accessguard.matrix', compact('people', 'systems', 'cells'));
	}

	/**
	 * Click-to-cycle a single cell. Body: person_id, system_id, state.
	 * Returns JSON with the new state + verified timestamp.
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

		// Confirm both records belong to the same tenant — no cross-tenant writes.
		$person = Person::query()->where('tenant_id', $tenantId)->findOrFail($data['person_id']);
		$system = BusinessSystem::query()->where('tenant_id', $tenantId)->findOrFail($data['system_id']);

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
				$tenantId,
				$person->id,
				$system->id,
				$data['state'],
				$now->toDateTimeString(),
				$now->toDateTimeString(),
				$now->toDateTimeString(),
			],
		);

		return response()->json([
			'ok' => true,
			'state' => $data['state'],
			'verified_at' => $now->format('Y-m-d'),
		]);
	}

	protected function mustHaveAccess(Request $request): void
	{
		abort_unless($request->user(), 401);
		$bag = $this->features->forUser($request->user());
		abort_unless($bag->bool('tool.accessguard.enabled'), 402);
	}
}
