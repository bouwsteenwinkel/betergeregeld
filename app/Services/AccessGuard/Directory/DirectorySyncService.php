<?php

namespace App\Services\AccessGuard\Directory;

use App\Models\AccessGuard\DirectoryConnection;
use App\Models\AccessGuard\Person;
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

			$connection->update([
				'last_synced_at' => $now,
				'last_sync_status' => 'ok',
				'last_sync_message' => null,
				'last_sync_users_seen' => $seen,
				'last_sync_users_created' => $created,
				'last_sync_users_updated' => $updated,
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

		return compact('seen', 'created', 'updated');
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
