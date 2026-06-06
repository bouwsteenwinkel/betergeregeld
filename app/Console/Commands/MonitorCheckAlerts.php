<?php

namespace App\Console\Commands;

use App\Models\Monitor\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Vergelijkt per server de huidige toestand met de laatst-gemelde (alert_state)
 * en mailt alleen bij een overgang — dus één mail bij offline-gaan, één bij
 * herstel, geen herhaling elke run. Bedoeld om elke paar minuten te draaien.
 */
class MonitorCheckAlerts extends Command
{
	protected $signature = 'monitor:check-alerts';

	protected $description = 'Mail bij offline/volle-schijf-overgangen van gemonitorde servers.';

	public function handle(): int
	{
		$to = config('monitor.alert_email');
		$servers = Server::query()->where('is_active', true)->where('alerts_enabled', true)->get();
		$changes = 0;

		foreach ($servers as $server) {
			$condition = $server->currentCondition();

			if ($condition === $server->alert_state) {
				continue;
			}

			$previous = $server->alert_state;
			$server->forceFill(['alert_state' => $condition, 'alerted_at' => now()])->save();
			$changes++;

			if ($to) {
				[$subject, $body] = $this->message($server, $condition, $previous);
				Mail::raw($body, fn ($m) => $m->to($to)->subject($subject));
			}

			$this->info("{$server->name}: {$previous} → {$condition}");
		}

		$this->info("Klaar — {$changes} overgang(en) van " . $servers->count() . ' server(s).');

		return self::SUCCESS;
	}

	/**
	 * @return array{0:string,1:string}
	 */
	private function message(Server $server, string $condition, string $previous): array
	{
		$lastSeen = $server->agent_last_seen_at?->diffForHumans() ?? 'nooit';
		$disk = $server->last_disk_percent !== null ? "{$server->last_disk_percent}%" : 'onbekend';
		$url = route('filament.admin.resources.monitor-servers.index');

		if ($condition === 'ok') {
			$subject = "[Monitoring] HERSTELD: {$server->name}";
			$body = "Server '{$server->name}' is weer normaal (was: {$previous}).\n\n"
				. "Laatste contact: {$lastSeen}\nSchijfgebruik: {$disk}\n\n{$url}";

			return [$subject, $body];
		}

		$reden = $condition === 'offline'
			? "De server stuurt geen heartbeat meer (laatste contact: {$lastSeen})."
			: "De systeemschijf zit vol: {$disk} gebruikt.";

		$subject = "[Monitoring] ALERT: {$server->name} — " . ($condition === 'offline' ? 'OFFLINE' : 'SCHIJF VOL');
		$body = "Server '{$server->name}' ({$server->ip_address}) heeft een probleem.\n\n"
			. "{$reden}\n\nLaatste contact: {$lastSeen}\nSchijfgebruik: {$disk}\n\nBekijk: {$url}";

		return [$subject, $body];
	}
}
