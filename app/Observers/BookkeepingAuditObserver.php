<?php

namespace App\Observers;

use App\Models\BookkeepingAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Attached to the four bookkeeping models. Writes a row to
 * bookkeeping_audit_log for every create / update / delete.
 *
 * Redacted fields (receipt_path's storage path etc.) are allowed because
 * the audit log is only visible to the same tenant that produced the
 * mutation. `id` is stored on its own field, not in `changes`.
 */
class BookkeepingAuditObserver
{
	private const ENTITY_MAP = [
		'App\\Models\\BookkeepingTransaction' => 'transaction',
		'App\\Models\\BookkeepingRelation' => 'relation',
		'App\\Models\\BookkeepingCategory' => 'category',
		'App\\Models\\BookkeepingVatRate' => 'vat_rate',
	];

	public function created(Model $model): void
	{
		$attrs = $model->getAttributes();
		unset($attrs['id'], $attrs['created_at'], $attrs['updated_at'], $attrs['tenant_id']);
		$this->write($model, 'created', $attrs);
	}

	public function updated(Model $model): void
	{
		$diff = [];
		foreach ($model->getChanges() as $key => $newValue) {
			if (in_array($key, ['updated_at', 'created_at'], true)) {
				continue;
			}
			$diff[$key] = [
				'from' => $model->getOriginal($key),
				'to' => $newValue,
			];
		}
		if (empty($diff)) {
			return;
		}
		$this->write($model, 'updated', $diff);
	}

	public function deleted(Model $model): void
	{
		$attrs = $model->getOriginal();
		unset($attrs['created_at'], $attrs['updated_at'], $attrs['tenant_id']);
		$this->write($model, 'deleted', $attrs);
	}

	private function write(Model $model, string $action, array $changes): void
	{
		$entityType = self::ENTITY_MAP[get_class($model)] ?? class_basename($model);
		$tenantId = $model->getAttribute('tenant_id') ?: $model->getOriginal('tenant_id');
		if (! $tenantId) {
			return; // system-owned rows (default categories/rates) aren't audited
		}

		$user = Auth::user();
		$request = app()->bound('request') ? Request::instance() : null;

		BookkeepingAuditLog::create([
			'tenant_id' => $tenantId,
			'user_id' => $user?->getAuthIdentifier(),
			'user_email' => $user?->email,
			'entity_type' => $entityType,
			'entity_id' => (string) ($model->getKey() ?? ''),
			'action' => $action,
			'changes' => $changes,
			'ip_hash' => $request && $request->ip()
				? hash('sha256', $request->ip() . '|' . config('app.key'))
				: null,
			'user_agent' => $request
				? mb_substr((string) $request->header('User-Agent', ''), 0, 300)
				: null,
			'created_at' => now(),
		]);
	}
}
