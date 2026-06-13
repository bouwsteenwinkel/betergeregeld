<?php

namespace App\Console\Commands;

use App\Models\Monitor\SocketLabsStatus;
use App\Services\Monitor\SocketLabsApi;
use Illuminate\Console\Command;

/**
 * Vangnet naast de SocketLabs-webhooks: pollt periodiek de v2 API en legt de
 * bereikbaarheid vast. De daadwerkelijke alert (bij ONbereikbaar) loopt via
 * monitor:check-alerts (state-based, dimensie 'api').
 */
class SocketLabsPoll extends Command
{
	protected $signature = 'monitor:socketlabs-poll';

	protected $description = 'Poll de SocketLabs v2 API (vangnet voor de webhook-monitoring).';

	public function handle(SocketLabsApi $api): int
	{
		$r = $api->poll();

		if (! ($r['configured'] ?? false)) {
			$this->info('SocketLabs-poll: geen API-key/ServerID geconfigureerd — overgeslagen.');
			return self::SUCCESS;
		}

		SocketLabsStatus::instance()->forceFill([
			'api_reachable'  => (bool) ($r['reachable'] ?? false),
			'api_checked_at' => now(),
		])->save();

		$detail = $r['status'] ?? ($r['error'] ?? '-');
		$this->info('SocketLabs-poll: ' . (($r['reachable'] ?? false) ? 'bereikbaar' : 'ONBEREIKBAAR') . " ({$detail})");

		return self::SUCCESS;
	}
}
