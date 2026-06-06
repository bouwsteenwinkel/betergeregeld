<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Monitor\CronMonitor;
use App\Models\Monitor\CronPing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Heartbeat-endpoint voor bewaakte cron-jobs. De job roept de ping-URL aan met
 * zijn ping_token; het optionele {signal}-segment onderscheidt success (default,
 * de heartbeat), start (job begonnen) en fail (job klaar maar mislukt).
 *
 * Auth = het token in de URL — geen sessie, dus CSRF-exempt en buiten de
 * locale-prefix. GET én POST werken, zodat een kale curl/Invoke-WebRequest aan
 * het einde van een script volstaat. Exit-code, duur (ms) en bericht mogen mee
 * via query of body (code/ms/msg).
 */
class CronPingController extends Controller
{
	public function __invoke(Request $request, string $token, string $signal = 'success'): JsonResponse
	{
		$signal = strtolower($signal);

		if (! in_array($signal, ['success', 'start', 'fail'], true)) {
			return response()->json(['ok' => false, 'error' => 'invalid_signal'], 422);
		}

		$monitor = CronMonitor::query()
			->where('ping_token', $token)
			->where('is_active', true)
			->first();

		if (! $monitor) {
			return response()->json(['ok' => false, 'error' => 'invalid_token'], 404);
		}

		$exitCode   = $request->input('code', $request->input('exit_code'));
		$durationMs = $request->input('ms', $request->input('duration_ms'));
		$message    = $request->input('msg', $request->input('message'));

		// Een fail kan ook impliciet zijn: success-signaal met een exit-code != 0.
		if ($signal === 'success' && $exitCode !== null && (int) $exitCode !== 0) {
			$signal = 'fail';
		}

		$now = now();

		CronPing::create([
			'cron_monitor_id' => $monitor->id,
			'status'          => $signal,
			'exit_code'       => $exitCode !== null ? (int) $exitCode : null,
			'duration_ms'     => $durationMs !== null ? (int) $durationMs : null,
			'message'         => $message !== null ? mb_substr((string) $message, 0, 500) : null,
			'source_ip'       => $request->ip(),
			'received_at'     => $now,
		]);

		$update = [
			'last_status'  => $signal,
			'last_message' => $message !== null ? mb_substr((string) $message, 0, 500) : $monitor->last_message,
		];

		if ($signal === 'start') {
			$update['last_started_at'] = $now;
		} elseif ($signal === 'success') {
			// Heartbeat: dit verzet de deadline en wist een eventuele foutstand.
			$update['last_ping_at']     = $now;
			$update['last_duration_ms'] = $durationMs !== null ? (int) $durationMs : null;
			$update['last_exit_code']   = null;
			$update['last_message']     = $message !== null ? mb_substr((string) $message, 0, 500) : null;
		} elseif ($signal === 'fail') {
			// Geen heartbeat: last_ping_at blijft staan, zodat de checker een fout
			// meldt maar niet alsnog 'late' rekent op dezelfde run.
			$update['last_exit_code']   = $exitCode !== null ? (int) $exitCode : null;
			$update['last_duration_ms'] = $durationMs !== null ? (int) $durationMs : $monitor->last_duration_ms;
		}

		$monitor->forceFill($update)->save();

		return response()->json([
			'ok'          => true,
			'monitor'     => $monitor->name,
			'signal'      => $signal,
			'received_at' => $now->toIso8601String(),
		]);
	}
}
