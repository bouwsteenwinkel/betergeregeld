<?php

namespace App\Services\Monitor;

use App\Models\Monitor\SocketLabsEvent;
use App\Models\Monitor\SocketLabsStatus;
use Illuminate\Support\Carbon;

/**
 * Berekent de SocketLabs-monitoringtoestand over het ingestelde tijdvenster:
 * queue (Deferred/Queued-backlog), failures (bezorgfouten), complaints
 * (klachten/reputatie) en silence (geen events meer). Gedeeld door de
 * alert-evaluatie (monitor:check-alerts) en het Filament-dashboard.
 */
class SocketLabsEvaluator
{
	/** Is de SocketLabs-monitoring überhaupt in gebruik (config of data aanwezig)? */
	public function isActive(): bool
	{
		return (bool) config('socketlabs.webhook_secret')
			|| (bool) config('socketlabs.api_key')
			|| SocketLabsEvent::query()->exists();
	}

	/**
	 * @return array{
	 *   window:int, since:Carbon, counts:array<string,int>, total:int,
	 *   failure_rate:float, queue:string, failure:string, complaint:string,
	 *   silence:string, last_event_at:?Carbon
	 * }
	 */
	public function conditions(): array
	{
		$window = max(1, (int) config('socketlabs.window_minutes', 30));
		$since = now()->subMinutes($window);

		$rows = SocketLabsEvent::query()
			->where('occurred_at', '>=', $since)
			->selectRaw('type, COUNT(*) as c')
			->groupBy('type')
			->pluck('c', 'type');

		$count = fn (string $t) => (int) ($rows[$t] ?? 0);
		$delivered = $count('Delivered');
		$failed    = $count('Failed');
		$deferred  = $count('Deferred');
		$queued    = $count('Queued');
		$complaint = $count('Complaint');

		$volume = $delivered + $failed;
		$failureRate = $volume > 0 ? round($failed / $volume * 100, 1) : 0.0;

		// Queue loopt vol
		$queueState = ($deferred + $queued) >= (int) config('socketlabs.deferred_threshold', 50)
			? 'alert' : 'ok';

		// Storingen (alleen bij voldoende volume om ruis te voorkomen)
		$failureState = ($volume >= (int) config('socketlabs.min_volume', 20)
			&& $failureRate >= (float) config('socketlabs.failure_rate_pct', 20))
			? 'alert' : 'ok';

		// Reputatie
		$complaintState = $complaint >= (int) config('socketlabs.complaint_threshold', 1)
			? 'alert' : 'ok';

		$status = SocketLabsStatus::instance();

		// Stilte (dead-man) — alleen als ingeschakeld én er ooit verkeer was
		$silenceMinutes = (int) config('socketlabs.silence_minutes', 0);
		$lastEventAt = $status->last_event_at;
		$silenceState = 'ok';
		if ($silenceMinutes > 0 && $lastEventAt && $lastEventAt->lt(now()->subMinutes($silenceMinutes))) {
			$silenceState = 'alert';
		}

		// API-poll (vangnet): alleen alarm als de poll aanstaat én de laatste
		// poll faalde. Nooit polste = geen alarm (cron-monitoring dekt de scheduler).
		$apiState = (config('socketlabs.api_key') && $status->api_checked_at && $status->api_reachable === false)
			? 'alert' : 'ok';

		return [
			'window'        => $window,
			'since'         => $since,
			'counts'        => [
				'Delivered' => $delivered,
				'Failed'    => $failed,
				'Deferred'  => $deferred,
				'Queued'    => $queued,
				'Complaint' => $complaint,
			],
			'total'         => $delivered + $failed + $deferred + $queued + $complaint,
			'failure_rate'  => $failureRate,
			'queue'         => $queueState,
			'failure'       => $failureState,
			'complaint'     => $complaintState,
			'silence'       => $silenceState,
			'api'           => $apiState,
			'last_event_at' => $lastEventAt,
			'api_reachable' => $status->api_reachable,
			'api_checked_at'=> $status->api_checked_at,
		];
	}
}
