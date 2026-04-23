<?php

namespace App\Services\AccessGuard\Directory;

use App\Models\AccessGuard\AccessProfile;
use App\Models\AccessGuard\DirectoryConnection;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\ProfileMember;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Pulls users from an external directory and mirrors them into the
 * AccessGuard Person table.
 *
 * Matching order per incoming user:
 *  1. external_source + external_id (most stable — survives email change).
 *  2. Existing Person with same tenant_id + email (first hit wins).
 *  3. Create new Person with type='employee'.
 *
 * Soft-deletes are NOT revived here — if a Person was deleted on purpose,
 * admin must restore them explicitly.
 *
 * accountEnabled = false in Graph → Person.status = 'inactive'.
 * accountEnabled = true           → Person.status stays 'active' (or
 *                                   whatever it was if already non-active).
 */
class DirectorySyncService
{
	public function sync(DirectoryConnection $connection, DirectoryClient $client): array
	{
		$tenantId = $connection->tenant_id;
		$now = CarbonImmutable::now();
		$source = $connection->provider;

		$seen = 0;
		$created = 0;
		$updated = 0;
		$groupsSeen = 0;
		$groupsUpserted = 0;
		$membershipsSeen = 0;

		try {
			foreach ($client->listUsers() as $dirUser) {
				$seen++;
				$result = $this->upsertPerson($tenantId, $source, $dirUser, $now);
				if ($result === 'created') {
					$created++;
				} elseif ($result === 'updated') {
					$updated++;
				}
			}

			// Groups must come AFTER users — we match members by external_id,
			// so every possible person must already exist in the Person table.
			foreach ($client->listGroups() as $dirGroup) {
				$groupsSeen++;
				$profile = $this->upsertProfile($tenantId, $source, $dirGroup, $now);
				if ($profile) {
					$groupsUpserted++;
					$membershipsSeen += $this->syncMemberships($tenantId, $source, $profile, $dirGroup->memberExternalIds, $now);
				}
			}

			$connection->update([
				'last_synced_at' => $now,
				'last_sync_status' => 'ok',
				'last_sync_message' => null,
				'last_sync_users_seen' => $seen,
				'last_sync_users_created' => $created,
				'last_sync_users_updated' => $updated,
				'last_sync_groups_seen' => $groupsSeen,
				'last_sync_groups_upserted' => $groupsUpserted,
				'last_sync_memberships_seen' => $membershipsSeen,
				'status' => 'connected',
			]);
		} catch (Throwable $e) {
			Log::error('AccessGuard directory sync failed', [
				'tenant' => $tenantId,
				'provider' => $source,
				'error' => $e->getMessage(),
			]);
			$connection->update([
				'last_synced_at' => $now,
				'last_sync_status' => 'error',
				'last_sync_message' => substr($e->getMessage(), 0, 500),
				'status' => 'error',
			]);
			throw $e;
		}

		return compact('seen', 'created', 'updated', 'groupsSeen', 'groupsUpserted', 'membershipsSeen');
	}

	/**
	 * Upsert an AccessProfile for a directory group. Matches by
	 * (tenant_id, external_source, external_id). If the group's displayName
	 * collides with an existing human-created profile's name, we append
	 * " — M365" to keep the unique(tenant_id, name) index happy.
	 */
	private function upsertProfile(string $tenantId, string $source, DirectoryGroup $g, CarbonImmutable $now): ?AccessProfile
	{
		return DB::transaction(function () use ($tenantId, $source, $g, $now) {
			$profile = AccessProfile::query()
				->where('tenant_id', $tenantId)
				->where('external_source', $source)
				->where('external_id', $g->externalId)
				->first();

			$name = $g->displayName;

			if (! $profile) {
				$collides = AccessProfile::query()
					->where('tenant_id', $tenantId)
					->where('name', $name)
					->exists();
				if ($collides) {
					$name .= ' — ' . strtoupper($source);
				}
			}

			$payload = [
				'tenant_id' => $tenantId,
				'name' => $profile?->name ?? $name, // keep original name on re-sync; don't rename on each run
				'description' => $g->description,
				'is_active' => true,
				'external_source' => $source,
				'external_id' => $g->externalId,
				'last_synced_at' => $now,
			];

			if ($profile) {
				$profile->fill($payload)->save();
				return $profile;
			}

			return AccessProfile::create($payload);
		});
	}

	/**
	 * Replace the set of ProfileMembers for a synced profile with whatever
	 * the directory currently shows. Members added in AccessGuard manually
	 * (source='manual') are left alone — only rows with source=$source and
	 * no longer present in the membership list get pruned.
	 *
	 * @param string[] $memberExternalIds
	 * @return int count of membership rows visible after sync (for metrics)
	 */
	private function syncMemberships(string $tenantId, string $source, AccessProfile $profile, array $memberExternalIds, CarbonImmutable $now): int
	{
		if (empty($memberExternalIds)) {
			// Drop all synced memberships for this profile — group is empty
			// on the source side (or we couldn't read members).
			ProfileMember::query()
				->where('tenant_id', $tenantId)
				->where('profile_id', $profile->id)
				->where('source', $source)
				->delete();
			return 0;
		}

		// Resolve external_id → person_id in one query
		$personIds = Person::query()
			->where('tenant_id', $tenantId)
			->where('external_source', $source)
			->whereIn('external_id', $memberExternalIds)
			->pluck('id', 'external_id');

		$resolvedPersonIds = $personIds->values()->all();

		if (empty($resolvedPersonIds)) {
			ProfileMember::query()
				->where('tenant_id', $tenantId)
				->where('profile_id', $profile->id)
				->where('source', $source)
				->delete();
			return 0;
		}

		DB::transaction(function () use ($tenantId, $source, $profile, $resolvedPersonIds, $now) {
			$rows = [];
			foreach ($resolvedPersonIds as $pid) {
				$rows[] = [
					'tenant_id' => $tenantId,
					'profile_id' => $profile->id,
					'person_id' => $pid,
					'source' => $source,
					'last_synced_at' => $now,
					'created_at' => $now,
					'updated_at' => $now,
				];
			}

			ProfileMember::query()->upsert(
				$rows,
				['tenant_id', 'profile_id', 'person_id'],
				['source', 'last_synced_at', 'updated_at'],
			);

			// Prune: synced members for this profile whose person_id is no
			// longer in the membership list.
			ProfileMember::query()
				->where('tenant_id', $tenantId)
				->where('profile_id', $profile->id)
				->where('source', $source)
				->whereNotIn('person_id', $resolvedPersonIds)
				->delete();
		});

		return count($resolvedPersonIds);
	}

	/** @return 'created'|'updated'|'unchanged' */
	private function upsertPerson(string $tenantId, string $source, DirectoryUser $u, CarbonImmutable $now): string
	{
		return DB::transaction(function () use ($tenantId, $source, $u, $now) {
			$person = Person::query()
				->where('tenant_id', $tenantId)
				->where('external_source', $source)
				->where('external_id', $u->externalId)
				->first();

			if (! $person && $u->email) {
				$person = Person::query()
					->where('tenant_id', $tenantId)
					->where('email', $u->email)
					->whereNull('external_source')
					->first();
			}

			$payload = [
				'external_source' => $source,
				'external_id' => $u->externalId,
				'last_sign_in_at' => $u->lastSignInAt,
				'last_synced_at' => $now,
			];

			if ($u->email) $payload['email'] = $u->email;
			if ($u->firstName) $payload['first_name'] = $u->firstName;
			if ($u->lastName) $payload['last_name'] = $u->lastName;
			if ($u->jobTitle) $payload['job_title'] = $u->jobTitle;
			if ($u->department) $payload['department'] = $u->department;

			// Status: only auto-flip to inactive. Never auto-flip back to active
			// (admin might have set scheduled_in/scheduled_out manually).
			if (! $u->accountEnabled) {
				$payload['status'] = 'inactive';
			}

			if ($person) {
				$person->fill($payload);
				$dirty = $person->isDirty();
				$person->save();
				return $dirty ? 'updated' : 'unchanged';
			}

			Person::create(array_merge($payload, [
				'tenant_id' => $tenantId,
				'type' => 'employee',
				'first_name' => $u->firstName ?? 'Unknown',
				'last_name' => $u->lastName ?? '',
				'status' => $u->accountEnabled ? 'active' : 'inactive',
			]));
			return 'created';
		});
	}
}
