<?php

namespace App\Console\Commands;

use App\Jobs\RunQualityScanJob;
use App\Models\Quality\MonitoredPage;
use Illuminate\Console\Command;

/**
 * Dispatcht kwaliteitsscans voor alle actieve pagina's waarvan de
 * scan-frequentie verstreken is sinds de laatste geslaagde scan.
 */
class QualityScanDispatchDue extends Command
{
	protected $signature = 'quality-scan:dispatch-due';

	protected $description = 'Dispatch kwaliteitsscans voor actieve pagina\'s die toe zijn aan een nieuwe scan.';

	public function handle(): int
	{
		$dispatched = 0;

		MonitoredPage::where('is_active', true)->get()->each(function (MonitoredPage $page) use (&$dispatched) {
			if ($page->isDue()) {
				RunQualityScanJob::dispatch($page);
				$dispatched++;
				$this->line("• {$page->label} ({$page->url})");
			}
		});

		$this->info("Klaar — {$dispatched} scan(s) gedispatcht.");

		return self::SUCCESS;
	}
}
