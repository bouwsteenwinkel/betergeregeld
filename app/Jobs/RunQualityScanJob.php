<?php

namespace App\Jobs;

use App\Models\Quality\MonitoredPage;
use App\Services\QualityScan\QualityScanNotifier;
use App\Services\QualityScan\QualityScanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Draait één kwaliteitsscan voor een pagina. Op de 'monitoring'-queue;
 * timeout 120s, 2 pogingen, 60s backoff.
 */
class RunQualityScanJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $timeout = 120;

	public int $tries = 2;

	public int $backoff = 60;

	public function __construct(public MonitoredPage $page)
	{
		$this->onQueue('monitoring');
	}

	public function handle(QualityScanService $service, QualityScanNotifier $notifier): void
	{
		if (! $this->page->is_active) {
			return;
		}

		$scan = $service->scan($this->page);

		if ($scan->status === 'completed') {
			$notifier->evaluate($scan);
		}
	}
}
