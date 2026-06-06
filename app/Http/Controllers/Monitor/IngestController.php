<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Monitor\Metric;
use App\Models\Monitor\Server;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives a metrics sample from a host collector. Auth is a per-server token
 * (Bearer or X-Monitor-Token header) — no session, so this route is CSRF-exempt
 * and lives outside the locale prefix. Each push stores one immutable sample and
 * refreshes the server's heartbeat + denormalised last_* readings.
 */
class IngestController extends Controller
{
	public function __invoke(Request $request): JsonResponse
	{
		$token = $request->bearerToken() ?: $request->header('X-Monitor-Token');

		if (! $token) {
			return response()->json(['ok' => false, 'error' => 'missing_token'], 401);
		}

		$server = Server::query()
			->where('ingest_token', $token)
			->where('is_active', true)
			->first();

		if (! $server) {
			return response()->json(['ok' => false, 'error' => 'invalid_token'], 403);
		}

		$data = $request->validate([
			'collected_at'   => ['nullable', 'date'],
			'cpu_percent'    => ['nullable', 'numeric', 'between:0,100'],
			'mem_used_mb'    => ['nullable', 'integer', 'min:0'],
			'mem_total_mb'   => ['nullable', 'integer', 'min:0'],
			'disk_used_gb'   => ['nullable', 'numeric', 'min:0'],
			'disk_total_gb'  => ['nullable', 'numeric', 'min:0'],
			'uptime_seconds' => ['nullable', 'integer', 'min:0'],
			'load'           => ['nullable', 'array'],
			'agent_version'  => ['nullable', 'string', 'max:40'],
		]);

		$memPercent = ! empty($data['mem_total_mb'])
			? round(($data['mem_used_mb'] ?? 0) / $data['mem_total_mb'] * 100, 2)
			: null;

		$diskPercent = ! empty($data['disk_total_gb'])
			? round(($data['disk_used_gb'] ?? 0) / $data['disk_total_gb'] * 100, 2)
			: null;

		$now = now();
		$collectedAt = ! empty($data['collected_at']) ? Carbon::parse($data['collected_at']) : $now;

		Metric::create([
			'server_id'      => $server->id,
			'collected_at'   => $collectedAt,
			'cpu_percent'    => $data['cpu_percent'] ?? null,
			'mem_used_mb'    => $data['mem_used_mb'] ?? null,
			'mem_total_mb'   => $data['mem_total_mb'] ?? null,
			'mem_percent'    => $memPercent,
			'disk_used_gb'   => $data['disk_used_gb'] ?? null,
			'disk_total_gb'  => $data['disk_total_gb'] ?? null,
			'disk_percent'   => $diskPercent,
			'uptime_seconds' => $data['uptime_seconds'] ?? null,
			'load_json'      => $data['load'] ?? null,
		]);

		$server->forceFill([
			'agent_last_seen_at' => $now,
			'agent_version'      => $data['agent_version'] ?? $server->agent_version,
			'last_cpu_percent'   => $data['cpu_percent'] ?? null,
			'last_mem_percent'   => $memPercent,
			'last_disk_percent'  => $diskPercent,
		])->save();

		return response()->json([
			'ok'          => true,
			'server'      => $server->name,
			'received_at' => $now->toIso8601String(),
		]);
	}
}
