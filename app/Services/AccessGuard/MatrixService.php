<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\PersonAccessItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Shared helpers for the matrix + item layers.
 *
 * Cell-level access_state is the aggregate of its items when the
 * system has any; otherwise it's stored directly. This keeps the
 * 2D matrix fast to render while still allowing per-item precision.
 */
class MatrixService
{
	public function systemHasItems(string $tenantId, int $systemId): bool
	{
		return AccessItem::query()
			->where('tenant_id', $tenantId)
			->where('system_id', $systemId)
			->where('is_active', true)
			->exists();
	}

	/**
	 * Upsert a single item state for person × access_item and
	 * recompute the cell-level aggregate.
	 *
	 * @return array{state:string, verified_at:string, cell_state:string}
	 */
	public function setItemState(
		string $tenantId,
		int $personId,
		int $accessItemId,
		string $state,
	): array {
		if (! in_array($state, PersonAccessItem::STATES, true)) {
			throw new RuntimeException("Invalid state: {$state}");
		}

		$item = AccessItem::query()
			->where('tenant_id', $tenantId)
			->findOrFail($accessItemId);

		$now = CarbonImmutable::now();

		DB::statement(
			'INSERT INTO accessguard_person_access_items
			  (tenant_id, person_id, system_id, access_item_id, access_state, last_verified_at, created_at, updated_at)
			 VALUES (?, ?, ?, ?, ?, ?, ?, ?)
			 ON DUPLICATE KEY UPDATE
			   access_state = VALUES(access_state),
			   last_verified_at = VALUES(last_verified_at),
			   updated_at = VALUES(updated_at)',
			[
				$tenantId,
				$personId,
				$item->system_id,
				$accessItemId,
				$state,
				$now->toDateTimeString(),
				$now->toDateTimeString(),
				$now->toDateTimeString(),
			],
		);

		$cellState = $this->recomputeCell($tenantId, $personId, $item->system_id);

		return [
			'state' => $state,
			'verified_at' => $now->format('Y-m-d'),
			'cell_state' => $cellState,
		];
	}

	/**
	 * Aggregate a person's item states into the cell-level access_state.
	 *
	 * Rules:
	 *   - ≥1 has_access → has_access
	 *   - all active items known + none has_access + all no_access → no_access
	 *   - any needs_review or mix of has_access/other → needs_review
	 *   - no item rows yet → unknown
	 */
	public function recomputeCell(string $tenantId, int $personId, int $systemId): string
	{
		$items = PersonAccessItem::query()
			->where('tenant_id', $tenantId)
			->where('person_id', $personId)
			->where('system_id', $systemId)
			->pluck('access_state')
			->all();

		if (empty($items)) {
			$aggregate = 'unknown';
		} elseif (in_array('needs_review', $items, true)) {
			$aggregate = 'needs_review';
		} elseif (in_array('has_access', $items, true)) {
			$mixed = count(array_unique($items)) > 1;
			$aggregate = $mixed ? 'needs_review' : 'has_access';
		} else {
			// all items are either no_access or unknown
			$allNo = count(array_filter($items, fn ($s) => $s !== 'no_access')) === 0;
			$aggregate = $allNo ? 'no_access' : 'unknown';
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
				$tenantId,
				$personId,
				$systemId,
				$aggregate,
				$now->toDateTimeString(),
				$now->toDateTimeString(),
				$now->toDateTimeString(),
			],
		);

		return $aggregate;
	}
}
