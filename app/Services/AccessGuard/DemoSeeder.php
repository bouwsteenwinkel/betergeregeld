<?php

namespace App\Services\AccessGuard;

use App\Models\AccessGuard\AccessCell;
use App\Models\AccessGuard\AccessItem;
use App\Models\AccessGuard\AccessProfile;
use App\Models\AccessGuard\BusinessSystem;
use App\Models\AccessGuard\Cycle;
use App\Models\AccessGuard\CycleItem;
use App\Models\AccessGuard\Person;
use App\Models\AccessGuard\PersonAccessItem;
use App\Models\AccessGuard\ProfileItem;
use App\Models\AccessGuard\Reminder;
use App\Models\AccessGuard\RiskFlag;
use App\Models\AccessGuard\VaultCredential;
use App\Services\AccessGuard\Ai\OpenAiClient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a realistic AccessGuard demo dataset for a tenant so the
 * first-run user can see every feature work in ~5 seconds instead
 * of filling everything by hand.
 *
 * Idempotent: detects "demo mode" via a single marker person email
 * (demo-ag-seed@<tenant>.demo) and wipes+reseeds on every call.
 */
class DemoSeeder
{
	private const MARKER_EMAIL_SUFFIX = '@demo-ag-seed.local';

	public function __construct(private readonly MatrixService $matrix) {}

	public function isDemoSeeded(string $tenantId): bool
	{
		return Person::query()
			->where('tenant_id', $tenantId)
			->where('email', 'like', '%' . self::MARKER_EMAIL_SUFFIX)
			->exists();
	}

	/**
	 * Wipe all demo data for this tenant.
	 * Only wipes rows marked with our suffix or created by the
	 * seeder — never touches real user data.
	 */
	public function wipe(string $tenantId): int
	{
		$peopleIds = Person::query()
			->where('tenant_id', $tenantId)
			->where('email', 'like', '%' . self::MARKER_EMAIL_SUFFIX)
			->pluck('id');

		if ($peopleIds->isEmpty()) return 0;

		$systemNames = ['Microsoft 365 (demo)', 'Slack (demo)', 'Salesforce (demo)', 'Exact Online (demo)', 'AWS Console (demo)', '1Password (demo)'];
		$systemIds = BusinessSystem::query()
			->where('tenant_id', $tenantId)
			->whereIn('name', $systemNames)
			->pluck('id');

		DB::transaction(function () use ($tenantId, $peopleIds, $systemIds) {
			// Matrix data
			AccessCell::query()->where('tenant_id', $tenantId)->whereIn('person_id', $peopleIds)->delete();
			PersonAccessItem::query()->where('tenant_id', $tenantId)->whereIn('person_id', $peopleIds)->delete();

			// Access items + the systems we created
			if ($systemIds->isNotEmpty()) {
				AccessItem::query()->where('tenant_id', $tenantId)->whereIn('system_id', $systemIds)->delete();
			}

			// Cycle + items
			$cycleIds = Cycle::query()->where('tenant_id', $tenantId)->where('title', 'like', '%(demo)%')->pluck('id');
			if ($cycleIds->isNotEmpty()) {
				CycleItem::query()->whereIn('cycle_id', $cycleIds)->delete();
				Cycle::query()->whereIn('id', $cycleIds)->delete();
			}

			// Risk + reminder with demo-ish subjects
			RiskFlag::query()->where('tenant_id', $tenantId)
				->whereIn('subject_id', $peopleIds->map(fn ($i) => (string) $i))
				->delete();
			Reminder::query()->where('tenant_id', $tenantId)
				->where(function ($q) use ($peopleIds) {
					$q->whereIn('subject_id', $peopleIds->map(fn ($i) => (string) $i));
				})->delete();

			// Profiles
			AccessProfile::query()->where('tenant_id', $tenantId)->where('name', 'like', '% (demo)')->delete();

			// Vault
			VaultCredential::query()->where('tenant_id', $tenantId)->where('name', 'like', '% (demo)')->delete();

			// People + systems last (FKs). Person uses SoftDeletes so we
			// force-delete to keep re-seeding clean.
			Person::query()->where('tenant_id', $tenantId)->whereIn('id', $peopleIds)->forceDelete();
			if ($systemIds->isNotEmpty()) {
				BusinessSystem::query()->where('tenant_id', $tenantId)->whereIn('id', $systemIds)->delete();
			}
		});

		return $peopleIds->count();
	}

	/**
	 * Seed the full demo dataset. Re-seeds cleanly if called twice.
	 *
	 * @return array<string,int> per-entity counts
	 */
	public function seed(string $tenantId, string $actorUserId): array
	{
		$this->wipe($tenantId);

		$now = CarbonImmutable::now();
		$seed = [];

		$seed['people'] = $this->seedPeople($tenantId);
		$seed['systems'] = $this->seedSystems($tenantId);
		$seed['items'] = $this->seedItems($tenantId);
		$seed['cells'] = $this->seedMatrix($tenantId);
		$seed['cycle'] = $this->seedCycle($tenantId, $actorUserId, $now);
		$seed['risks'] = $this->seedRisks($tenantId, $now);
		$seed['reminders'] = $this->seedReminders($tenantId, $now);
		$seed['profile'] = $this->seedProfile($tenantId);
		$seed['vault'] = $this->seedVault($tenantId, $actorUserId);

		return $seed;
	}

	private function seedPeople(string $tenantId): int
	{
		$now = CarbonImmutable::now();
		$rows = [
			['Emma', 'de Boer', 'emma.deboer' . self::MARKER_EMAIL_SUFFIX, 'Sales Manager', 'Sales', 'active'],
			['Thomas', 'Bakker', 'thomas.bakker' . self::MARKER_EMAIL_SUFFIX, 'Senior Developer', 'Engineering', 'active'],
			['Sanne', 'Visser', 'sanne.visser' . self::MARKER_EMAIL_SUFFIX, 'HR Lead', 'HR', 'active'],
			['Kees', 'Janssen', 'kees.janssen' . self::MARKER_EMAIL_SUFFIX, 'CFO', 'Finance', 'active'],
			['Noor', 'Kramer', 'noor.kramer' . self::MARKER_EMAIL_SUFFIX, 'Marketeer', 'Marketing', 'active'],
			['Lukas', 'de Wit', 'lukas.dewit' . self::MARKER_EMAIL_SUFFIX, 'Ex-Developer', 'Engineering', 'inactive'],
		];

		$data = [];
		foreach ($rows as $r) {
			$data[] = [
				'tenant_id' => $tenantId,
				'type' => 'employee',
				'first_name' => $r[0],
				'last_name' => $r[1],
				'email' => $r[2],
				'job_title' => $r[3],
				'department' => $r[4],
				'status' => $r[5],
				'created_at' => $now,
				'updated_at' => $now,
			];
		}
		Person::insert($data);
		return count($data);
	}

	private function seedSystems(string $tenantId): int
	{
		$now = CarbonImmutable::now();
		$rows = [
			['Microsoft 365 (demo)', 'saas', 'Primair e-mail + Office'],
			['Slack (demo)', 'comm', 'Team chat'],
			['Salesforce (demo)', 'saas', 'CRM'],
			['Exact Online (demo)', 'finance', 'Boekhouding'],
			['AWS Console (demo)', 'infra', 'Productie infrastructuur'],
			['1Password (demo)', 'security', 'Team vault'],
		];
		$data = [];
		foreach ($rows as $r) {
			$data[] = [
				'tenant_id' => $tenantId,
				'name' => $r[0],
				'category' => $r[1],
				'notes' => $r[2],
				'is_active' => true,
				'created_at' => $now,
				'updated_at' => $now,
			];
		}
		BusinessSystem::insert($data);
		return count($data);
	}

	private function seedItems(string $tenantId): int
	{
		$m365 = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', 'Microsoft 365 (demo)')->first();
		if (! $m365) return 0;
		$now = CarbonImmutable::now();
		$items = [
			['Basic licence', 'licence', 'E1 — email + web Office', 10],
			['E3 licence', 'licence', 'Business Premium', 20],
			['Global Admin', 'role', 'Tenant-wide admin', 30],
		];
		foreach ($items as $i => [$name, $type, $desc, $sort]) {
			AccessItem::create([
				'tenant_id' => $tenantId,
				'system_id' => $m365->id,
				'name' => $name,
				'type' => $type,
				'description' => $desc,
				'is_active' => true,
				'sort_order' => $sort,
			]);
		}
		return count($items);
	}

	private function seedMatrix(string $tenantId): int
	{
		$people = Person::query()->where('tenant_id', $tenantId)
			->where('email', 'like', '%' . self::MARKER_EMAIL_SUFFIX)
			->get(['id', 'job_title', 'status'])
			->keyBy('id');
		$systems = BusinessSystem::query()->where('tenant_id', $tenantId)
			->where('name', 'like', '%(demo)')
			->get(['id', 'name'])
			->keyBy('name');

		$cells = [];
		$now = CarbonImmutable::now();

		// Seed plausible access patterns
		$access = [
			'Sales Manager' => [
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'Salesforce (demo)' => 'has_access',
				'Exact Online (demo)' => 'no_access',
				'AWS Console (demo)' => 'no_access',
				'1Password (demo)' => 'has_access',
			],
			'Senior Developer' => [
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'Salesforce (demo)' => 'no_access',
				'Exact Online (demo)' => 'no_access',
				'AWS Console (demo)' => 'has_access',
				'1Password (demo)' => 'has_access',
			],
			'HR Lead' => [
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'Salesforce (demo)' => 'needs_review',
				'Exact Online (demo)' => 'has_access',
				'AWS Console (demo)' => 'no_access',
				'1Password (demo)' => 'no_access',
			],
			'CFO' => [
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'Salesforce (demo)' => 'has_access',
				'Exact Online (demo)' => 'has_access',
				'AWS Console (demo)' => 'needs_review',
				'1Password (demo)' => 'has_access',
			],
			'Marketeer' => [
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'Salesforce (demo)' => 'has_access',
				'Exact Online (demo)' => 'no_access',
				'AWS Console (demo)' => 'no_access',
				'1Password (demo)' => 'no_access',
			],
			'Ex-Developer' => [
				// Orphan: inactive person still has stuff → triggers sev-5 risk
				'Microsoft 365 (demo)' => 'has_access',
				'Slack (demo)' => 'has_access',
				'AWS Console (demo)' => 'has_access',
				'Salesforce (demo)' => 'no_access',
				'Exact Online (demo)' => 'no_access',
				'1Password (demo)' => 'no_access',
			],
		];

		foreach ($people as $p) {
			$title = $p->job_title;
			foreach ($access[$title] ?? [] as $sysName => $state) {
				$sys = $systems->get($sysName);
				if (! $sys) continue;
				$verifiedAgo = $state === 'has_access' ? rand(5, 120) : rand(30, 90);
				$cells[] = [
					'tenant_id' => $tenantId,
					'person_id' => $p->id,
					'system_id' => $sys->id,
					'access_state' => $state,
					'last_verified_at' => $now->subDays($verifiedAgo),
					'created_at' => $now,
					'updated_at' => $now,
				];
			}
		}

		if ($cells) {
			AccessCell::insert($cells);
		}

		// Wire up per-item states for the CFO on M365 (showcase items)
		$cfo = $people->first(fn ($p) => $p->job_title === 'CFO');
		$m365 = $systems->get('Microsoft 365 (demo)');
		if ($cfo && $m365) {
			$items = AccessItem::query()->where('tenant_id', $tenantId)->where('system_id', $m365->id)->get();
			foreach ($items as $it) {
				$state = $it->name === 'Global Admin' ? 'has_access'
					: ($it->name === 'E3 licence' ? 'has_access' : 'no_access');
				PersonAccessItem::create([
					'tenant_id' => $tenantId,
					'person_id' => $cfo->id,
					'system_id' => $m365->id,
					'access_item_id' => $it->id,
					'access_state' => $state,
					'last_verified_at' => $now->subDays(95), // deliberate: triggers stale_admin on Global Admin
				]);
			}
		}

		return count($cells);
	}

	private function seedCycle(string $tenantId, string $actorUserId, CarbonImmutable $now): int
	{
		$cycle = Cycle::create([
			'tenant_id' => $tenantId,
			'created_by_user_id' => $actorUserId,
			'title' => 'Q' . ceil($now->month / 3) . ' ' . $now->year . ' Access Review (demo)',
			'scope' => 'active_people',
			'status' => 'active',
			'starts_at' => $now->subDays(2)->toDateString(),
			'due_at' => $now->addDays(5)->toDateString(),
			'notes' => 'Voorbeeld-cyclus om te laten zien hoe reviews werken.',
		]);

		// Snapshot: active people × active systems → about 5 × 6 = 30 items
		$people = Person::query()->where('tenant_id', $tenantId)
			->whereIn('status', ['active', 'scheduled_in', 'scheduled_out'])
			->where('email', 'like', '%' . self::MARKER_EMAIL_SUFFIX)
			->get();
		$systems = BusinessSystem::query()->where('tenant_id', $tenantId)
			->where('name', 'like', '%(demo)')
			->where('is_active', true)
			->get();

		$rows = [];
		foreach ($people as $p) {
			foreach ($systems as $s) {
				$cell = AccessCell::query()
					->where('tenant_id', $tenantId)
					->where('person_id', $p->id)
					->where('system_id', $s->id)
					->first();
				$rows[] = [
					'tenant_id' => $tenantId,
					'cycle_id' => $cycle->id,
					'person_id' => $p->id,
					'system_id' => $s->id,
					'person_label' => trim($p->first_name . ' ' . $p->last_name),
					'system_label' => $s->name,
					'snapshot_state' => $cell?->access_state ?? 'unknown',
					'created_at' => $now,
					'updated_at' => $now,
				];
			}
		}
		if ($rows) {
			CycleItem::insert($rows);
		}
		return count($rows);
	}

	private function seedRisks(string $tenantId, CarbonImmutable $now): int
	{
		$exDev = Person::query()->where('tenant_id', $tenantId)
			->where('email', 'like', '%lukas.dewit%')->first();
		$cfo = Person::query()->where('tenant_id', $tenantId)
			->where('email', 'like', '%kees.janssen%')->first();
		if (! $exDev || ! $cfo) return 0;

		$rows = [
			[
				'kind' => 'orphan_access', 'severity' => 5,
				'subject_type' => 'person', 'subject_id' => (string) $exDev->id,
				'title' => 'Lukas de Wit',
				'description' => 'Inactieve persoon heeft nog 3 cellen op has_access.',
				'payload' => ['has_access_cells' => 3, 'has_access_items' => 0],
			],
			[
				'kind' => 'stale_admin', 'severity' => 4,
				'subject_type' => 'access_item', 'subject_id' => 'demo:cfo-global-admin',
				'title' => 'Kees Janssen — Global Admin (Microsoft 365 demo)',
				'description' => '95 dagen niet geverifieerd op een admin-achtige rol.',
				'payload' => ['days_unverified' => 95],
			],
		];

		foreach ($rows as $r) {
			RiskFlag::create([
				'tenant_id' => $tenantId,
				'kind' => $r['kind'], 'severity' => $r['severity'],
				'subject_type' => $r['subject_type'], 'subject_id' => $r['subject_id'],
				'title' => $r['title'], 'description' => $r['description'],
				'status' => 'open', 'detected_at' => $now,
				'payload' => $r['payload'],
			]);
		}
		return count($rows);
	}

	private function seedReminders(string $tenantId, CarbonImmutable $now): int
	{
		$cycle = Cycle::query()->where('tenant_id', $tenantId)->where('title', 'like', '%(demo)%')->first();
		if (! $cycle) return 0;
		Reminder::create([
			'tenant_id' => $tenantId,
			'kind' => 'cycle_due',
			'subject_type' => 'cycle',
			'subject_id' => (string) $cycle->id,
			'title' => $cycle->title,
			'description' => 'Deadline over 5 dagen — rond de review af.',
			'due_at' => $cycle->due_at,
			'status' => 'open',
		]);
		return 1;
	}

	private function seedProfile(string $tenantId): int
	{
		$profile = AccessProfile::create([
			'tenant_id' => $tenantId,
			'name' => 'Sales rol (demo)',
			'description' => 'Standaard toegang voor een Sales-medewerker',
			'is_active' => true,
		]);

		$m365 = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', 'Microsoft 365 (demo)')->first();
		$slack = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', 'Slack (demo)')->first();
		$sfdc = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', 'Salesforce (demo)')->first();
		$vault = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', '1Password (demo)')->first();

		foreach (array_filter([$m365, $slack, $sfdc, $vault]) as $sys) {
			ProfileItem::create([
				'tenant_id' => $tenantId,
				'profile_id' => $profile->id,
				'system_id' => $sys->id,
				'default_state' => 'has_access',
			]);
		}
		return 1;
	}

	private function seedVault(string $tenantId, string $actorUserId): int
	{
		$m365 = BusinessSystem::query()->where('tenant_id', $tenantId)->where('name', 'Microsoft 365 (demo)')->first();
		VaultCredential::create([
			'tenant_id' => $tenantId,
			'system_id' => $m365?->id,
			'name' => 'M365 tenant admin (demo)',
			'type' => 'password',
			'username' => 'admin@tenant.onmicrosoft.com',
			'secret_enc' => Crypt::encryptString('ThisIsAnExampleSecret42!'),
			'metadata' => ['note' => 'Voorbeeld-credential. Reveal om het in actie te zien.'],
			'rotation_interval_days' => 90,
			'last_rotated_at' => CarbonImmutable::now()->subDays(10),
			'created_by_user_id' => $actorUserId,
		]);
		return 1;
	}
}
