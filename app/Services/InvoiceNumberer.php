<?php

namespace App\Services;

use App\Models\BookkeepingInvoice;
use Illuminate\Support\Facades\DB;

/**
 * Generates the next invoice number for a tenant per calendar year,
 * format "YYYY-NNN" with a zero-padded 3-digit counter.
 *
 * Races within the same tenant-year are serialised with a pessimistic
 * lock on the latest existing row — fine for single-user / small-team
 * tenants. For high-concurrency we'd move to a dedicated sequence table.
 */
class InvoiceNumberer
{
	public function next(string $tenantId, int $year): string
	{
		return DB::transaction(function () use ($tenantId, $year) {
			$prefix = $year . '-';
			$last = BookkeepingInvoice::query()
				->where('tenant_id', $tenantId)
				->where('invoice_number', 'like', $prefix . '%')
				->orderByDesc('invoice_number')
				->lockForUpdate()
				->value('invoice_number');

			$next = 1;
			if ($last && preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $last, $m)) {
				$next = (int) $m[1] + 1;
			}

			return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
		});
	}
}
