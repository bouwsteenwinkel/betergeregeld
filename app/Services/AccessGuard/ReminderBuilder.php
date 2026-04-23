<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleAction;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\Process;
use App\Models\AccessGuard\Reminder;
use Carbon\CarbonImmutable;

/**
 * Scans for upcoming/overdue deadlines and creates Reminder rows.
 * Idempotent on (kind, subject_type, subject_id) per tenant.
 */
class ReminderBuilder
{
	/**
	 * @return array<string,int> per-kind counts written
	 */
	public function build(string $tenantId, CarbonImmutable $now = null): array
	{
		$now ??= CarbonImmutable::now();
		return [
			'cycle_due' => $this->buildCycleDue($tenantId, $now),
			'process_due' => $this->buildProcessDue($tenantId, $now),
			'action_overdue' => $this->buildActionOverdue($tenantId, $now),
			'person_starting' => $this->buildPersonStarting($tenantId, $now),
			'person_leaving' => $this->buildPersonLeaving($tenantId, $now),
		];
	}

	/** Open cycles due within 7 days or already overdue. */
	private function buildCycleDue(string $tenantId, CarbonImmutable $now): int
	{
		$soon = $now->addDays(7);
		$rows = Cycle::query()
			->where('tenant_id', $tenantId)
			->whereIn('status', ['planned', 'active'])
			->whereNotNull('due_at')
			->where('due_at', '<=', $soon->toDateString())
			->get();

		$count = 0;
		foreach ($rows as $c) {
			$this->upsert($tenantId, [
				'kind' => 'cycle_due',
				'subject_type' => 'cycle',
				'subject_id' => (string) $c->id,
				'title' => $c->title,
				'description' => $this->dueDescription($now, $c->due_at),
				'due_at' => $c->due_at->toDateString(),
			], $now);
			$count++;
		}
		return $count;
	}

	/** Open processes due within 7 days or already overdue. */
	private function buildProcessDue(string $tenantId, CarbonImmutable $now): int
	{
		$soon = $now->addDays(7);
		$rows = Process::query()
			->where('tenant_id', $tenantId)
			->where('status', 'active')
			->whereNotNull('due_at')
			->where('due_at', '<=', $soon->toDateString())
			->get();

		$count = 0;
		foreach ($rows as $p) {
			$this->upsert($tenantId, [
				'kind' => 'process_due',
				'subject_type' => 'process',
				'subject_id' => (string) $p->id,
				'title' => $p->title,
				'description' => $this->dueDescription($now, $p->due_at),
				'due_at' => $p->due_at->toDateString(),
			], $now);
			$count++;
		}
		return $count;
	}

	/** Open actions with created_at older than 14 days. */
	private function buildActionOverdue(string $tenantId, CarbonImmutable $now, int $ageDays = 14): int
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
				'kind' => 'action_overdue',
				'subject_type' => 'action',
				'subject_id' => (string) $a->id,
				'title' => $a->title,
				'description' => __('Actie staat :n dagen open.', ['n' => $age]),
				'due_at' => $a->created_at->toDateString(),
			], $now);
			$count++;
		}
		return $count;
	}

	/** scheduled_in person whose start_date is within 7 days. */
	private function buildPersonStarting(string $tenantId, CarbonImmutable $now): int
	{
		$soon = $now->addDays(7);
		$rows = Person::query()
			->where('tenant_id', $tenantId)
			->where('status', 'scheduled_in')
			->whereNotNull('start_date')
			->whereBetween('start_date', [$now->toDateString(), $soon->toDateString()])
			->get();

		$count = 0;
		foreach ($rows as $p) {
			$this->upsert($tenantId, [
				'kind' => 'person_starting',
				'subject_type' => 'person',
				'subject_id' => (string) $p->id,
				'title' => trim($p->first_name . ' ' . $p->last_name) . ' — ' . __('start binnenkort'),
				'description' => __('Start op :date — onboarding plannen.', ['date' => $p->start_date->format('d-m-Y')]),
				'due_at' => $p->start_date->toDateString(),
			], $now);
			$count++;
		}
		return $count;
	}

	/** scheduled_out person whose end_date is within 7 days. */
	private function buildPersonLeaving(string $tenantId, CarbonImmutable $now): int
	{
		$soon = $now->addDays(7);
		$rows = Person::query()
			->where('tenant_id', $tenantId)
			->where('status', 'scheduled_out')
			->whereNotNull('end_date')
			->whereBetween('end_date', [$now->toDateString(), $soon->toDateString()])
			->get();

		$count = 0;
		foreach ($rows as $p) {
			$this->upsert($tenantId, [
				'kind' => 'person_leaving',
				'subject_type' => 'person',
				'subject_id' => (string) $p->id,
				'title' => trim($p->first_name . ' ' . $p->last_name) . ' — ' . __('vertrekt binnenkort'),
				'description' => __('Einddatum :date — offboarding afronden.', ['date' => $p->end_date->format('d-m-Y')]),
				'due_at' => $p->end_date->toDateString(),
			], $now);
			$count++;
		}
		return $count;
	}

	private function dueDescription(CarbonImmutable $now, \DateTimeInterface $due): string
	{
		$today = $now->startOfDay();
		$dueDay = CarbonImmutable::parse($due)->startOfDay();
		if ($dueDay->lessThan($today)) {
			return __(':n dagen over deadline.', ['n' => $today->diffInDays($dueDay, absolute: true)]);
		}
		if ($dueDay->equalTo($today)) {
			return __('Deadline vandaag.');
		}
		return __('Deadline over :n dagen.', ['n' => $today->diffInDays($dueDay)]);
	}

	private function upsert(string $tenantId, array $fields, CarbonImmutable $now): void
	{
		// Only resurface if not explicitly dismissed.
		$existing = Reminder::query()
			->where('tenant_id', $tenantId)
			->where('kind', $fields['kind'])
			->where('subject_type', $fields['subject_type'])
			->where('subject_id', $fields['subject_id'])
			->first();
		if ($existing && $existing->status === 'dismissed') {
			return;
		}

		$values = array_merge($fields, [
			'tenant_id' => $tenantId,
			'status' => 'open',
			'updated_at' => $now,
		]);

		Reminder::query()->upsert(
			[$values + ['created_at' => $now]],
			['tenant_id', 'kind', 'subject_type', 'subject_id'],
			['title', 'description', 'due_at', 'updated_at'],
		);
	}
}
