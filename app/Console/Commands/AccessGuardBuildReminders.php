<?php

namespace App\Console\Commands;

use App\Services\AccessGuard\ReminderBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccessGuardBuildReminders extends Command
{
	protected $signature = 'accessguard:build-reminders {--tenant= : Only build for this tenant_id}';

	protected $description = 'Create upcoming-deadline reminders per tenant.';

	public function handle(ReminderBuilder $builder): int
	{
		$tenantIds = $this->option('tenant')
			? [$this->option('tenant')]
			: DB::table('tenants')->where('is_active', 1)->pluck('id')->all();

		$grand = [];
		foreach ($tenantIds as $tenantId) {
			$counts = $builder->build($tenantId);
			foreach ($counts as $k => $v) {
				$grand[$k] = ($grand[$k] ?? 0) + $v;
			}
			$total = array_sum($counts);
			if ($total > 0) {
				$this->line(sprintf('[%s] %d reminders: %s',
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
