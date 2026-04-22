<?php

namespace App\Services;

use App\Mail\InvoiceReminderMail;
use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingInvoiceReminder;
use App\Models\BookkeepingTenantSettings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Decides which invoices are due for a reminder email and sends them.
 *
 * Kinds and when they fire, relative to invoice->due_date:
 *   pre_due     — 3 days before due
 *   due         — on the due date itself
 *   overdue_7   — 7 days after due
 *   overdue_21  — 21 days after due (final)
 *
 * An invoice never receives the same kind twice (enforced by a unique
 * (invoice_id, kind) index on bookkeeping_invoice_reminders).
 */
class InvoiceReminderService
{
	public const KINDS = ['pre_due' => -3, 'due' => 0, 'overdue_7' => 7, 'overdue_21' => 21];

	/** @return array{checked:int, sent:int, skipped:int, reasons:array<string,int>} */
	public function runForAllTenants(?CarbonImmutable $today = null): array
	{
		$today ??= CarbonImmutable::now()->startOfDay();

		$candidates = BookkeepingInvoice::query()
			->where('status', 'sent')
			->whereNotNull('due_date')
			->with('relation')
			->get();

		$stats = ['checked' => 0, 'sent' => 0, 'skipped' => 0, 'reasons' => []];

		foreach ($candidates as $invoice) {
			$stats['checked']++;
			$due = CarbonImmutable::parse($invoice->due_date)->startOfDay();
			$diff = (int) $today->diffInDays($due, false) * -1; // positive = overdue

			$kind = $this->mapKind($diff);
			if (! $kind) {
				$stats['skipped']++;
				$this->bumpReason($stats, 'not_in_window');
				continue;
			}

			$result = $this->sendIfEligible($invoice, $kind, false);
			if ($result === 'sent') {
				$stats['sent']++;
			} else {
				$stats['skipped']++;
				$this->bumpReason($stats, $result);
			}
		}

		return $stats;
	}

	/**
	 * Sends a specific reminder kind for an invoice, enforcing idempotency
	 * and tenant/relation preconditions. $manual=true bypasses the auto-
	 * reminders-enabled flag so the user can force-send from the UI.
	 * @return string  'sent' | 'no_email' | 'already_sent' | 'auto_disabled' | 'no_settings'
	 */
	public function sendIfEligible(BookkeepingInvoice $invoice, string $kind, bool $manual): string
	{
		if (! isset(self::KINDS[$kind])) {
			return 'invalid_kind';
		}

		if (BookkeepingInvoiceReminder::where('invoice_id', $invoice->id)->where('kind', $kind)->exists()) {
			return 'already_sent';
		}

		$settings = BookkeepingTenantSettings::find($invoice->tenant_id);
		if (! $settings) {
			return 'no_settings';
		}
		if (! $manual && ! $settings->auto_reminders_enabled) {
			return 'auto_disabled';
		}

		$relation = $invoice->relation;
		$to = $relation?->email;
		if (! $to) {
			return 'no_email';
		}

		try {
			Mail::to($to)->send(new InvoiceReminderMail($invoice, $settings, $kind));
		} catch (\Throwable $e) {
			Log::error('invoice reminder send failed', [
				'invoice_id' => $invoice->id,
				'kind' => $kind,
				'error' => $e->getMessage(),
			]);
			return 'send_failed';
		}

		BookkeepingInvoiceReminder::create([
			'tenant_id' => $invoice->tenant_id,
			'invoice_id' => $invoice->id,
			'kind' => $kind,
			'sent_at' => now(),
			'sent_to_email' => $to,
			'was_manual' => $manual,
		]);

		return 'sent';
	}

	private function mapKind(int $diffDays): ?string
	{
		foreach (self::KINDS as $kind => $threshold) {
			if ($diffDays === $threshold) {
				return $kind;
			}
		}
		return null;
	}

	private function bumpReason(array &$stats, string $reason): void
	{
		$stats['reasons'][$reason] = ($stats['reasons'][$reason] ?? 0) + 1;
	}
}
