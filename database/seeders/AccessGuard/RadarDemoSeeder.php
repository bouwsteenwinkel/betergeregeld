<?php

namespace Database\Seeders\AccessGuard;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Drops in 8 demo Radar assets that cover the full spread of `kind`
 * values, so a fresh tenant immediately has a feel for what each
 * connection-type looks like and which ones can actually be scanned.
 *
 * Picked URLs deliberately:
 *   - All are public sites that respond to passive HTTP fingerprinting
 *   - WordPress.org, Drupal.org and a Joomla demo so we cover all 3 big CMSes
 *   - api.github.com to show a non-HTML JSON API
 *   - bouwsteenwinkel.nl as an own-site example
 *   - 2 non-scannable kinds (server, container) so users see what an
 *     "agent-managed" asset looks like in the form
 *
 * Idempotent: re-running this seeder upserts on (tenant_id, name).
 *
 * Invoke with:
 *   php artisan db:seed --class="Database\\Seeders\\AccessGuard\\RadarDemoSeeder"
 *
 * Optional env: RADAR_DEMO_TENANT_ID=<uuid> to target a specific tenant.
 * Without it, the first tenant in the DB is used.
 */
class RadarDemoSeeder extends Seeder
{
	public function run(): void
	{
		$tenantId = env('RADAR_DEMO_TENANT_ID') ?: Tenant::query()->value('id');
		if (! $tenantId) {
			$this->command?->warn('No tenant found — create one first or set RADAR_DEMO_TENANT_ID.');
			return;
		}

		foreach ($this->examples() as $example) {
			Asset::query()->updateOrCreate(
				['tenant_id' => $tenantId, 'name' => $example['name']],
				array_merge($example, [
					'tenant_id' => $tenantId,
					'discovered_at' => Carbon::now(),
				]),
			);
		}

		$this->command?->info('Seeded ' . count($this->examples()) . ' demo Radar assets for tenant ' . $tenantId);
	}

	/** @return array<int, array<string, mixed>> */
	private function examples(): array
	{
		return [
			// 1. WordPress reference site — clean WP detection target
			[
				'name' => 'WordPress.org (reference WP install)',
				'kind' => 'website',
				'url' => 'https://wordpress.org',
				'hostname' => 'wordpress.org',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'low',
				'status' => 'active',
				'notes' => 'Demo target — public WordPress.org site. Use to validate WP core + plugin fingerprinting.',
			],

			// 2. Drupal — second-most-common CMS in NL gov/edu
			[
				'name' => 'Drupal.org',
				'kind' => 'cms',
				'url' => 'https://www.drupal.org',
				'hostname' => 'www.drupal.org',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'low',
				'status' => 'active',
				'notes' => 'Demo target — should fingerprint as Drupal via <meta generator>.',
			],

			// 3. nginx project page — server-banner exposure example
			[
				'name' => 'nginx.org',
				'kind' => 'website',
				'url' => 'https://nginx.org',
				'hostname' => 'nginx.org',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'low',
				'status' => 'active',
				'notes' => 'Demo target — exposes Server: nginx/X.Y.Z header.',
			],

			// 4. Webshop archetype — Magento demo
			[
				'name' => 'Magento (commerce.adobe.com)',
				'kind' => 'webshop',
				'url' => 'https://magento.com',
				'hostname' => 'magento.com',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'medium',
				'status' => 'active',
				'notes' => 'Demo webshop entry. Real Magento storefronts should be added per customer.',
			],

			// 5. Joomla — third major CMS
			[
				'name' => 'Joomla.org',
				'kind' => 'cms',
				'url' => 'https://www.joomla.org',
				'hostname' => 'www.joomla.org',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'low',
				'status' => 'active',
			],

			// 6. JSON API — fingerprinters should mostly return empty
			[
				'name' => 'GitHub REST API',
				'kind' => 'api',
				'url' => 'https://api.github.com',
				'hostname' => 'api.github.com',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'medium',
				'status' => 'active',
				'notes' => 'Demo API endpoint. Demonstrates "kind=api" — passive scanner returns mostly headers, no CMS data.',
			],

			// 7. Customer-owned domain example
			[
				'name' => 'Bouwsteenwinkel.nl',
				'kind' => 'website',
				'url' => 'https://bouwsteenwinkel.nl',
				'hostname' => 'bouwsteenwinkel.nl',
				'environment' => 'production',
				'is_public' => 'yes',
				'auth_required' => 'no',
				'criticality' => 'high',
				'status' => 'active',
				'notes' => 'Eigen primaire domein — als demo van een "echte klant" asset.',
			],

			// 8. Non-scannable example: agent-managed server (no URL)
			[
				'name' => 'Webserver vps01.betergeregeld.nl (agent-only)',
				'kind' => 'server',
				'url' => null,
				'hostname' => 'vps01.betergeregeld.nl',
				'environment' => 'production',
				'is_public' => 'no',
				'auth_required' => 'yes',
				'criticality' => 'critical',
				'status' => 'active',
				'notes' => 'Demo van een server-asset zonder URL. http_probe scans worden overgeslagen; software-inventaris zou via een (toekomstige) on-host agent komen.',
			],
		];
	}
}
