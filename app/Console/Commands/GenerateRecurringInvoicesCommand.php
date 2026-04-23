<?php

namespace App\Console\Commands;

use App\Services\RecurringInvoiceGenerator;
use Illuminate\Console\Command;

class GenerateRecurringInvoicesCommand extends Command
{
	protected $signature = 'bookkeeping:generate-recurring-invoices';

	protected $description = 'Generate invoices from recurring templates whose next_run_at is due, and optionally email them.';

	public function handle(RecurringInvoiceGenerator $generator): int
	{
		$stats = $generator->runDue();

		$this->info(sprintf(
			'Recurring: %d checked, %d generated, %d emailed, %d failed',
			$stats['checked'], $stats['generated'], $stats['sent'], $stats['failed'],
		));
		foreach ($stats['log'] as $row) {
			$this->line('  ' . json_encode($row));
		}

		return self::SUCCESS;
	}
}
