<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Seo\SeoProperty;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo-data voor het bureau "Rankdata": het bureau + een bureau-login, 3 demo-
 * klanten (tenants) met elk een klant-login, en realistische SEO- (Search
 * Console), PageSpeed- en uptime-cijfers zodat de dashboards meteen gevuld zijn.
 *
 * Volledig idempotent: opnieuw draaien ververst de cijfers, maakt geen duplicaten.
 *
 *   php artisan db:seed --class=RankdataDemoSeeder
 */
class RankdataDemoSeeder extends Seeder
{
	private const DEMO_PASSWORD = 'rankdata-demo';

	public function run(): void
	{
		$server = DB::table('monitor_servers')->value('id'); // organisatorische ouder voor de checks

		$agency = Agency::firstOrCreate(
			['slug' => 'rankdata'],
			['name' => 'Rankdata', 'contact_email' => 'team@rankdata.nl', 'primary_color' => '#4f46e5', 'is_active' => true]
		);

		// Bureau-beheerder (ziet alle Rankdata-klanten)
		User::updateOrCreate(
			['email' => 'demo@rankdata.nl'],
			[
				'tenant_id' => null,
				'agency_id' => $agency->id,
				'role' => 'agency',
				'password_hash' => Hash::make(self::DEMO_PASSWORD),
				'is_active' => true,
				'status' => 'active',
				'email_verified_at' => now(),
			]
		);

		foreach ($this->tenantDefs() as $def) {
			$tenant = Tenant::firstOrCreate(
				['agency_id' => $agency->id, 'name' => $def['name']],
				['plan' => 'pro', 'is_active' => true]
			);

			// Klant-login (ziet alleen eigen statistieken)
			User::updateOrCreate(
				['email' => $def['email']],
				[
					'tenant_id' => $tenant->id,
					'agency_id' => null,
					'role' => 'client',
					'password_hash' => Hash::make(self::DEMO_PASSWORD),
					'is_active' => true,
					'status' => 'active',
					'email_verified_at' => now(),
				]
			);

			$prop = SeoProperty::updateOrCreate(
				['tenant_id' => $tenant->id, 'site_url' => 'sc-domain:' . $def['domain']],
				['label' => $def['name'], 'is_active' => true, 'last_imported_date' => now()->subDay()->toDateString(), 'freshness_alert_state' => 'ok']
			);

			$this->seedSearchConsole($prop->id, $def);
			$this->seedPageSpeed($prop->id, $def['domain']);
			$this->seedUptime($server, $tenant->id, $def);
		}
	}

	/** Search Console: 30 dagen, per zoekwoord een rij/dag met groei- en positie-trend. */
	private function seedSearchConsole(int $propertyId, array $def): void
	{
		DB::table('seo_query_daily')->where('property_id', $propertyId)->delete();

		$rows = [];
		$now = now();
		for ($d = 29; $d >= 0; $d--) {
			$date = $now->copy()->subDays($d)->toDateString();
			$progress = (29 - $d) / 29;                       // 0 (oud) → 1 (recent)
			$growth = 1 + $progress * 0.45;                   // verkeer groeit door de maand
			foreach ($def['keywords'] as [$query, $path, $imprBase, $posBase]) {
				$impr = (int) round($imprBase * $def['scale'] * $growth * (0.78 + mt_rand(0, 44) / 100));
				if ($impr < 1) {
					continue;
				}
				$position = max(1.0, $posBase - $progress * 3.2 + mt_rand(-14, 14) / 10);
				$ctr = $position <= 3 ? mt_rand(80, 190) / 1000
					: ($position <= 10 ? mt_rand(20, 75) / 1000 : mt_rand(0, 18) / 1000);
				$clicks = (int) round($impr * $ctr);
				$page = 'https://' . $def['domain'] . $path;
				$rows[] = [
					'property_id' => $propertyId,
					'date' => $date,
					'query' => $query,
					'page' => $page,
					'clicks' => $clicks,
					'impressions' => $impr,
					'ctr' => $impr ? round($clicks / $impr, 4) : 0,
					'position' => round($position, 1),
					'created_at' => $now,
				];
			}
		}
		foreach (array_chunk($rows, 500) as $chunk) {
			DB::table('seo_query_daily')->insert($chunk);
		}
	}

	/** PageSpeed: 14 dagen, homepage, mobile + desktop, met lichte verbetering. */
	private function seedPageSpeed(int $propertyId, string $domain): void
	{
		DB::table('seo_psi_daily')->where('property_id', $propertyId)->delete();

		$url = 'https://' . $domain . '/';
		$rows = [];
		$now = now();
		for ($d = 13; $d >= 0; $d--) {
			$date = $now->copy()->subDays($d)->toDateString();
			$progress = (13 - $d) / 13;
			foreach (['mobile', 'desktop'] as $strategy) {
				$mobile = $strategy === 'mobile';
				$rows[] = [
					'property_id' => $propertyId,
					'url' => $url,
					'strategy' => $strategy,
					'date' => $date,
					'performance_score' => (int) min(100, ($mobile ? 52 : 80) + round($progress * 12) + mt_rand(-4, 6)),
					'lcp_ms' => (int) round(($mobile ? 3400 : 1800) - $progress * 600 + mt_rand(-200, 200)),
					'cls' => round(max(0, ($mobile ? 0.12 : 0.05) - $progress * 0.05 + mt_rand(-2, 4) / 100), 3),
					'inp_ms' => (int) round(($mobile ? 260 : 150) - $progress * 60 + mt_rand(-30, 30)),
					'ttfb_ms' => (int) round(($mobile ? 620 : 480) - $progress * 120 + mt_rand(-60, 60)),
					'seo_score' => mt_rand(90, 100),
					'accessibility_score' => mt_rand(84, 98),
					'best_practices_score' => mt_rand(85, 100),
					'created_at' => $now,
				];
			}
		}
		DB::table('seo_psi_daily')->insert($rows);
	}

	/** Uptime: een HTTP-check per klant + 7 dagen resultaten (2-uurlijks). */
	private function seedUptime(?string $serverId, string $tenantId, array $def): void
	{
		$target = 'https://' . $def['domain'] . '/';
		$now = now();

		$check = DB::table('monitor_checks')->where('tenant_id', $tenantId)->where('target', $target)->first();
		$checkId = $check->id ?? (string) Str::orderedUuid();
		$values = [
			'server_id' => $serverId,
			'tenant_id' => $tenantId,
			'name' => 'Website bereikbaar',
			'type' => 'http',
			'target' => $target,
			'expected_code' => 200,
			'timeout_seconds' => 10,
			'is_active' => 1,
			'last_status' => 'up',
			'last_code' => 200,
			'last_latency_ms' => mt_rand(140, 420),
			'last_checked_at' => $now,
			'updated_at' => $now,
		];
		if ($check) {
			DB::table('monitor_checks')->where('id', $checkId)->update($values);
		} else {
			DB::table('monitor_checks')->insert($values + ['id' => $checkId, 'created_at' => $now]);
		}

		DB::table('monitor_check_results')->where('check_id', $checkId)->delete();
		$rows = [];
		$total = 7 * 12; // 7 dagen × elke 2 uur
		// Eén klant krijgt een korte storing zodat de uptime-historie realistisch is.
		$incidentAt = $def['incident'] ? mt_rand(20, $total - 8) : -1;
		for ($i = $total; $i >= 0; $i--) {
			$at = $now->copy()->subHours($i * 2);
			$down = $incidentAt >= 0 && $i <= $incidentAt && $i > $incidentAt - 3;
			$rows[] = [
				'check_id' => $checkId,
				'checked_at' => $at,
				'status' => $down ? 'down' : 'up',
				'latency_ms' => $down ? null : mt_rand(120, 520),
				'http_code' => $down ? 503 : 200,
				'error' => $down ? 'HTTP 503 Service Unavailable' : null,
			];
		}
		foreach (array_chunk($rows, 500) as $chunk) {
			DB::table('monitor_check_results')->insert($chunk);
		}
	}

	private function tenantDefs(): array
	{
		return [
			[
				'name' => 'Koffiebranderij De Bonen',
				'domain' => 'bonen-koffie.nl',
				'email' => 'info@bonen-koffie.nl',
				'scale' => 1.0,
				'incident' => false,
				'keywords' => [
					['koffiebonen kopen', '/koffiebonen', 140, 6.5],
					['espresso bonen', '/espresso', 90, 8.0],
					['koffie abonnement', '/abonnement', 70, 5.0],
					['vers gebrande koffie', '/vers', 55, 11.0],
					['koffiebranderij', '/', 120, 4.0],
					['biologische koffie', '/biologisch', 45, 13.0],
				],
			],
			[
				'name' => 'FietsXL Amsterdam',
				'domain' => 'fietsxl-amsterdam.nl',
				'email' => 'info@fietsxl-amsterdam.nl',
				'scale' => 1.8,
				'incident' => true,
				'keywords' => [
					['elektrische fiets amsterdam', '/e-bikes', 220, 5.5],
					['stadsfiets kopen', '/stadsfietsen', 160, 7.0],
					['bakfiets huren', '/bakfiets-huur', 95, 4.5],
					['fietsenwinkel amsterdam', '/', 280, 3.5],
					['fiets reparatie amsterdam', '/reparatie', 130, 6.0],
					['tweedehands fiets', '/occasions', 110, 9.0],
				],
			],
			[
				'name' => 'Studio Noord Interieur',
				'domain' => 'studionoord-interieur.nl',
				'email' => 'info@studionoord-interieur.nl',
				'scale' => 0.7,
				'incident' => false,
				'keywords' => [
					['interieurontwerp', '/', 95, 7.5],
					['binnenhuisarchitect', '/diensten', 70, 9.0],
					['meubels op maat', '/meubels', 60, 6.0],
					['interieuradvies', '/advies', 50, 10.0],
					['woonkamer inrichten', '/inspiratie', 80, 12.0],
					['keuken ontwerp', '/keukens', 40, 8.5],
				],
			],
		];
	}
}
