<?php

namespace Database\Seeders;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Person;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessGuardDemoSeeder extends Seeder
{
	public function run(): void
	{
		$tenantId = DB::table('tenants')->where('name', 'Demo BV')->value('id');
		if (! $tenantId) {
			$this->command?->warn('Demo BV tenant not found — skipping AccessGuard demo seed.');
			return;
		}

		// Idempotent: skip if already seeded.
		if (Person::query()->where('tenant_id', $tenantId)->exists()) {
			$this->command?->info('AccessGuard demo data already present for Demo BV — skipping.');
			return;
		}

		$people = [
			['first_name' => 'Jan', 'last_name' => 'de Vries', 'email' => 'jan@demo.bv', 'job_title' => 'CEO', 'department' => 'Directie', 'status' => 'active', 'type' => 'employee'],
			['first_name' => 'Lisa', 'last_name' => 'Jansen', 'email' => 'lisa@demo.bv', 'job_title' => 'Finance Manager', 'department' => 'Finance', 'status' => 'active', 'type' => 'employee'],
			['first_name' => 'Mohammed', 'last_name' => 'El Amrani', 'email' => 'mohammed@demo.bv', 'job_title' => 'Developer', 'department' => 'Engineering', 'status' => 'active', 'type' => 'employee'],
			['first_name' => 'Sophie', 'last_name' => 'van Dijk', 'email' => 'sophie@demo.bv', 'job_title' => 'Marketing Lead', 'department' => 'Marketing', 'status' => 'active', 'type' => 'employee'],
			['first_name' => 'Patrick', 'last_name' => 'Smit', 'email' => 'patrick@external.nl', 'job_title' => 'Freelance Designer', 'department' => 'Marketing', 'status' => 'active', 'type' => 'contractor'],
		];

		foreach ($people as &$p) {
			$p['tenant_id'] = $tenantId;
		}
		Person::insert($people);

		$systems = [
			['name' => 'Microsoft 365', 'category' => 'saas'],
			['name' => 'Salesforce', 'category' => 'saas'],
			['name' => 'AWS Console', 'category' => 'infra'],
			['name' => 'Exact Online', 'category' => 'finance'],
			['name' => 'Slack', 'category' => 'comm'],
			['name' => '1Password', 'category' => 'security'],
		];
		foreach ($systems as &$s) {
			$s['tenant_id'] = $tenantId;
			$s['is_active'] = true;
		}
		BusinessSystem::insert($systems);

		// Sprinkle some cells so the matrix isn't empty.
		$personIds = Person::query()->where('tenant_id', $tenantId)->pluck('id');
		$systemIds = BusinessSystem::query()->where('tenant_id', $tenantId)->pluck('id');
		$states = ['has_access', 'has_access', 'has_access', 'no_access', 'needs_review']; // weighted

		$rows = [];
		foreach ($personIds as $pid) {
			foreach ($systemIds as $sid) {
				if (random_int(0, 100) > 40) { // ~60% filled
					$rows[] = [
						'tenant_id' => $tenantId,
						'person_id' => $pid,
						'system_id' => $sid,
						'access_state' => $states[array_rand($states)],
						'last_verified_at' => now()->subDays(random_int(0, 60)),
						'created_at' => now(),
						'updated_at' => now(),
					];
				}
			}
		}
		if ($rows) {
			AccessCell::insert($rows);
		}

		$this->command?->info('Seeded AccessGuard demo: ' . count($people) . ' people, ' . count($systems) . ' systems, ' . count($rows) . ' cells.');
	}
}
