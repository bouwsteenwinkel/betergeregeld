<?php

namespace App\Console\Commands;

use App\Models\Monitor\Check;
use App\Services\Monitor\CheckRunner;
use Illuminate\Console\Command;

/**
 * Voert alle actieve HTTP/TCP-uptimechecks uit. Bedoeld om elke paar minuten
 * te draaien; resultaten voeden de SLA-berekening per server.
 */
class MonitorRunChecks extends Command
{
	protected $signature = 'monitor:run-checks';

	protected $description = 'Voer alle actieve HTTP/TCP-uptimechecks uit.';

	public function handle(CheckRunner $runner): int
	{
		$checks = Check::query()->where('is_active', true)->get();

		foreach ($checks as $check) {
			$result = $runner->run($check);
			$code = $result->http_code ? " ({$result->http_code})" : '';
			$this->line("{$check->name}: {$result->status}{$code} — {$result->latency_ms}ms");
		}

		$this->info('Klaar — ' . $checks->count() . ' check(s) uitgevoerd.');

		return self::SUCCESS;
	}
}
