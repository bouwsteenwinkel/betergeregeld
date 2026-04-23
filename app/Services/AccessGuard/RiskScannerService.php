<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleAction;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\PersonAccessItem;
use App\Models\AccessGuard\Process;
use App\Models\AccessGuard\RiskFlag;
use App\Models\AccessGuard\VaultCredential;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Periodic scan of a tenant's access matrix, reviews and processes
 * to surface risk patterns as open RiskFlag rows. Idempotent on the
 * unique (tenant, kind, subject_type, subject_id) key so repeat runs
 * won't duplicate, and resolved rows stay resolved — a rule only
 * reopens a risk by creating a new subject_id.
 */
class RiskScannerService
{
	public function __construct(private readonly NotificationService $notifier) {}

	/**
	 * @return array<string,int> per-rule counts written
	 */
	public function scan(string $tenantId, CarbonImmutable $now = null): array
	{
		$now ??= CarbonImmutable::now();
		$counts = [];
		$counts['stale_admin'] = $this->scanStaleAdmins($tenantId, $now);
		$counts['orphan_access'] = $this->scanOrphanAccess($tenantId, $now);
		$counts['excessive_access'] = $this->scanExcessiveAccess($tenantId, $now);
		$counts['overdue_review'] = $this->scanOverdueReviews($tenantId, $now);
		$counts['overdue_action'] = $this->scanOverdueActions($tenantId, $now);
		$counts['pending_onboarding'] = $this->scanPendingOnboarding($tenantId, $now);
		$counts['stale_credential'] = $this->scanStaleCredentials($tenantId, $now);
		$counts['dormant_account'] = $this->scanDormantAccounts($tenantId, $now);
		return $counts;
	}

	/**
	 * Person with a linked M365/Google account that hasn't signed in for 90+ days.
	 * Only runs against people synced from a directory (external_source set).
	 */
	private function scanDormantAccounts(string $tenantId, CarbonImmutable $now): int
	{
		$threshold = $now->subDays(90);
		$people = Person::query()
			->where('tenant_id', $tenantId)
			->whereNotNull('external_source')
			->where('status', 'active')
			->whereNotNull('last_sign_in_at')
			->where('last_sign_in_at', '<', $threshold)
			->get(['id', 'first_name', 'last_name', 'external_source', 'last_sign_in_at']);

		$count = 0;
		foreach ($people as $p) {
			$days = (int) $now->diffInDays($p->last_sign_in_at);
			$severity = $days >= 180 ? 4 : 3;
			$this->upsert($tenantId, [
				'kind' => 'dormant_account',
				'severity' => $severity,
				'subject_type' => 'person',
				'subject_id' => (string) $p->id,
				'title' => trim($p->first_name . ' ' . $p->last_name),
				'description' => __(':days dagen niet ingelogd op :source. Overweeg toegang in te trekken.', [
					'days' => $days,
					'source' => strtoupper($p->external_source),
				]),
				'payload' => [
					'person_id' => $p->id,
					'days_dormant' => $days,
					'last_sign_in_at' => $p->last_sign_in_at->toIso8601String(),
					'source' => $p->external_source,
				],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Vault credential past expires_at OR rotation overdue. Severity 4-5. */
	private function scanStaleCredentials(string $tenantId, CarbonImmutable $now): int
	{
		$rows = VaultCredential::query()
			->where('tenant_id', $tenantId)
			->get(['id', 'name', 'expires_at', 'rotation_interval_days', 'last_rotated_at']);

		$count = 0;
		foreach ($rows as $cred) {
			$expired = $cred->expires_at !== null && $cred->expires_at->isPast();
			$rotationDue = $cred->rotation_interval_days
				&& $cred->last_rotated_at
				&& $cred->last_rotated_at->copy()->addDays($cred->rotation_interval_days)->isPast();
			if (! $expired && ! $rotationDue) {
				continue;
			}

			$reasons = [];
			if ($expired) {
				$reasons[] = __('verlopen op :date', ['date' => $cred->expires_at->format('d-m-Y')]);
			}
			if ($rotationDue) {
				$reasons[] = __('rotatie overdue sinds :date', ['date' => $cred->last_rotated_at->copy()->addDays($cred->rotation_interval_days)->format('d-m-Y')]);
			}

			$this->upsert($tenantId, [
				'kind' => 'stale_credential',
				'severity' => $expired ? 5 : 4,
				'subject_type' => 'credential',
				'subject_id' => (string) $cred->id,
				'title' => $cred->name,
				'description' => implode(' · ', $reasons),
				'payload' => [
					'credential_id' => $cred->id,
					'expired' => $expired,
					'rotation_due' => $rotationDue,
				],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Admin-type item with has_access + not verified in 90+ days. Severity 4. */
	private function scanStaleAdmins(string $tenantId, CarbonImmutable $now): int
	{
		$cutoff = $now->subDays(90);

		$rows = PersonAccessItem::query()
			->where('tenant_id', $tenantId)
			->where('access_state', 'has_access')
			->where(function ($q) use ($cutoff) {
				$q->whereNull('last_verified_at')->orWhere('last_verified_at', '<', $cutoff);
			})
			->whereHas('accessItem', fn ($q) => $q->whereIn('type', ['role', 'account'])
				->where(function ($q) {
					$q->where('name', 'like', '%admin%')
						->orWhere('name', 'like', '%owner%')
						->orWhere('name', 'like', '%root%');
				}))
			->with(['person:id,first_name,last_name', 'accessItem:id,name', 'system:id,name'])
			->get();

		$count = 0;
		foreach ($rows as $pai) {
			$this->upsert($tenantId, [
				'kind' => 'stale_admin',
				'severity' => 4,
				'subject_type' => 'access_item',
				'subject_id' => (string) $pai->access_item_id . ':' . $pai->person_id,
				'title' => trim(($pai->person->first_name ?? '') . ' ' . ($pai->person->last_name ?? ''))
					. ' — ' . ($pai->accessItem?->name ?? '') . ' (' . ($pai->system?->name ?? '') . ')',
				'description' => __(':days dagen niet geverifieerd op een admin-achtige rol.', [
					'days' => $pai->last_verified_at ? $now->diffInDays($pai->last_verified_at) : '90+',
				]),
				'payload' => [
					'person_id' => $pai->person_id,
					'access_item_id' => $pai->access_item_id,
					'system_id' => $pai->system_id,
					'last_verified_at' => $pai->last_verified_at?->toIso8601String(),
				],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Inactive/archived person still has any has_access cell or item. Severity 5. */
	private function scanOrphanAccess(string $tenantId, CarbonImmutable $now): int
	{
		$people = Person::query()
			->where('tenant_id', $tenantId)
			->where('status', 'inactive')
			->get(['id', 'first_name', 'last_name']);

		$count = 0;
		foreach ($people as $person) {
			$cellCount = AccessCell::query()
				->where('tenant_id', $tenantId)
				->where('person_id', $person->id)
				->where('access_state', 'has_access')
				->count();
			$itemCount = PersonAccessItem::query()
				->where('tenant_id', $tenantId)
				->where('person_id', $person->id)
				->where('access_state', 'has_access')
				->count();

			if ($cellCount + $itemCount === 0) {
				continue;
			}

			$this->upsert($tenantId, [
				'kind' => 'orphan_access',
				'severity' => 5,
				'subject_type' => 'person',
				'subject_id' => (string) $person->id,
				'title' => trim($person->first_name . ' ' . $person->last_name),
				'description' => __('Inactieve persoon heeft nog :n cellen en :m items op has_access.', [
					'n' => $cellCount, 'm' => $itemCount,
				]),
				'payload' => [
					'person_id' => $person->id,
					'has_access_cells' => $cellCount,
					'has_access_items' => $itemCount,
				],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Person with has_access on >= 10 systems. Severity 3. */
	private function scanExcessiveAccess(string $tenantId, CarbonImmutable $now, int $threshold = 10): int
	{
		$rows = DB::table('accessguard_access_cells')
			->where('tenant_id', $tenantId)
			->where('access_state', 'has_access')
			->selectRaw('person_id, COUNT(*) AS c')
			->groupBy('person_id')
			->having('c', '>=', $threshold)
			->get();

		$count = 0;
		foreach ($rows as $r) {
			$person = Person::find($r->person_id);
			if (! $person || $person->tenant_id !== $tenantId) {
				continue;
			}
			$this->upsert($tenantId, [
				'kind' => 'excessive_access',
				'severity' => 3,
				'subject_type' => 'person',
				'subject_id' => (string) $person->id,
				'title' => trim($person->first_name . ' ' . $person->last_name),
				'description' => __('Toegang tot :n systemen — review of dit nodig is.', ['n' => $r->c]),
				'payload' => ['systems_count' => $r->c, 'threshold' => $threshold],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Review cycle past due_at but not completed/cancelled. Severity 4. */
	private function scanOverdueReviews(string $tenantId, CarbonImmutable $now): int
	{
		$rows = Cycle::query()
			->where('tenant_id', $tenantId)
			->whereIn('status', ['planned', 'active'])
			->whereNotNull('due_at')
			->where('due_at', '<', $now->toDateString())
			->get();

		$count = 0;
		foreach ($rows as $c) {
			$overdueDays = $now->diffInDays($c->due_at, absolute: true);
			$this->upsert($tenantId, [
				'kind' => 'overdue_review',
				'severity' => $overdueDays > 30 ? 5 : 4,
				'subject_type' => 'cycle',
				'subject_id' => (string) $c->id,
				'title' => $c->title,
				'description' => __(':n dagen over deadline.', ['n' => $overdueDays]),
				'payload' => ['overdue_days' => $overdueDays, 'due_at' => $c->due_at->toDateString()],
			], $now);
			$count++;
		}
		return $count;
	}

	/** Open action older than 14 days. Severity 3. */
	private function scanOverdueActions(string $tenantId, CarbonImmutable $now, int $ageDays = 14): int
	{
		$cutoff = $now->subDays($ageDays);
		$rows = CycleAction::query()
			->where('tenant_id', $tenantId)
			->where('status', 'open')
			->where('created_at', '<', $cutoff)
			->get();

		$count = 0;
		foreach ($rows as $a) {
			$age = $now->diffInDays($a->created_at, absolute: true);
			$this->upsert($tenantId, [
				'kind' => 'overdue_action',
				'severity' => $age > 30 ? 4 : 3,
				'subject_type' => 'action',
				'subject_id' => (string) $a->id,
				'title' => $a->title,
				'description' => __('Actie staat :n dagen open.', ['n' => $age]),
				'payload' => ['age_days' => $age, 'kind' => $a->kind],
			], $now);
			$count++;
		}
		return $count;
	}

	/**
	 * Person with status=scheduled_in but no active onboarding
	 * process — someone's going to start without setup. Severity 3.
	 */
	private function scanPendingOnboarding(string $tenantId, CarbonImmutable $now): int
	{
		$people = Person::query()
			->where('tenant_id', $tenantId)
			->where('status', 'scheduled_in')
			->get();

		$count = 0;
		foreach ($people as $person) {
			$hasProcess = Process::query()
				->where('tenant_id', $tenantId)
				->where('person_id', $person->id)
				->where('kind', 'onboarding')
				->whereIn('status', ['active', 'completed'])
				->exists();
			if ($hasProcess) {
				continue;
			}

			$this->upsert($tenantId, [
				'kind' => 'pending_onboarding',
				'severity' => 3,
				'subject_type' => 'person',
				'subject_id' => (string) $person->id,
				'title' => trim($person->first_name . ' ' . $person->last_name),
				'description' => __('Staat op scheduled_in maar er draait nog geen onboarding-proces.'),
				'payload' => [
					'person_id' => $person->id,
					'start_date' => $person->start_date?->toDateString(),
				],
			], $now);
			$count++;
		}
		return $count;
	}

	private function upsert(string $tenantId, array $fields, CarbonImmutable $now): void
	{
		$values = array_merge($fields, [
			'tenant_id' => $tenantId,
			'status' => 'open',
			'detected_at' => $now,
			'updated_at' => $now,
		]);
		$values['payload'] = isset($values['payload']) ? json_encode($values['payload']) : null;

		// Was this risk already open/acked/resolved before this run? We only
		// fire instant alerts on first detection — so repeat runs stay quiet.
		$existed = RiskFlag::query()
			->where('tenant_id', $tenantId)
			->where('kind', $fields['kind'])
			->where('subject_type', $fields['subject_type'])
			->where('subject_id', $fields['subject_id'])
			->exists();

		RiskFlag::query()->upsert(
			[$values + ['created_at' => $now]],
			['tenant_id', 'kind', 'subject_type', 'subject_id'],
			['severity', 'title', 'description', 'detected_at', 'payload', 'updated_at'],
		);

		if (! $existed && ($fields['severity'] ?? 0) >= 5) {
			$fresh = RiskFlag::query()
				->where('tenant_id', $tenantId)
				->where('kind', $fields['kind'])
				->where('subject_type', $fields['subject_type'])
				->where('subject_id', $fields['subject_id'])
				->first();
			if ($fresh) {
				$this->notifier->dispatchInstantAlert($fresh);
			}
		}
	}
}
