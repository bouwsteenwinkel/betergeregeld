<?php

namespace App\Services\Seo;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Wrapper rond Google's PageSpeed Insights v5 API. Public API — werkt
 * zonder OAuth, optioneel met API-key voor hogere quota.
 *
 * Returnt genormaliseerde metrics-shape:
 *   [
 *     'lcp_ms', 'cls', 'inp_ms', 'ttfb_ms',
 *     'performance_score', 'seo_score',
 *     'accessibility_score', 'best_practices_score',
 *   ]
 *
 * Bij fout returnt null en zet $lastError voor de caller om te loggen.
 */
class PageSpeedInsightsClient
{
	public const API_BASE = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

	public ?string $lastError = null;

	/**
	 * Run één PSI-call voor (url, strategy).
	 */
	public function fetch(string $url, string $strategy = 'mobile'): ?array
	{
		$this->lastError = null;

		$query = [
			'url'      => $url,
			'strategy' => $strategy,
			// Multiple categories: PSI accepteert herhaalde category-params.
			'category' => 'performance',
		];
		// config() i.p.v. kale env() zodat de key na `config:cache` op prod blijft werken.
		if ($key = (config('seo.psi_api_key') ?: env('PSI_API_KEY'))) {
			$query['key'] = $key;
		}

		try {
			$resp = Http::timeout(90)
				->acceptJson()
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
				->get(self::API_BASE . '?' . http_build_query($query)
					. '&category=seo&category=accessibility&category=best-practices');

			if (!$resp->ok()) {
				$this->lastError = 'PSI HTTP ' . $resp->status() . ': ' . substr($resp->body(), 0, 200);
				return null;
			}

			$j = $resp->json();

			$audits = $j['lighthouseResult']['audits']    ?? [];
			$cats   = $j['lighthouseResult']['categories'] ?? [];
			$le     = $j['loadingExperience']['metrics']   ?? [];

			// Core Web Vitals — prefer field-data, fall back to lab-data.
			$lcp = isset($le['LARGEST_CONTENTFUL_PAINT_MS']['percentile'])
				? (int) $le['LARGEST_CONTENTFUL_PAINT_MS']['percentile']
				: (isset($audits['largest-contentful-paint']['numericValue'])
					? (int) $audits['largest-contentful-paint']['numericValue']
					: null);

			$cls = isset($le['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'])
				? (float) $le['CUMULATIVE_LAYOUT_SHIFT_SCORE']['percentile'] / 100
				: (isset($audits['cumulative-layout-shift']['numericValue'])
					? (float) $audits['cumulative-layout-shift']['numericValue']
					: null);

			$inp = isset($le['INTERACTION_TO_NEXT_PAINT']['percentile'])
				? (int) $le['INTERACTION_TO_NEXT_PAINT']['percentile'] : null;
			$ttfb = isset($le['EXPERIMENTAL_TIME_TO_FIRST_BYTE']['percentile'])
				? (int) $le['EXPERIMENTAL_TIME_TO_FIRST_BYTE']['percentile'] : null;

			return [
				'lcp_ms'               => $lcp,
				'cls'                  => $cls,
				'inp_ms'               => $inp,
				'ttfb_ms'              => $ttfb,
				'performance_score'    => isset($cats['performance']['score']) ? (int) round($cats['performance']['score'] * 100) : null,
				'seo_score'            => isset($cats['seo']['score']) ? (int) round($cats['seo']['score'] * 100) : null,
				'accessibility_score'  => isset($cats['accessibility']['score']) ? (int) round($cats['accessibility']['score'] * 100) : null,
				'best_practices_score' => isset($cats['best-practices']['score']) ? (int) round($cats['best-practices']['score'] * 100) : null,
			];
		} catch (\Throwable $e) {
			$this->lastError = $e->getMessage();
			return null;
		}
	}
}
