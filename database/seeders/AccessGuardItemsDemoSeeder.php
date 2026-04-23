<?php

namespace Database\Seeders;

use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\BusinessSystem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessGuardItemsDemoSeeder extends Seeder
{
	public function run(): void
	{
		$tenantId = DB::table('tenants')->where('name', 'Demo BV')->value('id');
		if (! $tenantId) {
			$this->command?->warn('Demo BV tenant not found — skipping.');
			return;
		}

		$seed = [
			'Microsoft 365' => [
				['Basic licence', 'licence', 'Business Basic plan — email + web Office.'],
				['E3 licence', 'licence', 'Business Premium — volledige desktop apps.'],
				['E5 licence', 'licence', 'Enterprise — incl. security + compliance.'],
				['Global Admin', 'role', 'Tenant-wide admin rechten.'],
			],
			'Salesforce' => [
				['Admin', 'role', 'System administrator, volledige toegang.'],
				['Standard User', 'role', 'Standaard verkoop-user.'],
				['Read-only', 'role', 'Alleen lezen, geen mutaties.'],
				['Finance dashboard', 'role', 'Toegang tot finance rapportages.'],
			],
		];

		foreach ($seed as $systemName => $items) {
			$system = BusinessSystem::query()
				->where('tenant_id', $tenantId)
				->where('name', $systemName)
				->first();
			if (! $system) {
				$this->command?->warn("System '{$systemName}' not found for Demo BV — skipping.");
				continue;
			}

			if (AccessItem::query()->where('tenant_id', $tenantId)->where('system_id', $system->id)->exists()) {
				$this->command?->info("Items already exist for '{$systemName}' — skipping.");
				continue;
			}

			foreach ($items as $i => [$name, $type, $description]) {
				AccessItem::create([
					'tenant_id' => $tenantId,
					'system_id' => $system->id,
					'name' => $name,
					'type' => $type,
					'description' => $description,
					'is_active' => true,
					'sort_order' => ($i + 1) * 10,
				]);
			}
		}

		$this->command?->info('AccessGuard demo items seeded on Demo BV.');
	}
}
