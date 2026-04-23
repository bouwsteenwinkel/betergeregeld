<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleAction;
use App\Models\AccessGuard\CycleItem;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\PersonAccessItem;
use App\Models\AccessGuard\ReviewLogEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReviewService
{
	public function __construct(private readonly MatrixService $matrix) {}

	/**
	 * Create a new review cycle and snapshot the current matrix into items.
	 *
	 * Scope "active_people" only snapshots cells for people in active/
	 * scheduled_in/scheduled_out. Scope "all" snapshots everyone including
	 * inactive (useful for audit-style reviews).
	 */
	public function createCycle(
		string $tenantId,
		string $actorUserId,
		string $title,
		string $scope = 'active_people',
		?string $dueAt = null,
		?string $notes = null,
	): Cycle {
		return DB::transaction(function () use ($tenantId, $actorUserId, $title, $scope, $dueAt, $notes) {
			$cycle = Cycle::create([
				'tenant_id' => $tenantId,
				'created_by_user_id' => $actorUserId,
				'title' => $title,
				'scope' => $scope,
				'status' => 'active',
				'starts_at' => CarbonImmutable::now()->toDateString(),
				'due_at' => $dueAt,
				'notes' => $notes,
			]);

			$this->snapshotItems($cycle);

			$this->log($tenantId, $actorUserId, 'cycle_created', [
				'cycle_id' => $cycle->id,
			], cycleId: $cycle->id);

			return $cycle->fresh('items');
		});
	}

	private function snapshotItems(Cycle $cycle): int
	{
		$tenantId = $cycle->tenant_id;

		$peopleQuery = Person::query()->where('tenant_id', $tenantId);
		if ($cycle->scope !== 'all') {
			$peopleQuery->whereIn('status', ['active', 'scheduled_in', 'scheduled_out']);
		}
		$people = $peopleQuery->get(['id', 'first_name', 'middle_name', 'last_name']);
		$systems = BusinessSystem::query()
			->where('tenant_id', $tenantId)
			->where('is_active', true)
			->get(['id', 'name']);

		if ($people->isEmpty() || $systems->isEmpty()) {
			return 0;
		}

		$cellMap = AccessCell::query()
			->where('tenant_id', $tenantId)
			->whereIn('person_id', $people->pluck('id'))
			->whereIn('system_id', $systems->pluck('id'))
			->get(['person_id', 'system_id', 'access_state', 'access_level', 'account_identifier'])
			->groupBy(fn ($c) => $c->person_id . '_' . $c->system_id);

		$items = AccessItem::query()
			->where('tenant_id', $tenantId)
			->where('is_active', true)
			->whereIn('system_id', $systems->pluck('id'))
			->orderBy('sort_order')
			->get(['id', 'system_id', 'name'])
			->groupBy('system_id');

		$personItemMap = PersonAccessItem::query()
			->where('tenant_id', $tenantId)
			->whereIn('person_id', $people->pluck('id'))
			->get(['person_id', 'access_item_id', 'access_state', 'access_level', 'account_identifier'])
			->groupBy(fn ($r) => $r->person_id . '_' . $r->access_item_id);

		$rows = [];
		$now = CarbonImmutable::now()->toDateTimeString();
		foreach ($people as $p) {
			$fullName = trim(implode(' ', array_filter([
				$p->first_name, $p->middle_name, $p->last_name,
			])));
			foreach ($systems as $s) {
				$systemItems = $items->get($s->id);

				if ($systemItems && $systemItems->isNotEmpty()) {
					// One snapshot row per active access item
					foreach ($systemItems as $it) {
						$pai = $personItemMap->get($p->id . '_' . $it->id)?->first();
						$rows[] = [
							'tenant_id' => $tenantId,
							'cycle_id' => $cycle->id,
							'person_id' => $p->id,
							'system_id' => $s->id,
							'access_item_id' => $it->id,
							'person_label' => $fullName,
							'system_label' => $s->name,
							'snapshot_item_name' => $it->name,
							'snapshot_state' => $pai?->access_state ?? 'unknown',
							'snapshot_level' => $pai?->access_level,
							'snapshot_identifier' => $pai?->account_identifier,
							'created_at' => $now,
							'updated_at' => $now,
						];
					}
				} else {
					// Legacy cell-level snapshot
					$cell = $cellMap->get($p->id . '_' . $s->id)?->first();
					$rows[] = [
						'tenant_id' => $tenantId,
						'cycle_id' => $cycle->id,
						'person_id' => $p->id,
						'system_id' => $s->id,
						'access_item_id' => null,
						'person_label' => $fullName,
						'system_label' => $s->name,
						'snapshot_item_name' => null,
						'snapshot_state' => $cell?->access_state ?? 'unknown',
						'snapshot_level' => $cell?->access_level,
						'snapshot_identifier' => $cell?->account_identifier,
						'created_at' => $now,
						'updated_at' => $now,
					];
				}
			}
		}

		foreach (array_chunk($rows, 500) as $chunk) {
			CycleItem::insert($chunk);
		}

		return count($rows);
	}

	public function recordDecision(
		string $tenantId,
		string $actorUserId,
		CycleItem $item,
		string $decision,
		?string $note = null,
	): CycleItem {
		if (! in_array($decision, CycleItem::DECISIONS, true)) {
			throw new RuntimeException("Invalid decision: {$decision}");
		}
		if ($item->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on review item');
		}

		$prev = $item->decision;
		$item->update([
			'decision' => $decision,
			'decision_note' => $note,
			'decided_by_user_id' => $actorUserId,
			'decided_at' => CarbonImmutable::now(),
		]);

		$this->log($tenantId, $actorUserId, $prev ? 'decision_changed' : 'decision_set', [
			'decision' => $decision,
			'previous' => $prev,
			'note' => $note,
		], cycleId: $item->cycle_id, itemId: $item->id, personId: $item->person_id, systemId: $item->system_id);

		return $item;
	}

	/**
	 * Close a cycle: materializes actions for revoke/change decisions,
	 * immediately bumps last_verified_at for keep decisions (no further
	 * work needed), and flags undecided items with "keep" as default so
	 * nothing is stuck halfway.
	 */
	public function completeCycle(
		string $tenantId,
		string $actorUserId,
		Cycle $cycle,
		string $undecidedDefault = 'keep',
	): Cycle {
		if ($cycle->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on cycle');
		}
		if (! $cycle->isOpen()) {
			throw new RuntimeException("Cycle {$cycle->id} is not open (status={$cycle->status})");
		}

		return DB::transaction(function () use ($tenantId, $actorUserId, $cycle, $undecidedDefault) {
			// 1. Default undecided items
			$undecided = CycleItem::query()
				->where('cycle_id', $cycle->id)
				->whereNull('decision')
				->get();
			foreach ($undecided as $item) {
				$this->recordDecision($tenantId, $actorUserId, $item, $undecidedDefault);
			}

			// 2. Create actions for revoke/change
			$decidedItems = CycleItem::query()
				->where('cycle_id', $cycle->id)
				->whereIn('decision', ['revoke', 'change'])
				->get();

			$now = CarbonImmutable::now();
			foreach ($decidedItems as $item) {
				$kind = $item->decision === 'revoke' ? 'revoke_access' : 'review_level';
				$subject = $item->access_item_id
					? "{$item->person_label} → {$item->system_label} / {$item->snapshot_item_name}"
					: "{$item->person_label} → {$item->system_label}";
				$title = $item->decision === 'revoke' ? "Revoke: {$subject}" : "Review level: {$subject}";

				$action = CycleAction::create([
					'tenant_id' => $tenantId,
					'cycle_id' => $cycle->id,
					'review_item_id' => $item->id,
					'person_id' => $item->person_id,
					'system_id' => $item->system_id,
					'access_item_id' => $item->access_item_id,
					'kind' => $kind,
					'title' => $title,
					'status' => 'open',
					'note' => $item->decision_note,
				]);

				$this->log($tenantId, $actorUserId, 'action_created', [
					'action_id' => $action->id,
					'kind' => $kind,
					'access_item_id' => $item->access_item_id,
				], cycleId: $cycle->id, itemId: $item->id, actionId: $action->id, personId: $item->person_id, systemId: $item->system_id);
			}

			// 3. For "keep" decisions: bump last_verified_at on the matched row
			//    (item-level or cell-level depending on the snapshot).
			$keeps = CycleItem::query()
				->where('cycle_id', $cycle->id)
				->where('decision', 'keep')
				->select('person_id', 'system_id', 'access_item_id')
				->get();
			foreach ($keeps as $k) {
				if ($k->access_item_id) {
					PersonAccessItem::query()
						->where('tenant_id', $tenantId)
						->where('person_id', $k->person_id)
						->where('access_item_id', $k->access_item_id)
						->update(['last_verified_at' => $now]);
				} else {
					AccessCell::query()
						->where('tenant_id', $tenantId)
						->where('person_id', $k->person_id)
						->where('system_id', $k->system_id)
						->update(['last_verified_at' => $now]);
				}
			}

			$cycle->update([
				'status' => 'completed',
				'completed_at' => $now,
			]);

			$this->log($tenantId, $actorUserId, 'cycle_completed', [
				'actions_opened' => count($decidedItems),
				'undecided_defaulted_to' => $undecidedDefault,
				'undecided_count' => count($undecided),
			], cycleId: $cycle->id);

			return $cycle->fresh();
		});
	}

	public function cancelCycle(string $tenantId, string $actorUserId, Cycle $cycle, ?string $reason = null): Cycle
	{
		if ($cycle->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on cycle');
		}

		$cycle->update(['status' => 'cancelled']);
		$this->log($tenantId, $actorUserId, 'cycle_cancelled', ['reason' => $reason], cycleId: $cycle->id);
		return $cycle;
	}

	/**
	 * Mark an action done: applies the intended change to the matrix cell.
	 * - revoke_access: flip cell to no_access
	 * - review_level: no state change, just mark verified
	 */
	public function markActionDone(string $tenantId, string $actorUserId, CycleAction $action, ?string $note = null): CycleAction
	{
		if ($action->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on action');
		}
		if ($action->status !== 'open') {
			throw new RuntimeException("Action {$action->id} not open (status={$action->status})");
		}

		return DB::transaction(function () use ($tenantId, $actorUserId, $action, $note) {
			$now = CarbonImmutable::now();
			$applied = [];

			if ($action->access_item_id) {
				// Item-level: flip the per-item state (cell aggregate recomputes).
				if ($action->kind === 'revoke_access') {
					$this->matrix->setItemState(
						$tenantId,
						$action->person_id,
						$action->access_item_id,
						'no_access',
					);
					$applied = ['scope' => 'item', 'item_state' => 'no_access'];
				} else {
					// review_level → only bump verified timestamp on the item
					PersonAccessItem::query()
						->where('tenant_id', $tenantId)
						->where('person_id', $action->person_id)
						->where('access_item_id', $action->access_item_id)
						->update(['last_verified_at' => $now]);
					$applied = ['scope' => 'item', 'verified_only' => true];
				}
			} else {
				// Legacy cell-level
				$cellUpdate = ['last_verified_at' => $now];
				if ($action->kind === 'revoke_access') {
					$cellUpdate['access_state'] = 'no_access';
				}
				AccessCell::query()
					->where('tenant_id', $tenantId)
					->where('person_id', $action->person_id)
					->where('system_id', $action->system_id)
					->update($cellUpdate);
				$applied = ['scope' => 'cell'] + $cellUpdate;
			}

			$action->update([
				'status' => 'done',
				'completed_at' => $now,
				'note' => $note ?: $action->note,
			]);

			$this->log($tenantId, $actorUserId, 'action_done', [
				'kind' => $action->kind,
				'applied' => $applied,
			], cycleId: $action->cycle_id, itemId: $action->review_item_id, actionId: $action->id, personId: $action->person_id, systemId: $action->system_id);

			return $action;
		});
	}

	public function cancelAction(string $tenantId, string $actorUserId, CycleAction $action, ?string $reason = null): CycleAction
	{
		if ($action->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on action');
		}

		$action->update(['status' => 'cancelled', 'completed_at' => CarbonImmutable::now()]);
		$this->log($tenantId, $actorUserId, 'action_cancelled', ['reason' => $reason], cycleId: $action->cycle_id, itemId: $action->review_item_id, actionId: $action->id, personId: $action->person_id, systemId: $action->system_id);
		return $action;
	}

	private function log(
		string $tenantId,
		?string $actorUserId,
		string $event,
		array $payload = [],
		?int $cycleId = null,
		?int $itemId = null,
		?int $actionId = null,
		?int $personId = null,
		?int $systemId = null,
	): void {
		ReviewLogEntry::create([
			'tenant_id' => $tenantId,
			'cycle_id' => $cycleId,
			'review_item_id' => $itemId,
			'review_action_id' => $actionId,
			'person_id' => $personId,
			'system_id' => $systemId,
			'actor_user_id' => $actorUserId,
			'event' => $event,
			'payload' => $payload,
			'created_at' => CarbonImmutable::now(),
		]);
	}
}
