<?php

namespace App\Console\Commands;

use App\Models\AccessGuard\DirectoryConnection;
use App\Services\AccessGuard\Directory\DirectorySyncService;
use App\Services\AccessGuard\Directory\MsGraphClient;
use Illuminate\Console\Command;
use Throwable;

class AccessGuardSyncDirectories extends Command
{
	protected $signature = 'accessguard:sync-directories {--tenant= : Only sync this tenant_id}';

	protected $description = 'Pull users from all connected directories (M365) into the AccessGuard Person table.';

	public function handle(DirectorySyncService $service): int
	{
		$query = DirectoryConnection::query()->where('status', '!=', 'disconnected');
		if ($tenant = $this->option('tenant')) {
			$query->where('tenant_id', $tenant);
		}
		$connections = $query->get();

		if ($connections->isEmpty()) {
			$this->line('No connected directories found.');
			return self::SUCCESS;
		}

		$exit = self::SUCCESS;
		foreach ($connections as $conn) {
			$this->line("Syncing {$conn->provider} for tenant " . substr($conn->tenant_id, 0, 8) . '…');
			try {
				$client = match ($conn->provider) {
					'm365' => new MsGraphClient($conn),
					default => throw new \RuntimeException("Unknown provider {$conn->provider}"),
				};
				$res = $service->sync($conn, $client);
				$this->info("  seen={$res['seen']} created={$res['created']} updated={$res['updated']}");
			} catch (Throwable $e) {
				$this->error('  ' . $e->getMessage());
				$exit = self::FAILURE;
			}
		}
		return $exit;
	}
}
