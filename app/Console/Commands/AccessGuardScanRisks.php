<?php

namespace App\Console\Commands;

use App\Services\AccessGuard\RiskScannerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccessGuardScanRisks extends Command
{
	protected $signature = 'accessguard:scan-risks {--tenant= : Only scan this tenant_id}';

	protected $description = 'Detect risk patterns (stale admins, orphan access, etc.) per tenant.';

	public function handle(RiskScannerService $scanner): int
	{
		$tenantIds = $this->option('tenant')
			? [$this->option('tenant')]
			: DB::table('tenants')->where('is_active', 1)->pluck('id')->all();

		$grand = [];
		foreach ($tenantIds as $tenantId) {
			$counts = $scanner->scan($tenantId);
			foreach ($counts as $k => $v) {
				$grand[$k] = ($grand[$k] ?? 0) + $v;
			}
			$total = array_sum($counts);
			if ($total > 0) {
				$this->line(sprintf('[%s] %d flags: %s',
					substr($tenantId, 0, 8),
					$total,
					implode(', ', array_map(fn ($k, $v) => "{$k}={$v}", array_keys($counts), array_values($counts))),
				));
			}
		}
		$this->info('Done. Grand totals: ' . json_encode($grand));
		return self::SUCCESS;
	}
}
