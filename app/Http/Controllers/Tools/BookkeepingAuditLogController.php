<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\BookkeepingAuditLog;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookkeepingAuditLogController extends Controller
{
	public function __construct(private readonly FeatureResolver $features) {}

	public function index(Request $request, string $locale): View
	{
		abort_unless($request->user(), 401);
		abort_unless(
			$this->features->forUser($request->user())->bool('tool.bookkeeping.enabled'),
			402,
		);

		$filters = $request->validate([
			'entity_type' => ['nullable', 'in:transaction,relation,category,vat_rate'],
			'action' => ['nullable', 'in:created,updated,deleted'],
			'from' => ['nullable', 'date'],
			'to' => ['nullable', 'date'],
			'user_id' => ['nullable', 'uuid'],
		]);

		$q = BookkeepingAuditLog::query()
			->where('tenant_id', $request->user()->tenant_id)
			->orderByDesc('created_at');

		if (! empty($filters['entity_type'])) {
			$q->where('entity_type', $filters['entity_type']);
		}
		if (! empty($filters['action'])) {
			$q->where('action', $filters['action']);
		}
		if (! empty($filters['from'])) {
			$q->where('created_at', '>=', $filters['from'] . ' 00:00:00');
		}
		if (! empty($filters['to'])) {
			$q->where('created_at', '<=', $filters['to'] . ' 23:59:59');
		}
		if (! empty($filters['user_id'])) {
			$q->where('user_id', $filters['user_id']);
		}

		$entries = $q->paginate(50)->withQueryString();

		return view('tools.bookkeeping.audit-log.index', [
			'entries' => $entries,
			'filters' => $filters,
		]);
	}
}
