<?php

namespace App\Services\Monitor;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Dunne client voor de SocketLabs v2 API. Gebruikt als monitoring-vangnet:
 * bevestigt dat de API + credentials werken (naast de realtime webhooks).
 *
 *   GET {base}/servers/{serverId}/reports/messages/detail   (Bearer-auth)
 */
class SocketLabsApi
{
	/**
	 * @return array{configured:bool, reachable?:bool, status?:int, error?:string}
	 */
	public function poll(): array
	{
		$key = config('socketlabs.api_key');
		$serverId = config('socketlabs.server_id');
		if (! $key || ! $serverId) {
			return ['configured' => false];
		}

		$base = rtrim((string) config('socketlabs.api_base', 'https://api.socketlabs.com/v2'), '/');
		$url = "{$base}/servers/{$serverId}/reports/messages/detail";
		$today = now()->toDateString();
		$verify = class_exists(CaBundle::class) ? CaBundle::getSystemCaRootBundlePath() : true;

		try {
			$resp = Http::withToken($key)
				->withOptions(['verify' => $verify])
				->timeout(15)
				->get($url, [
					'startDate'  => $today,
					'endDate'    => $today,
					'pageSize'   => 1,
					'pageNumber' => 0,
				]);

			return [
				'configured' => true,
				'reachable'  => $resp->successful(),
				'status'     => $resp->status(),
			];
		} catch (\Throwable $e) {
			return ['configured' => true, 'reachable' => false, 'error' => $e->getMessage()];
		}
	}
}
