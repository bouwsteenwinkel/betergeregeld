<?php

namespace App\Services\Security;

use App\Models\Security\SecurityFinding;
use App\Models\Security\SecurityScan;
use App\Models\Seo\SeoProperty;

/**
 * Orkestreert één security-scan (fase 1, agent-loos) voor een site: Safe
 * Browsing + DNS-blacklist + mixed-content/broken-links. Schrijft een
 * scan-record met samenvatting + losse findings.
 */
class SecurityScanService
{
	public function __construct(
		private readonly SafeBrowsingClient $safeBrowsing,
		private readonly BlacklistChecker $blacklist,
		private readonly LinkCrawler $crawler,
	) {
	}

	public function scan(SeoProperty $site): SecurityScan
	{
		$url = $this->homepageUrl($site);
		$host = (string) parse_url($url, PHP_URL_HOST);

		$scan = SecurityScan::create([
			'property_id' => $site->id,
			'status'      => 'running',
			'started_at'  => now(),
		]);

		try {
			$findings = [];

			$sb = $this->safeBrowsing->check($url);
			foreach ($sb['threats'] as $threat) {
				$findings[] = ['category' => 'safe_browsing', 'severity' => 'hoog',
					'finding' => 'Google Safe Browsing-melding: ' . $threat, 'url' => $url];
			}

			$bl = $this->blacklist->check($host);
			foreach ($bl['hits'] as $hit) {
				$findings[] = ['category' => 'blacklist', 'severity' => 'hoog',
					'finding' => 'Vermeld op blacklist ' . $hit['list'] . ' (' . $hit['target'] . ')'];
			}

			$crawl = $this->crawler->scan($url);
			foreach ($crawl['mixed_content'] as $resource) {
				$findings[] = ['category' => 'mixed_content', 'severity' => 'middel',
					'finding' => 'Onveilige (http) resource op een https-pagina', 'url' => mb_substr($resource, 0, 1000)];
			}
			foreach ($crawl['broken'] as $broken) {
				$findings[] = ['category' => 'broken_link', 'severity' => 'laag',
					'finding' => 'Broken link (' . ($broken['code'] ? 'HTTP ' . $broken['code'] : 'geen reactie') . ')',
					'url' => mb_substr($broken['url'], 0, 1000), 'code' => $broken['code'] ?: null];
			}

			foreach ($findings as $f) {
				SecurityFinding::create(['security_scan_id' => $scan->id] + $f);
			}

			$scan->update([
				'status'              => 'completed',
				'completed_at'        => now(),
				'safe_browsing'       => $sb['status'],
				'blacklisted'         => $bl['listed'],
				'mixed_content_count' => count($crawl['mixed_content']),
				'broken_link_count'   => count($crawl['broken']),
				'links_checked'       => $crawl['links_checked'],
				'error_message'       => $crawl['error'],
			]);
		} catch (\Throwable $e) {
			$scan->update(['status' => 'failed', 'completed_at' => now(), 'error_message' => mb_substr($e->getMessage(), 0, 500)]);
		}

		return $scan->fresh();
	}

	private function homepageUrl(SeoProperty $site): string
	{
		$domain = preg_replace('/^sc-domain:/', '', (string) $site->site_url);
		if (preg_match('#^https?://#i', $domain)) {
			return rtrim($domain, '/') . '/';
		}

		return 'https://' . $domain . '/';
	}
}
