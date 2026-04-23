<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\ChecklistTemplate;
use App\Models\AccessGuard\CycleAction;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\PersonAccessItem;
use App\Models\AccessGuard\Process;
use App\Models\AccessGuard\ProcessEvidence;
use App\Models\AccessGuard\ProcessItem;
use App\Models\AccessGuard\ReviewLogEntry;
use Carbon\CarbonImmutable;
use Database\Seeders\AccessGuardTemplatesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProcessService
{
	public function __construct(private readonly VaultService $vault) {}

	private const ALLOWED_MIME = ['application/pdf', 'image/png', 'image/jpeg'];
	private const MAX_BYTES = 15 * 1024 * 1024; // 15 MB

	public function start(
		string $tenantId,
		string $actorUserId,
		Person $person,
		string $kind,
		?int $templateId = null,
		?string $dueAt = null,
		?string $notes = null,
	): Process {
		if ($person->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on person');
		}
		if (! in_array($kind, Process::KINDS, true)) {
			throw new RuntimeException("Invalid kind: {$kind}");
		}

		$template = $this->resolveTemplate($tenantId, $kind, $templateId);

		return DB::transaction(function () use ($tenantId, $actorUserId, $person, $kind, $template, $dueAt, $notes) {
			$title = ($kind === 'onboarding' ? 'Onboarding: ' : 'Offboarding: ') . trim($person->first_name . ' ' . $person->last_name);

			$process = Process::create([
				'tenant_id' => $tenantId,
				'person_id' => $person->id,
				'kind' => $kind,
				'status' => 'active',
				'title' => $title,
				'created_by_user_id' => $actorUserId,
				'started_at' => CarbonImmutable::now()->toDateString(),
				'due_at' => $dueAt,
				'notes' => $notes,
			]);

			$now = CarbonImmutable::now()->toDateTimeString();
			$rows = [];
			foreach ($template->items as $ti) {
				$rows[] = [
					'tenant_id' => $tenantId,
					'process_id' => $process->id,
					'title' => $ti->title,
					'description' => $ti->description,
					'status' => 'todo',
					'sort_order' => $ti->sort_order,
					'requires_evidence' => $ti->requires_evidence,
					'created_at' => $now,
					'updated_at' => $now,
				];
			}
			if ($rows) {
				ProcessItem::insert($rows);
			}

			// Person status follows the process start.
			if ($kind === 'onboarding') {
				// stay active or advance from scheduled_in
				if ($person->status === 'scheduled_in') {
					$person->update(['status' => 'active']);
				}
			} else { // offboarding
				if ($person->status === 'active') {
					$person->update(['status' => 'scheduled_out']);
				}
			}

			$this->log($tenantId, $actorUserId, 'process_started', [
				'process_id' => $process->id,
				'kind' => $kind,
				'template_id' => $template->id,
			], personId: $person->id);

			return $process->fresh('items');
		});
	}

	public function updateItem(
		string $tenantId,
		string $actorUserId,
		ProcessItem $item,
		string $status,
		?string $reason = null,
	): ProcessItem {
		if ($item->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on item');
		}
		if (! in_array($status, ProcessItem::STATUSES, true)) {
			throw new RuntimeException("Invalid item status: {$status}");
		}
		if (in_array($status, ['blocked', 'na'], true) && ! $reason) {
			throw new RuntimeException('Reason required for blocked/na');
		}

		$updates = [
			'status' => $status,
			'status_reason' => in_array($status, ['blocked', 'na'], true) ? $reason : null,
		];

		if (in_array($status, ['done', 'na'], true)) {
			$updates['completed_at'] = CarbonImmutable::now();
			$updates['completed_by_user_id'] = $actorUserId;
		} else {
			$updates['completed_at'] = null;
			$updates['completed_by_user_id'] = null;
		}

		$item->update($updates);

		return $item;
	}

	public function uploadEvidence(
		string $tenantId,
		string $actorUserId,
		ProcessItem $item,
		UploadedFile $file,
	): ProcessEvidence {
		if ($item->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on item');
		}
		if ($file->getSize() > self::MAX_BYTES) {
			throw new RuntimeException('File too large (max 15 MB)');
		}
		if (! in_array($file->getMimeType(), self::ALLOWED_MIME, true)) {
			throw new RuntimeException('Only PDF, PNG or JPG allowed');
		}

		$uuid = (string) Str::uuid();
		$safeName = preg_replace('/[^A-Za-z0-9._-]+/', '_', $file->getClientOriginalName() ?: 'evidence');
		$relativePath = sprintf(
			'accessguard/evidence/%s/%d/%s-%s',
			$tenantId, $item->id, $uuid, $safeName,
		);

		$contents = file_get_contents($file->getRealPath());
		Storage::disk('local')->put($relativePath, $contents);

		return ProcessEvidence::create([
			'tenant_id' => $tenantId,
			'process_item_id' => $item->id,
			'file_uuid' => $uuid,
			'original_name' => $file->getClientOriginalName(),
			'stored_path' => $relativePath,
			'mime' => $file->getMimeType(),
			'size_bytes' => $file->getSize(),
			'sha256' => hash('sha256', $contents),
			'uploaded_by_user_id' => $actorUserId,
			'uploaded_at' => CarbonImmutable::now(),
		]);
	}

	public function deleteEvidence(string $tenantId, ProcessEvidence $evidence): void
	{
		if ($evidence->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on evidence');
		}
		Storage::disk('local')->delete($evidence->stored_path);
		$evidence->delete();
	}

	/**
	 * Complete the process. On offboarding: auto-create revoke_access
	 * actions for every has_access cell of the subject person. The person
	 * is set to inactive. On onboarding: person stays active.
	 *
	 * Cannot complete while open items (todo/in_progress) remain.
	 */
	public function complete(string $tenantId, string $actorUserId, Process $process): Process
	{
		if ($process->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on process');
		}
		if (! $process->isOpen()) {
			throw new RuntimeException("Process {$process->id} is not open");
		}

		$unfinished = ProcessItem::query()
			->where('process_id', $process->id)
			->whereIn('status', ['todo', 'in_progress'])
			->count();
		if ($unfinished > 0) {
			throw new RuntimeException("Process has {$unfinished} unfinished items — mark them done, na or blocked first");
		}

		return DB::transaction(function () use ($tenantId, $actorUserId, $process) {
			$now = CarbonImmutable::now();
			$person = $process->person;

			$actionsCreated = 0;
			if ($process->kind === 'offboarding') {
				// Pre-resolve system names + which systems have items
				$systemNames = BusinessSystem::query()
					->where('tenant_id', $tenantId)
					->pluck('name', 'id')
					->all();
				$itemBearingSystems = AccessItem::query()
					->where('tenant_id', $tenantId)
					->where('is_active', true)
					->distinct()
					->pluck('system_id')
					->all();

				// Per-item revokes for systems with items
				$itemsToRevoke = PersonAccessItem::query()
					->where('tenant_id', $tenantId)
					->where('person_id', $process->person_id)
					->where('access_state', 'has_access')
					->whereIn('system_id', $itemBearingSystems)
					->with('accessItem:id,name')
					->get();

				foreach ($itemsToRevoke as $pai) {
					$systemName = $systemNames[$pai->system_id] ?? "system #{$pai->system_id}";
					$itemName = $pai->accessItem?->name ?? "item #{$pai->access_item_id}";
					CycleAction::create([
						'tenant_id' => $tenantId,
						'process_id' => $process->id,
						'person_id' => $pai->person_id,
						'system_id' => $pai->system_id,
						'access_item_id' => $pai->access_item_id,
						'kind' => 'revoke_access',
						'title' => "Revoke: {$person->full_name} → {$systemName} / {$itemName}",
						'status' => 'open',
						'note' => 'Auto-created on offboarding completion',
					]);
					$actionsCreated++;
				}

				// Legacy cell-level revokes for systems WITHOUT items
				$cellRevokes = AccessCell::query()
					->where('tenant_id', $tenantId)
					->where('person_id', $process->person_id)
					->where('access_state', 'has_access')
					->whereNotIn('system_id', $itemBearingSystems)
					->get();

				foreach ($cellRevokes as $cell) {
					$systemName = $systemNames[$cell->system_id] ?? "system #{$cell->system_id}";
					CycleAction::create([
						'tenant_id' => $tenantId,
						'process_id' => $process->id,
						'person_id' => $cell->person_id,
						'system_id' => $cell->system_id,
						'kind' => 'revoke_access',
						'title' => "Revoke: {$person->full_name} → {$systemName}",
						'status' => 'open',
						'note' => 'Auto-created on offboarding completion',
					]);
					$actionsCreated++;
				}

				if ($person && $person->status !== 'inactive') {
					$person->update([
						'status' => 'inactive',
						'end_date' => $person->end_date ?? $now->toDateString(),
					]);
				}

				// If the offboarded person has a V2 user account (same tenant
				// + email match), revoke every vault ACL they hold.
				$vaultRevoked = 0;
				if ($this->vault && $person && $person->email) {
					$user = DB::table('users')
						->where('tenant_id', $tenantId)
						->where('email', $person->email)
						->first();
					if ($user) {
						$vaultRevoked = $this->vault->revokeAllForUser(
							$tenantId, $actorUserId, (string) $user->id,
						);
					}
				}
				if ($vaultRevoked > 0) {
					$this->log($tenantId, $actorUserId, 'vault_acls_revoked', [
						'process_id' => $process->id,
						'count' => $vaultRevoked,
					], personId: $process->person_id);
				}
			} else {
				// onboarding completion: ensure person is active
				if ($person && $person->status !== 'inactive') {
					$person->update(['status' => 'active']);
				}
			}

			$process->update([
				'status' => 'completed',
				'completed_at' => $now,
			]);

			$this->log($tenantId, $actorUserId, 'process_completed', [
				'process_id' => $process->id,
				'kind' => $process->kind,
				'actions_created' => $actionsCreated,
			], personId: $process->person_id);

			return $process->fresh();
		});
	}

	public function cancel(string $tenantId, string $actorUserId, Process $process, ?string $reason = null): Process
	{
		if ($process->tenant_id !== $tenantId) {
			throw new RuntimeException('Tenant mismatch on process');
		}
		$process->update(['status' => 'cancelled']);
		$this->log($tenantId, $actorUserId, 'process_cancelled', ['reason' => $reason], personId: $process->person_id);
		return $process;
	}

	private function resolveTemplate(string $tenantId, string $kind, ?int $templateId): ChecklistTemplate
	{
		if ($templateId) {
			$tpl = ChecklistTemplate::query()
				->with('items')
				->where('tenant_id', $tenantId)
				->where('kind', $kind)
				->findOrFail($templateId);
			return $tpl;
		}

		$tpl = ChecklistTemplate::query()
			->with('items')
			->where('tenant_id', $tenantId)
			->where('kind', $kind)
			->where('is_default', true)
			->first();

		if (! $tpl) {
			// Seed-on-demand so tenants created after the one-off seeder still get defaults.
			(new AccessGuardTemplatesSeeder())->seedForTenant($tenantId);
			$tpl = ChecklistTemplate::query()
				->with('items')
				->where('tenant_id', $tenantId)
				->where('kind', $kind)
				->where('is_default', true)
				->firstOrFail();
		}

		return $tpl;
	}

	private function log(
		string $tenantId,
		?string $actorUserId,
		string $event,
		array $payload = [],
		?int $personId = null,
	): void {
		ReviewLogEntry::create([
			'tenant_id' => $tenantId,
			'person_id' => $personId,
			'actor_user_id' => $actorUserId,
			'event' => $event,
			'payload' => $payload,
			'created_at' => CarbonImmutable::now(),
		]);
	}
}
