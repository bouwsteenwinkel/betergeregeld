<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessProfile;
use App\Models\AccessGuard\Person;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProfileService
{
	public function __construct(private readonly MatrixService $matrix) {}

	/**
	 * Apply a profile to a person. Returns counts written per strategy.
	 *   - add_only: only touch cells/items that are currently "unknown"
	 *   - overwrite: apply the profile's default_state regardless
	 *   - dry_run: compute changes but don't write
	 *
	 * @return array{would_change:int, applied:int, skipped:int, strategy:string}
	 */
	public function applyTo(
		string $tenantId,
		string $actorUserId,
		Person $person,
		AccessProfile $profile,
		string $strategy = 'add_only',
	): array {
		if ($person->tenant_id !== $tenantId) throw new RuntimeException('Tenant mismatch on person');
		if ($profile->tenant_id !== $tenantId) throw new RuntimeException('Tenant mismatch on profile');
		if (! in_array($strategy, ['add_only', 'overwrite', 'dry_run'], true)) {
			throw new RuntimeException("Invalid strategy: {$strategy}");
		}

		$items = $profile->items()->with('system', 'accessItem')->get();
		$applied = 0; $skipped = 0; $wouldChange = 0;
		$now = CarbonImmutable::now();

		foreach ($items as $pi) {
			// Resolve the "current" state of this target for the person
			$current = $pi->access_item_id
				? $this->currentItemState($tenantId, $person->id, $pi->access_item_id)
				: $this->currentCellState($tenantId, $person->id, $pi->system_id);

			$shouldWrite = match ($strategy) {
				'add_only' => $current === 'unknown' || $current === null,
				'overwrite' => true,
				'dry_run' => false,
			};

			if ($current === $pi->default_state) {
				$skipped++;
				continue;
			}
			$wouldChange++;

			if (! $shouldWrite) {
				$skipped++;
				continue;
			}

			if ($pi->access_item_id) {
				$this->matrix->setItemState($tenantId, $person->id, $pi->access_item_id, $pi->default_state);
			} else {
				DB::statement(
					'INSERT INTO accessguard_access_cells
					  (tenant_id, person_id, system_id, access_state, last_verified_at, created_at, updated_at)
					 VALUES (?, ?, ?, ?, ?, ?, ?)
					 ON DUPLICATE KEY UPDATE
					   access_state = VALUES(access_state),
					   last_verified_at = VALUES(last_verified_at),
					   updated_at = VALUES(updated_at)',
					[
						$tenantId, $person->id, $pi->system_id,
						$pi->default_state,
						$now->toDateTimeString(), $now->toDateTimeString(), $now->toDateTimeString(),
					],
				);
			}
			$applied++;
		}

		return [
			'would_change' => $wouldChange,
			'applied' => $applied,
			'skipped' => $skipped,
			'strategy' => $strategy,
		];
	}

	/**
	 * Apply a profile to every person in its ProfileMember list — used to
	 * cascade a synced M365 group's access template to all its members
	 * in one click.
	 *
	 * @return array{members:int, total_applied:int, total_skipped:int, total_would_change:int, strategy:string}
	 */
	public function applyToAllMembers(
		string $tenantId,
		string $actorUserId,
		AccessProfile $profile,
		string $strategy = 'add_only',
	): array {
		if ($profile->tenant_id !== $tenantId) throw new RuntimeException('Tenant mismatch on profile');

		$members = $profile->members()->with('person')->get();
		$applied = 0; $skipped = 0; $would = 0;

		foreach ($members as $member) {
			if (! $member->person || $member->person->tenant_id !== $tenantId) continue;
			$result = $this->applyTo($tenantId, $actorUserId, $member->person, $profile, $strategy);
			$applied += $result['applied'];
			$skipped += $result['skipped'];
			$would += $result['would_change'];
		}

		return [
			'members' => $members->count(),
			'total_applied' => $applied,
			'total_skipped' => $skipped,
			'total_would_change' => $would,
			'strategy' => $strategy,
		];
	}

	private function currentCellState(string $tenantId, int $personId, int $systemId): ?string
	{
		return DB::table('accessguard_access_cells')
			->where('tenant_id', $tenantId)
			->where('person_id', $personId)
			->where('system_id', $systemId)
			->value('access_state');
	}

	private function currentItemState(string $tenantId, int $personId, int $accessItemId): ?string
	{
		return DB::table('accessguard_person_access_items')
			->where('tenant_id', $tenantId)
			->where('person_id', $personId)
			->where('access_item_id', $accessItemId)
			->value('access_state');
	}
}
