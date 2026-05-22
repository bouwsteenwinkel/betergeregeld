<?php

namespace App\Services\AccessGuard\Radar\Fingerprint;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use Throwable;

/**
 * Reads <meta name="generator" content="..."> from the asset's homepage.
 * This is how WordPress, Drupal, Joomla, Hugo, Ghost and many CMSes
 * advertise themselves — including the version number.
 *
 * Confidence calibration:
 *   - generator with explicit version → 0.85 (CMS *itself* claimed the version)
 *   - generator without version       → 0.50
 */
final class GeneratorMetaFingerprinter implements Fingerprinter
{
	public function fingerprint(Asset $asset, HttpClient $http): array
	{
		if ($asset->url === null) {
			return [];
		}

		try {
			$resp = $http->request('GET', $asset->url);
		} catch (Throwable) {
			return [];
		}
		if (! $resp->isHtml()) {
			return [];
		}

		// Use a non-greedy class instead of /s so a malformed unclosed tag
		// later in the document can't swallow megabytes of body. Limit the
		// scan to the first 256 KiB of HTML — generator tags live in <head>.
		$head = substr($resp->body, 0, 262_144);

		$out = [];
		$pattern = '~<meta\s+name=["\']generator["\']\s+content=["\']([^"\']{1,300})["\']~i';
		if (! preg_match_all($pattern, $head, $matches)) {
			return [];
		}

		foreach ($matches[1] as $content) {
			$content = trim(html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
			if ($content === '') continue;

			[$vendor, $product, $version] = $this->parseGenerator($content);
			$out[] = new DetectedSoftware(
				vendor: $vendor,
				product: $product,
				version: $version,
				detectionMethod: 'generator_meta',
				confidence: $version !== null ? 0.85 : 0.50,
			);
		}
		return $out;
	}

	/** @return array{0:string,1:string,2:?string} [vendor, product, version] */
	private function parseGenerator(string $content): array
	{
		// Common shapes:
		//   "WordPress 6.4.2"
		//   "Drupal 10 (https://www.drupal.org)"
		//   "Joomla! - Open Source Content Management"
		//   "Hugo 0.124.1"
		//   "Ghost 5.81"
		//   "Shopify"
		// Strategy: take leading word(s) as product, first bare semver-ish
		// token as version. Anything in parens or after a dash is dropped.
		$clean = preg_replace('~\s*\([^)]*\)~', '', $content) ?? $content;
		$clean = preg_replace('~\s*-\s*.*$~', '', $clean) ?? $clean;
		$clean = trim($clean);

		$version = null;
		if (preg_match('~\b(\d+(?:\.\d+){0,3}(?:[\-+][0-9A-Za-z.\-]+)?)\b~', $clean, $vm)) {
			$version = $vm[1];
			$clean = trim(str_replace($vm[0], '', $clean));
		}

		$productRaw = trim($clean) ?: 'unknown';
		$key = strtolower($productRaw);
		$known = [
			'wordpress'   => ['wordpress', 'wordpress'],
			'drupal'      => ['drupal', 'drupal'],
			'joomla!'     => ['joomla', 'joomla'],
			'joomla'      => ['joomla', 'joomla'],
			'hugo'        => ['gohugoio', 'hugo'],
			'ghost'       => ['ghost', 'ghost'],
			'shopify'     => ['shopify', 'shopify'],
			'magento'     => ['magento', 'magento'],
			'typo3 cms'   => ['typo3', 'typo3'],
			'typo3'       => ['typo3', 'typo3'],
			'wix.com website builder' => ['wix', 'wix'],
		];
		[$vendor, $product] = $known[$key] ?? ['unknown', $productRaw];
		return [$vendor, $product, $version];
	}
}
