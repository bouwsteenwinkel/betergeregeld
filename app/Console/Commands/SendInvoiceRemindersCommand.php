<?php

namespace App\Console\Commands;

use App\Services\InvoiceReminderService;
use Illuminate\Console\Command;

class SendInvoiceRemindersCommand extends Command
{
	protected $signature = 'bookkeeping:send-invoice-reminders';

	protected $description = 'Send payment-reminder emails for overdue and soon-to-be-due invoices across all tenants.';

	public function handle(InvoiceReminderService $service): int
	{
		$stats = $service->runForAllTenants();

		$this->info(sprintf(
			'Reminders: %d checked, %d sent, %d skipped',
			$stats['checked'], $stats['sent'], $stats['skipped'],
		));
		if (! empty($stats['reasons'])) {
			$this->line('Skip reasons:');
			foreach ($stats['reasons'] as $reason => $count) {
				$this->line(sprintf('  %s: %d', $reason, $count));
			}
		}

		return self::SUCCESS;
	}
}
