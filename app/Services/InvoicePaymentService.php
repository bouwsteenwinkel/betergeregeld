<?php

namespace App\Services;

use App\Models\BookkeepingCategory;
use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Creates the income transactions for a paid invoice.
 *
 * An invoice can mix VAT rates (e.g. a line at 21% and another at 9%),
 * but a transaction has a single vat_rate_id. We therefore group the
 * lines by vat_rate_id and create one transaction per group. All
 * generated transactions carry invoice_id so we can link back.
 *
 * Idempotent: if transactions for this invoice already exist, the
 * method is a no-op.
 */
class InvoicePaymentService
{
	public function createTransactionsForPaidInvoice(BookkeepingInvoice $invoice, ?string $userId = null): array
	{
		if (BookkeepingTransaction::where('invoice_id', $invoice->id)->exists()) {
			return [];
		}

		$invoice->loadMissing(['lines.vatRate', 'relation']);

		// Group lines by vat_rate_id (null → '_none' key for grouping)
		$groups = [];
		foreach ($invoice->lines as $line) {
			$key = $line->vat_rate_id === null ? '_none' : (string) $line->vat_rate_id;
			$groups[$key] ??= [
				'vat_rate_id' => $line->vat_rate_id,
				'net' => 0.0,
				'vat' => 0.0,
				'lines' => [],
			];
			$groups[$key]['net'] += $line->lineNet();
			$groups[$key]['vat'] += $line->lineVat();
			$groups[$key]['lines'][] = $line;
		}

		$category = BookkeepingCategory::query()
			->whereNull('tenant_id')
			->where('type', 'income')
			->where('name', 'Omzet')
			->first();

		$date = ($invoice->paid_at ?: CarbonImmutable::now())->toDateString();
		$created = [];

		DB::transaction(function () use ($invoice, $groups, $category, $date, $userId, &$created) {
			foreach ($groups as $group) {
				$gross = round($group['net'] + $group['vat'], 2);
				if ($gross <= 0) {
					continue;
				}
				$tx = BookkeepingTransaction::create([
					'id' => (string) Str::uuid(),
					'tenant_id' => $invoice->tenant_id,
					'created_by_user_id' => $userId,
					'invoice_id' => $invoice->id,
					'relation_id' => $invoice->relation_id,
					'category_id' => $category?->id,
					'type' => 'income',
					'transaction_date' => $date,
					'amount' => $gross,
					'vat_rate_id' => $group['vat_rate_id'],
					'vat_included' => true,
					'vat_deductible' => false, // income VAT isn't "deductible" — it's payable
					'description' => 'Factuur ' . $invoice->invoice_number . ' · ' . ($invoice->relation?->name ?? ''),
					'counterparty' => $invoice->relation?->name,
					'invoice_number' => $invoice->invoice_number,
					'import_source' => 'invoice',
				]);
				$created[] = $tx;
			}
		});

		return $created;
	}
}
