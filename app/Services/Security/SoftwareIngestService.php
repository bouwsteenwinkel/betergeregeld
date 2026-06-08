<?php

namespace App\Services\Security;

use App\Models\Security\SiteComponent;
use App\Models\Security\SiteVulnerability;
use App\Models\Seo\SeoProperty;
use Illuminate\Support\Facades\DB;

/**
 * Verwerkt de software-inventaris die de companion-plugin pusht: vervangt de
 * site_components + site_vulnerabilities van de site (volledige sync) en matcht
 * elke component tegen de Wordfence-feed.
 */
class SoftwareIngestService
{
	public function __construct(private readonly VulnerabilityMatcher $matcher)
	{
	}

	/**
	 * @param  array<int,array<string,mixed>>  $components
	 * @return array{components:int,updates:int,vulnerabilities:int}
	 */
	public function ingest(SeoProperty $site, array $components): array
	{
		return DB::transaction(function () use ($site, $components) {
			SiteComponent::where('property_id', $site->id)->delete();
			SiteVulnerability::where('property_id', $site->id)->delete();

			$updates = 0;
			$vulns = 0;

			foreach ($components as $c) {
				$type = in_array($c['type'] ?? '', ['core', 'plugin', 'theme'], true) ? $c['type'] : 'plugin';
				$slug = trim((string) ($c['slug'] ?? ''));
				if ($slug === '') {
					continue;
				}
				$version = isset($c['version']) && $c['version'] !== '' ? (string) $c['version'] : null;
				$name = isset($c['name']) ? mb_substr((string) $c['name'], 0, 190) : null;
				$hasUpdate = (bool) ($c['has_update'] ?? false);
				if ($hasUpdate) {
					$updates++;
				}

				$matches = $this->matcher->match($type, $slug, $version);

				SiteComponent::create([
					'property_id'    => $site->id,
					'type'           => $type,
					'slug'           => mb_substr($slug, 0, 190),
					'name'           => $name,
					'version'        => $version ? mb_substr($version, 0, 40) : null,
					'latest_version' => isset($c['latest_version']) ? mb_substr((string) $c['latest_version'], 0, 40) : null,
					'has_update'     => $hasUpdate,
					'wp_active'      => (bool) ($c['active'] ?? true),
					'vulnerable'     => $matches->isNotEmpty(),
					'vuln_count'     => $matches->count(),
					'reported_at'    => now(),
				]);

				foreach ($matches as $v) {
					$vulns++;
					SiteVulnerability::create([
						'property_id' => $site->id,
						'type'        => $type,
						'slug'        => mb_substr($slug, 0, 190),
						'name'        => $name ?: $slug,
						'version'     => $version ? mb_substr($version, 0, 40) : null,
						'title'       => mb_substr($v->title, 0, 255),
						'severity'    => $v->severity,
						'cve'         => $v->cve,
						'patched_in'  => $v->patched_in,
						'reference'   => $v->reference,
					]);
				}
			}

			DB::table('seo_properties')->where('id', $site->id)->update(['software_reported_at' => now()]);

			return ['components' => count($components), 'updates' => $updates, 'vulnerabilities' => $vulns];
		});
	}
}
