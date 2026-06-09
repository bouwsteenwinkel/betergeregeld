<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Seo\SeoProperty;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Rankdata\SiteProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Maakt/ververst een apart DEMO-bureau met twee demoklanten + demo-sites,
 * gevuld met voorbeelddata over álle dimensies (SEO, uptime, PageSpeed,
 * kwaliteit, security, software/kwetsbaarheden, file-integriteit) — één
 * gezonde site en één met openstaande issues. Alles is_demo=true, zodat de
 * scanners het overslaan en de selector het kan verbergen. Idempotent.
 */
class RankdataSeedDemo extends Command
{
	protected $signature = 'rankdata:seed-demo';

	protected $description = 'Maak/ververs het Demo-bureau met demoklanten en voorbeelddata.';

	public const PASSWORD = 'demo-rankdata';

	public function handle(): int
	{
		$agency = Agency::updateOrCreate(
			['slug' => 'demo-bureau'],
			['name' => 'Demo-bureau', 'contact_email' => 'demo@betergeregeld.com',
				'primary_color' => '#7c3aed', 'subdomain' => 'demo', 'is_active' => true, 'is_demo' => true],
		);

		$this->client($agency, 'Demo Webshop', 'demo-webshop.nl', true);
		$this->client($agency, 'Demo Dienstverlener', 'demo-dienstverlener.nl', false);

		$this->info("Klaar — Demo-bureau + 2 demoklanten geseed. Logins (wachtwoord '" . self::PASSWORD . "'):");
		$this->line('  info@demo-webshop.nl / info@demo-dienstverlener.nl');

		return self::SUCCESS;
	}

	private function client(Agency $agency, string $name, string $domain, bool $healthy): void
	{
		$tenant = Tenant::updateOrCreate(
			['agency_id' => $agency->id, 'name' => $name],
			['plan' => 'pro', 'is_active' => true, 'is_demo' => true],
		);

		User::updateOrCreate(
			['email' => 'info@' . $domain],
			['tenant_id' => $tenant->id, 'agency_id' => null, 'role' => 'client',
				'password_hash' => Hash::make(self::PASSWORD), 'is_active' => true,
				'status' => 'active', 'email_verified_at' => now()],
		);

		$prop = SeoProperty::updateOrCreate(
			['tenant_id' => $tenant->id, 'site_url' => 'sc-domain:' . $domain],
			['label' => $name, 'type' => 'website', 'is_active' => true, 'is_demo' => true,
				'last_imported_date' => now()->subDay()->toDateString(),
				'software_reported_at' => now(), 'integrity_checked_at' => now(),
				'freshness_alert_state' => 'ok'],
		);

		app(SiteProvisioner::class)->provision($prop);
		DB::table('monitor_checks')->where('property_id', $prop->id)->update(['is_demo' => true]);
		// software_reported_at/integrity_checked_at zitten niet in fillable → direct zetten.
		DB::table('seo_properties')->where('id', $prop->id)->update([
			'software_reported_at' => now(), 'integrity_checked_at' => now(),
		]);

		$this->seedSeo($prop->id, $domain, $healthy);
		$this->seedPsi($prop->id, $domain, $healthy);
		$this->seedUptime($prop->id, $healthy);
		$this->seedQuality($prop->id, $healthy);
		$this->seedSecurity($prop->id, $domain, $healthy);
		$this->seedSoftware($prop->id, $healthy);
		$this->seedIntegrity($prop->id, $healthy);

		$this->line("  ✓ {$name} ({$domain})");
	}

	private function seedSeo(int $propId, string $domain, bool $healthy): void
	{
		DB::table('seo_query_daily')->where('property_id', $propId)->delete();
		$queries = [
			['q' => $domain . ' kopen', 'p' => '/'],
			['q' => 'beste ' . explode('.', $domain)[0], 'p' => '/diensten'],
			['q' => explode('.', $domain)[0] . ' ervaringen', 'p' => '/over-ons'],
			['q' => 'offerte aanvragen', 'p' => '/contact'],
		];
		$mult = $healthy ? 1.0 : 0.5;
		$rows = [];
		for ($d = 30; $d >= 1; $d--) {
			$date = now()->subDays($d)->toDateString();
			foreach ($queries as $i => $q) {
				$impr = (int) ((120 - $i * 20) * $mult) + random_int(0, 15);
				$clicks = (int) ($impr * (0.08 - $i * 0.01));
				$rows[] = [
					'property_id' => $propId, 'date' => $date,
					'query' => $q['q'], 'page' => 'https://' . $domain . $q['p'],
					'clicks' => max(0, $clicks), 'impressions' => $impr,
					'ctr' => $impr ? round($clicks / $impr, 4) : 0,
					'position' => round(($i + 1) * ($healthy ? 2.5 : 6.0) + random_int(0, 2), 1),
					'created_at' => now(),
				];
			}
		}
		foreach (array_chunk($rows, 500) as $chunk) {
			DB::table('seo_query_daily')->insert($chunk);
		}
	}

	private function seedPsi(int $propId, string $domain, bool $healthy): void
	{
		DB::table('seo_psi_daily')->where('property_id', $propId)->delete();
		$url = 'https://' . $domain . '/';
		$data = $healthy
			? ['mobile' => [88, 1800, 0.02, 120], 'desktop' => [96, 900, 0.01, 60]]
			: ['mobile' => [44, 5200, 0.18, 480], 'desktop' => [61, 2100, 0.09, 210]];
		foreach ($data as $strategy => [$perf, $lcp, $cls, $inp]) {
			DB::table('seo_psi_daily')->insert([
				'property_id' => $propId, 'url' => $url, 'strategy' => $strategy,
				'date' => now()->subDay()->toDateString(),
				'lcp_ms' => $lcp, 'cls' => $cls, 'inp_ms' => $inp, 'ttfb_ms' => (int) ($lcp / 4),
				'performance_score' => $perf, 'seo_score' => $healthy ? 95 : 78,
				'accessibility_score' => $healthy ? 96 : 82, 'best_practices_score' => $healthy ? 100 : 79,
				'created_at' => now(),
			]);
		}
	}

	private function seedUptime(int $propId, bool $healthy): void
	{
		$check = DB::table('monitor_checks')->where('property_id', $propId)->first();
		if (! $check) {
			return;
		}
		DB::table('monitor_check_results')->where('check_id', $check->id)->delete();
		$rows = [];
		// 7 dagen, elke 3 uur een sample (56 punten).
		for ($i = 56; $i >= 0; $i--) {
			$at = now()->subHours($i * 3);
			// Onhealthy: storing-streak rond 36-30 uur geleden.
			$down = ! $healthy && $i <= 12 && $i >= 10;
			$rows[] = [
				'check_id' => $check->id, 'checked_at' => $at,
				'status' => $down ? 'down' : 'up',
				'latency_ms' => $down ? null : ($healthy ? random_int(180, 420) : random_int(600, 1400)),
				'http_code' => $down ? 503 : 200, 'error' => $down ? 'HTTP 503' : null,
			];
		}
		DB::table('monitor_check_results')->insert($rows);
		$last = end($rows);
		DB::table('monitor_checks')->where('id', $check->id)->update([
			'last_status' => $last['status'], 'last_code' => $last['http_code'],
			'last_latency_ms' => $last['latency_ms'], 'last_checked_at' => $last['checked_at'],
			'alert_state' => $last['status'],
		]);
	}

	private function seedQuality(int $propId, bool $healthy): void
	{
		$page = DB::table('monitored_pages')->where('site_id', $propId)->first();
		if (! $page) {
			return;
		}
		DB::table('quality_scans')->where('monitored_page_id', $page->id)->delete();
		$scanId = DB::table('quality_scans')->insertGetId([
			'monitored_page_id' => $page->id, 'status' => 'completed',
			'started_at' => now()->subMinutes(3), 'completed_at' => now()->subMinutes(2),
			'http_status' => 200, 'fetch_duration_ms' => 620, 'raw_input_hash' => Str::random(64),
			'ai_model' => 'claude-haiku-4-5-20251001', 'ai_input_tokens' => 5000, 'ai_output_tokens' => 1500,
			'score' => $healthy ? 86 : 58, 'created_at' => now(), 'updated_at' => now(),
		]);
		$findings = $healthy
			? [['warn', 'middel', 'De meta description is iets te lang (165 tekens).', 'meta']]
			: [
				['fail', 'hoog', 'Geen H1-kop gevonden op de pagina.', 'h1'],
				['warn', 'middel', 'Meerdere afbeeldingen missen een alt-tekst.', 'img'],
				['warn', 'middel', 'De titel is te lang (78 tekens).', 'title'],
				['fail', 'hoog', 'Cookiebanner laadt tracking vóór toestemming.', 'cookie'],
			];
		foreach ($findings as $i => [$status, $sev, $text, $check]) {
			DB::table('quality_findings')->insert([
				'quality_scan_id' => $scanId, 'check_id' => $check, 'source' => 'ai',
				'status' => $status, 'severity' => $sev, 'finding' => $text,
				'created_at' => now(), 'updated_at' => now(),
			]);
		}
	}

	private function seedSecurity(int $propId, string $domain, bool $healthy): void
	{
		DB::table('security_findings')->whereIn('security_scan_id',
			DB::table('security_scans')->where('property_id', $propId)->pluck('id'))->delete();
		DB::table('security_scans')->where('property_id', $propId)->delete();

		$scanId = DB::table('security_scans')->insertGetId([
			'property_id' => $propId, 'status' => 'completed',
			'started_at' => now()->subMinutes(2), 'completed_at' => now()->subMinute(),
			'safe_browsing' => 'ok', 'blacklisted' => $healthy ? 0 : 1,
			'mixed_content_count' => $healthy ? 0 : 2, 'broken_link_count' => $healthy ? 0 : 3,
			'links_checked' => 32, 'created_at' => now(), 'updated_at' => now(),
		]);
		if (! $healthy) {
			$rows = [
				['blacklist', 'hoog', 'Vermeld op blacklist dbl.spamhaus.org (' . $domain . ')', null, null],
				['mixed_content', 'middel', 'Onveilige (http) resource op een https-pagina', 'http://' . $domain . '/img/banner.jpg', null],
				['mixed_content', 'middel', 'Onveilige (http) resource op een https-pagina', 'http://cdn.example.com/old.js', null],
				['broken_link', 'laag', 'Broken link (HTTP 404)', 'https://' . $domain . '/oude-actie', 404],
				['broken_link', 'laag', 'Broken link (HTTP 404)', 'https://' . $domain . '/vacature/oud', 404],
				['broken_link', 'laag', 'Broken link (HTTP 500)', 'https://partner.example.com/x', 500],
			];
			foreach ($rows as [$cat, $sev, $finding, $url, $code]) {
				DB::table('security_findings')->insert([
					'security_scan_id' => $scanId, 'category' => $cat, 'severity' => $sev,
					'finding' => $finding, 'url' => $url, 'code' => $code,
					'created_at' => now(), 'updated_at' => now(),
				]);
			}
		}
	}

	private function seedSoftware(int $propId, bool $healthy): void
	{
		DB::table('site_components')->where('property_id', $propId)->delete();
		DB::table('site_vulnerabilities')->where('property_id', $propId)->delete();

		$components = [
			['core', 'wordpress', 'WordPress', '6.7.1', '6.7.1', false],
			['plugin', 'woocommerce', 'WooCommerce', $healthy ? '9.5.1' : '8.2.0', '9.5.1', ! $healthy],
			['plugin', 'contact-form-7', 'Contact Form 7', $healthy ? '6.0.1' : '5.7.0', '6.0.1', ! $healthy],
			['plugin', 'yoast-seo', 'Yoast SEO', '24.1', '24.1', false],
			['theme', 'astra', 'Astra', '4.8.0', '4.8.0', false],
		];
		foreach ($components as [$type, $slug, $name, $version, $latest, $hasUpdate]) {
			$vuln = ! $healthy && in_array($slug, ['woocommerce', 'contact-form-7'], true);
			DB::table('site_components')->insert([
				'property_id' => $propId, 'type' => $type, 'slug' => $slug, 'name' => $name,
				'version' => $version, 'latest_version' => $latest, 'has_update' => $hasUpdate ? 1 : 0,
				'wp_active' => 1, 'vulnerable' => $vuln ? 1 : 0, 'vuln_count' => $vuln ? 1 : 0,
				'reported_at' => now(), 'created_at' => now(), 'updated_at' => now(),
			]);
		}
		if (! $healthy) {
			$vulns = [
				['plugin', 'woocommerce', 'WooCommerce', '8.2.0', 'SQL-injectie in productfilter', 'high', 'CVE-2026-31000', '8.2.2'],
				['plugin', 'contact-form-7', 'Contact Form 7', '5.7.0', 'Stored XSS via formulierveld', 'medium', 'CVE-2026-31001', '5.7.1'],
			];
			foreach ($vulns as [$type, $slug, $name, $version, $title, $sev, $cve, $fixed]) {
				DB::table('site_vulnerabilities')->insert([
					'property_id' => $propId, 'type' => $type, 'slug' => $slug, 'name' => $name,
					'version' => $version, 'title' => $title, 'severity' => $sev, 'cve' => $cve,
					'patched_in' => $fixed, 'reference' => 'https://www.wordfence.com/vuln/' . $cve,
					'created_at' => now(), 'updated_at' => now(),
				]);
			}
		}
	}

	private function seedIntegrity(int $propId, bool $healthy): void
	{
		DB::table('site_integrity_issues')->where('property_id', $propId)->delete();
		if ($healthy) {
			return;
		}
		foreach ([['modified', 'wp-includes/load.php'], ['unexpected', 'wp-includes/x9z.php']] as [$type, $path]) {
			DB::table('site_integrity_issues')->insert([
				'property_id' => $propId, 'type' => $type, 'path' => $path,
				'created_at' => now(), 'updated_at' => now(),
			]);
		}
	}
}
