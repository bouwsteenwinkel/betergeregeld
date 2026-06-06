<?php

namespace App\Filament\Pages;

use App\Models\Monitor\Server;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/**
 * Limited, tenant-facing SLA/status view. A tenant only ever sees the server
 * its own tenant is linked to (tenants.server_id) — current status plus
 * heartbeat-based uptime. Raw resource internals stay in the super-admin resource.
 */
class ServerStatus extends Page
{
	protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

	protected static string|\UnitEnum|null $navigationGroup = 'Monitoring';

	protected static ?string $navigationLabel = 'Status & SLA';

	protected static ?int $navigationSort = 20;

	protected string $view = 'filament.pages.server-status';

	public function getTitle(): string
	{
		return 'Status & SLA';
	}

	public static function shouldRegisterNavigation(): bool
	{
		return (bool) (auth()->user()?->tenant?->server_id);
	}

	public static function canAccess(): bool
	{
		return (bool) (auth()->user()?->tenant?->server_id);
	}

	public function getServer(): ?Server
	{
		return auth()->user()?->tenant?->server;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getStatusData(): array
	{
		$server = $this->getServer();

		if (! $server) {
			return [];
		}

		$map = [
			'online'  => ['Online', 'success'],
			'stale'   => ['Vertraagd', 'warning'],
			'offline' => ['Offline', 'danger'],
			'unknown' => ['Onbekend', 'gray'],
		];

		[$label, $color] = $map[$server->status()] ?? $map['unknown'];

		// Echte HTTP/TCP-checks zijn een hardere uptime-bron; val terug op de
		// heartbeat-meting als er (nog) geen checkresultaten zijn.
		$check24 = $server->checkUptimePercent(24);
		$check30 = $server->checkUptimePercent(24 * 30);

		return [
			'name'          => $server->name,
			'status_label'  => $label,
			'status_color'  => $color,
			'last_seen'     => $server->agent_last_seen_at,
			'uptime_24h'    => $check24 ?? $server->uptimePercent(24),
			'uptime_30d'    => $check30 ?? $server->uptimePercent(24 * 30),
			'uptime_source' => $check24 !== null ? 'checks' : 'heartbeat',
		];
	}
}
