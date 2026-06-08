<?php

namespace App\Services\Rankdata;

use App\Models\Seo\SeoProperty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Richt de standaard-monitoring voor een site (seo_property) in: een
 * uptime-check, een homepage-kwaliteitspagina en een security-ingest-token.
 * Alles idempotent — veilig om bij elke aanmaak/bewerking te draaien.
 */
class SiteProvisioner
{
	public function provision(SeoProperty $site): void
	{
		$this->ensureCheck($site);
		$this->ensureMonitoredPage($site);
		$this->ensureToken($site);
	}

	private function domain(SeoProperty $site): string
	{
		return preg_replace('/^sc-domain:/', '', (string) $site->site_url);
	}

	private function ensureCheck(SeoProperty $site): void
	{
		if (DB::table('monitor_checks')->where('property_id', $site->id)->exists()) {
			return;
		}
		DB::table('monitor_checks')->insert([
			'id'              => (string) Str::orderedUuid(),
			'server_id'       => DB::table('monitor_servers')->value('id'),
			'tenant_id'       => $site->tenant_id,
			'property_id'     => $site->id,
			'name'            => 'Website bereikbaar',
			'type'            => 'http',
			'target'          => 'https://' . $this->domain($site) . '/',
			'expected_code'   => 200,
			'timeout_seconds' => 10,
			'is_active'       => 1,
			'created_at'      => now(),
			'updated_at'      => now(),
		]);
	}

	private function ensureMonitoredPage(SeoProperty $site): void
	{
		if (DB::table('monitored_pages')->where('site_id', $site->id)->exists()) {
			return;
		}
		DB::table('monitored_pages')->insert([
			'site_id'        => $site->id,
			'url'            => 'https://' . $this->domain($site) . '/',
			'label'          => 'Homepage',
			'is_active'      => 1,
			'scan_frequency' => 'weekly',
			'created_at'     => now(),
			'updated_at'     => now(),
		]);
	}

	private function ensureToken(SeoProperty $site): void
	{
		if ($site->security_ingest_token) {
			return;
		}
		$site->forceFill(['security_ingest_token' => Str::random(48)])->save();
	}
}
