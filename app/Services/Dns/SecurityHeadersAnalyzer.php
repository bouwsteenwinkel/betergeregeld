<?php

namespace App\Services\Dns;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Security-headers van een URL ophalen + scoren. Inspiratie:
 * securityheaders.com. Geen externe API — eigen HEAD-request.
 *
 * Gewogen score (max 100):
 *   - Strict-Transport-Security      30
 *   - Content-Security-Policy        25
 *   - X-Frame-Options                10
 *   - X-Content-Type-Options         10
 *   - Referrer-Policy                10
 *   - Permissions-Policy             10
 *   - Cross-Origin-Opener-Policy      5
 *
 * Letter-grade: 90+ = A, 75+ = B, 60+ = C, 40+ = D, anders = F.
 */
class SecurityHeadersAnalyzer
{
	public function analyze(string $url): array
	{
		if (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
			$url = 'https://' . $url;
		}

		try {
			$resp = Http::timeout(15)
				->withOptions([
					'verify'          => CaBundle::getSystemCaRootBundlePath(),
					'allow_redirects' => ['max' => 5, 'strict' => true, 'protocols' => ['http', 'https']],
				])
				->withUserAgent('BeterGeregeld-Headers-Analyzer/1.0')
				->get($url);
		} catch (\Throwable $e) {
			return [
				'url'     => $url,
				'error'   => 'Kon URL niet ophalen: ' . $e->getMessage(),
				'success' => false,
			];
		}

		$rawHeaders = $resp->headers();
		// Headers zijn arrays; we normaliseren naar de eerste waarde + lowercase key
		$headers = [];
		foreach ($rawHeaders as $key => $values) {
			$headers[strtolower($key)] = is_array($values) ? $values[0] : (string) $values;
		}

		$checks = [
			'strict-transport-security' => [
				'label'  => 'Strict-Transport-Security (HSTS)',
				'weight' => 30,
				'why'    => 'Forceert HTTPS — voorkomt downgrade-attacks. Mist betekent: aanvaller kan eerste request afluisteren.',
			],
			'content-security-policy' => [
				'label'  => 'Content-Security-Policy',
				'weight' => 25,
				'why'    => 'Beperkt wat browser mag laden — beste verdediging tegen XSS en data-exfiltratie.',
			],
			'x-frame-options' => [
				'label'  => 'X-Frame-Options',
				'weight' => 10,
				'why'    => 'Voorkomt clickjacking door iframing te blokkeren.',
			],
			'x-content-type-options' => [
				'label'  => 'X-Content-Type-Options',
				'weight' => 10,
				'why'    => '`nosniff` voorkomt MIME-sniffing attacks.',
			],
			'referrer-policy' => [
				'label'  => 'Referrer-Policy',
				'weight' => 10,
				'why'    => 'Beperkt referrer-info naar third-parties — privacy + lekken voorkomen.',
			],
			'permissions-policy' => [
				'label'  => 'Permissions-Policy',
				'weight' => 10,
				'why'    => 'Beperkt browser-features (camera, geolocation, etc.) per third-party context.',
			],
			'cross-origin-opener-policy' => [
				'label'  => 'Cross-Origin-Opener-Policy',
				'weight' => 5,
				'why'    => 'Isoleert browser-context — bescherming tegen Spectre-style side-channels.',
			],
		];

		$results = [];
		$score = 0;
		$maxScore = 0;
		foreach ($checks as $headerKey => $meta) {
			$maxScore += $meta['weight'];
			$present = isset($headers[$headerKey]);
			$results[] = [
				'header'  => str_replace('-', '-', ucwords($headerKey, '-')),
				'present' => $present,
				'value'   => $present ? $headers[$headerKey] : null,
				'weight'  => $meta['weight'],
				'label'   => $meta['label'],
				'why'     => $meta['why'],
			];
			if ($present) $score += $meta['weight'];
		}

		// Letter-grade
		$pct = $maxScore > 0 ? (int) round($score / $maxScore * 100) : 0;
		$grade = $pct >= 90 ? 'A' : ($pct >= 75 ? 'B' : ($pct >= 60 ? 'C' : ($pct >= 40 ? 'D' : 'F')));

		// Server-header (info-only, geen score)
		$server = $headers['server'] ?? null;
		$xPoweredBy = $headers['x-powered-by'] ?? null;

		return [
			'url'         => (string) $resp->effectiveUri(),
			'status'      => $resp->status(),
			'success'     => true,
			'score'       => $score,
			'max_score'   => $maxScore,
			'pct'         => $pct,
			'grade'       => $grade,
			'checks'      => $results,
			'server'      => $server,
			'x_powered_by'=> $xPoweredBy,
			'final_url'   => (string) $resp->effectiveUri(),
		];
	}
}
