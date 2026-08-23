<?php

namespace App\Http\Controllers\Monitor;

use App\Http\Controllers\Controller;
use App\Models\Monitor\DiskUsage;
use App\Models\Monitor\Server;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ontvangt een schijfmeting: één lijst mappen met hun omvang, van de collector.
 *
 * Zelfde token-beveiliging als de gewone ingest (Bearer per server), dus ook
 * CSRF-vrij en zonder sessie. Aparte endpoint omdat dit een heel ander ritme
 * heeft: de gewone meting gaat elke minuut, deze hooguit één keer per dag —
 * een map uitrekenen betekent hem volledig doorlopen.
 */
class IngestDiskController extends Controller
{
	public function __invoke(Request $request): JsonResponse
	{
		$token = (string) ($request->bearerToken() ?? $request->header('X-Monitor-Token', ''));

		$server = $token === '' ? null : Server::query()
			->where('ingest_token', $token)
			->where('is_active', true)
			->first();

		if (! $server) {
			return response()->json(['ok' => false, 'error' => 'invalid_token'], 403);
		}

		$data = $request->validate([
			'measured_at'      => ['nullable', 'date'],
			'entries'          => ['required', 'array', 'min:1', 'max:200'],
			'entries.*.soort'  => ['nullable', 'string', 'max:16'],
			'entries.*.pad'    => ['required', 'string', 'max:400'],
			'entries.*.bytes'  => ['required', 'numeric', 'min:0'],
		]);

		$moment = ! empty($data['measured_at']) ? Carbon::parse($data['measured_at']) : now();
		$nu = now();

		$rijen = [];
		foreach ($data['entries'] as $regel) {
			$rijen[] = [
				'server_id'   => $server->id,
				'measured_at' => $moment,
				'soort'       => $regel['soort'] ?? 'map',
				'pad'         => $regel['pad'],
				// Bewust via numeric + cast: PowerShell stuurt grote getallen als
				// double, en dan zou 'integer'-validatie ze afkeuren.
				'bytes'       => (int) $regel['bytes'],
				'created_at'  => $nu,
			];
		}

		DiskUsage::insert($rijen);

		return response()->json([
			'ok'          => true,
			'server'      => $server->name,
			'opgeslagen'  => count($rijen),
			'measured_at' => $moment->toIso8601String(),
		]);
	}
}
