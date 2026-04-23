<?php

namespace App\Services;

use App\Mail\InvoiceDeliveryMail;
use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingInvoiceLine;
use App\Models\BookkeepingRecurringInvoice;
use App\Models\BookkeepingTenantSettings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Spawns concrete invoices from active recurring templates whose
 * next_run_at is due. Advances next_run_at one frequency-step forward
 * so the same run never fires twice.
 */
class RecurringInvoiceGenerator
{
	public function __construct(
		private readonly InvoiceNumberer $numberer,
		private readonly InvoicePdfRenderer $pdfRenderer,
	) {}

	/** @return array{checked:int, generated:int, sent:int, failed:int, log:array<array<string,mixed>>} */
	public function runDue(?CarbonImmutable $today = null): array
	{
		$today ??= CarbonImmutable::now()->startOfDay();

		$due = BookkeepingRecurringInvoice::query()
			->where('is_active', true)
			->whereDate('next_run_at', '<=', $today->toDateString())
			->where(function ($q) use ($today) {
				$q->whereNull('end_date')->orWhere('end_date', '>=', $today->toDateString());
			})
			->with(['relation', 'lines'])
			->get();

		$stats = ['checked' => $due->count(), 'generated' => 0, 'sent' => 0, 'failed' => 0, 'log' => []];

		foreach ($due as $template) {
			try {
				[$invoice, $emailed] = $this->generateOne($template, $today);
				$stats['generated']++;
				if ($emailed) {
					$stats['sent']++;
				}
				$stats['log'][] = [
					'template' => $template->id,
					'invoice_number' => $invoice->invoice_number,
					'emailed' => $emailed,
				];
			} catch (\Throwable $e) {
				$stats['failed']++;
				$stats['log'][] = ['template' => $template->id, 'error' => $e->getMessage()];
				Log::error('recurring invoice generation failed', [
					'template' => $template->id, 'error' => $e->getMessage(),
				]);
			}
		}

		return $stats;
	}

	/** @return array{0: BookkeepingInvoice, 1: bool} */
	public function generateOne(BookkeepingRecurringInvoice $template, CarbonImmutable $today): array
	{
		return DB::transaction(function () use ($template, $today) {
			$issueDate = $today;
			$dueDate = $template->payment_terms_days > 0
				? $issueDate->addDays($template->payment_terms_days)
				: null;

			$invoice = new BookkeepingInvoice([
				'tenant_id' => $template->tenant_id,
				'relation_id' => $template->relation_id,
				'issue_date' => $issueDate->toDateString(),
				'due_date' => $dueDate?->toDateString(),
				'status' => 'draft',
				'payment_terms_days' => $template->payment_terms_days,
				'notes' => $template->notes,
				'recurring_template_id' => $template->id,
				'created_by_user_id' => $template->created_by_user_id,
			]);
			$invoice->id = (string) Str::uuid();
			$invoice->invoice_number = $this->numberer->next(
				$invoice->tenant_id,
				(int) $issueDate->year,
			);
			$invoice->save();

			foreach ($template->lines as $i => $line) {
				BookkeepingInvoiceLine::create([
					'invoice_id' => $invoice->id,
					'description' => $line->description,
					'quantity' => $line->quantity,
					'unit_price' => $line->unit_price,
					'vat_rate_id' => $line->vat_rate_id,
					'sort_order' => $i,
				]);
			}
			$invoice->load('lines.vatRate');
			$invoice->recalculate();
			$invoice->save();

			$template->update([
				'last_run_at' => now(),
				'next_run_at' => $this->advanceNextRun($template, $issueDate),
			]);

			$emailed = false;
			if ($template->auto_send_email) {
				$emailed = $this->emailInvoice($invoice);
			}

			return [$invoice, $emailed];
		});
	}

	private function advanceNextRun(BookkeepingRecurringInvoice $template, CarbonImmutable $ranAt): string
	{
		// monthly → next month, same day-of-month (clamped to last day if short month)
		$next = $ranAt->addMonthNoOverflow()->setDay(min($template->day_of_month, $ranAt->addMonthNoOverflow()->daysInMonth));
		return $next->toDateString();
	}

	private function emailInvoice(BookkeepingInvoice $invoice): bool
	{
		$invoice->load(['relation', 'lines.vatRate']);
		$relation = $invoice->relation;
		if (! $relation?->email) {
			return false;
		}

		$settings = BookkeepingTenantSettings::find($invoice->tenant_id);
		if (! $settings) {
			return false;
		}

		// Render PDF to a temp location so it can be attached
		$pdfDir = storage_path('app/bookkeeping/invoices/' . $invoice->tenant_id);
		$pdfPath = $pdfDir . '/factuur-' . $invoice->invoice_number . '.pdf';
		$this->pdfRenderer->render($invoice, $pdfPath);

		try {
			Mail::to($relation->email)->send(new InvoiceDeliveryMail($invoice, $settings, $pdfPath));
			$invoice->update(['status' => 'sent', 'sent_at' => now()]);
			return true;
		} catch (\Throwable $e) {
			Log::error('auto-send invoice email failed', [
				'invoice' => $invoice->invoice_number, 'error' => $e->getMessage(),
			]);
			return false;
		}
	}
}
