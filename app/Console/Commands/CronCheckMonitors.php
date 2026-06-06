<?php

namespace App\Console\Commands;

use App\Models\Monitor\CronMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Vergelijkt per bewaakte cron-job de huidige toestand (currentCondition) met de
 * laatst-gemelde (alert_state) en mailt alléén bij een OVERGANG — net als de
 * server- en SEO-freshness-alerts. Zo één mail bij 'late'/'failed' worden, en
 * één bij herstel; geen herhaling elke run. Bedoeld om elke paar minuten te
 * draaien via de scheduler.
 */
class CronCheckMonitors extends Command
{
	protected $signature = 'cron:check-monitors';

	protected $description = 'Mail bij overgangen (ok/late/failed) van bewaakte cron-jobs.';

	public function handle(): int
	{
		$fallback = config('monitor.alert_email');
		$changes = 0;

		foreach (CronMonitor::query()->where('is_active', true)->where('alerts_enabled', true)->where('is_source', false)->get() as $monitor) {
			$condition = $monitor->currentCondition();
			$previous = $monitor->alert_state ?? 'ok';

			if ($condition === $previous) {
				continue;
			}

			$monitor->forceFill(['alert_state' => $condition, 'alerted_at' => now()])->save();
			$changes++;

			$this->info("{$monitor->name}: {$previous} → {$condition}");

			$to = $monitor->notify_email ?: $fallback;
			if (! $to) {
				continue;
			}

			[$subject, $body] = $this->message($monitor, $condition, $previous);
			Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
		}

		$this->info("Klaar — {$changes} overgang(en).");

		return self::SUCCESS;
	}

	/**
	 * @return array{0:string,1:string}
	 */
	private function message(CronMonitor $monitor, string $condition, string $previous): array
	{
		$scope = trim(($monitor->tenant?->name ?? 'Platform') . ($monitor->website ? ' · ' . $monitor->website : ''));
		$last = $monitor->last_ping_at
			? $monitor->last_ping_at->diffForHumans() . ' (' . $monitor->last_ping_at->toDateTimeString() . ')'
			: 'nooit een succesvolle ping';
		$cadans = "elke {$monitor->expected_period_minutes} min (+{$monitor->grace_minutes} min speling)";

		if ($condition === 'ok') {
			$subject = "[Cron] HERSTELD: {$monitor->name} draait weer";
			$body = "De cron-job '{$monitor->name}' ({$scope}) draait weer normaal (was: {$previous}).\n\n"
				. "Laatste succesvolle ping: {$last}\nVerwachte cadans: {$cadans}";

			return [$subject, $body];
		}

		if ($condition === 'failed') {
			$subject = "[Cron] ALERT: {$monitor->name} meldde een FOUT";
			$reden = "De job draaide maar meldde een fout"
				. ($monitor->last_exit_code !== null ? " (exit-code {$monitor->last_exit_code})" : '')
				. ($monitor->last_message ? ":\n{$monitor->last_message}" : '.');
		} else { // late
			$subject = "[Cron] ALERT: {$monitor->name} draaide NIET op tijd";
			$reden = "Er kwam geen succesvolle ping binnen de verwachte cadans — de job is mogelijk niet gedraaid.";
		}

		$body = "De cron-job '{$monitor->name}' ({$scope}) heeft een probleem.\n\n"
			. "{$reden}\n\n"
			. "Laatste succesvolle ping: {$last}\n"
			. "Verwachte cadans: {$cadans}\n"
			. "Uiterlijk verwacht vóór: {$monitor->dueAt()->toDateTimeString()}";

		return [$subject, $body];
	}
}
