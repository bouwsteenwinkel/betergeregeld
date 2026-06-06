<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Monitor\CronMonitor;
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

		$applied = $monitor->applyPing(
			$signal,
			$exitCode !== null ? (int) $exitCode : null,
			$durationMs !== null ? (int) $durationMs : null,
			$message !== null ? (string) $message : null,
			$request->ip(),
		);

		return response()->json([
			'ok'          => true,
			'monitor'     => $monitor->name,
			'signal'      => $applied,
			'received_at' => now()->toIso8601String(),
		]);
	}
}
