<?php

namespace App\Services\Monitor;

use App\Models\Monitor\CronMonitor;
use Illuminate\Console\Scheduling\Event;

/**
 * Koppelt interne Laravel-scheduler-jobs aan hun cron-monitor. De jobs worden
 * niet over HTTP gepingd maar in-process bijgewerkt (geen self-call, werkt ook
 * als de webserver plat ligt). De definitie staat in config('monitor.internal_crons').
 */
class CronMonitorPinger
{
	/**
	 * Hangt success-/failure-hooks aan een geplande taak en geeft 'm terug, zodat
	 * je in routes/console.php een Schedule::command(...) ket kunt omwikkelen:
	 *   CronMonitorPinger::watch(Schedule::command('x')->daily(), 'x');
	 * onSuccess vuurt bij exit-code 0, onFailure bij != 0; overgeslagen runs
	 * (withoutOverlapping) vuren geen van beide — die vangt de dead-man's-switch.
	 */
	public static function watch(Event $event, string $key): Event
	{
		return $event
			->onSuccess(fn () => self::record($key, 'success'))
			->onFailure(fn () => self::record($key, 'fail', 1));
	}

	/**
	 * Zorgt dat de monitor voor deze sleutel bestaat (idempotent). Geeft null
	 * als de sleutel niet in de registry staat, zodat een onbekende job geen
	 * lege monitor aanmaakt.
	 */
	public static function ensure(string $key): ?CronMonitor
	{
		$def = collect(config('monitor.internal_crons', []))->firstWhere('key', $key);

		if (! $def) {
			return null;
		}

		return CronMonitor::firstOrCreate(
			['source_key' => $key],
			[
				'name'                    => $def['name'] ?? $key,
				'website'                 => $def['website'] ?? null,
				'expected_period_minutes' => $def['period'] ?? 1440,
				'grace_minutes'           => $def['grace'] ?? 60,
				'description'             => 'Interne Laravel-scheduler-job (' . $key . ').',
			]
		);
	}

	/**
	 * Legt een ping vast voor de monitor van deze sleutel. No-op als de sleutel
	 * onbekend is.
	 */
	public static function record(string $key, string $signal = 'success', ?int $exitCode = null, ?string $message = null): void
	{
		$monitor = self::ensure($key);

		$monitor?->applyPing($signal, $exitCode, null, $message, 'scheduler');
	}
}
