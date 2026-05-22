<?php

namespace App\Services\AccessGuard\Radar\Fingerprint;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use Throwable;

/**
 * Reads the Server, X-Powered-By and X-Generator response headers — the
 * cheapest possible fingerprint. Many shops strip these in production but
 * when they're present the data is high-confidence (the server itself
 * told us what it is).
 *
 * Confidence calibration:
 *   - Server header with explicit version (e.g. "nginx/1.24.0")  → 0.90
 *   - Server header without version       (e.g. "Apache")        → 0.55
 *   - X-Powered-By with version           (e.g. "PHP/8.3.10")    → 0.85
 *   - X-Powered-By without version                              → 0.50
 */
final class HttpHeaderFingerprinter implements Fingerprinter
{
	public function fingerprint(Asset $asset, HttpClient $http): array
	{
		if ($asset->url === null) {
			return [];
		}

		try {
			$resp = $http->request('HEAD', $asset->url);
		} catch (Throwable) {
			return [];
		}

		$results = [];
		if ($server = $resp->header('server')) {
			$results = array_merge($results, $this->parse('server', $server));
		}
		if ($poweredBy = $resp->header('x-powered-by')) {
			$results = array_merge($results, $this->parse('x-powered-by', $poweredBy));
		}
		if ($gen = $resp->header('x-generator')) {
			$results = array_merge($results, $this->parse('x-generator', $gen));
		}
		return $results;
	}

	/** @return DetectedSoftware[] */
	private function parse(string $headerName, string $value): array
	{
		$out = [];
		// Server: nginx/1.24.0 (Ubuntu); X-Powered-By: PHP/8.3.10
		// Strip parenthetical OS hints first — "(Ubuntu)" is platform info,
		// not a software product, and would otherwise show up as a bogus
		// detection. Then tokenize on whitespace, commas and semicolons.
		$cleaned = preg_replace('~\([^)]*\)~', '', $value) ?? $value;
		$tokens = preg_split('/[\s;,]+/', $cleaned, -1, PREG_SPLIT_NO_EMPTY) ?: [];
		foreach ($tokens as $tok) {
			if (! preg_match('~^([A-Za-z][A-Za-z0-9._\-]+)(?:/([0-9][0-9A-Za-z.\-_+]*))?$~', $tok, $m)) {
				continue;
			}
			$product = $m[1];
			$version = $m[2] ?? null;

			[$vendor, $productNorm] = $this->normaliseProduct($product);
			$confidence = match (true) {
				$headerName === 'server' && $version !== null => 0.90,
				$headerName === 'server'                      => 0.55,
				$version !== null                             => 0.85,
				default                                       => 0.50,
			};

			$out[] = new DetectedSoftware(
				vendor: $vendor,
				product: $productNorm,
				version: $version,
				detectionMethod: 'http_header',
				confidence: $confidence,
			);
		}
		return $out;
	}

	/** @return array{0:string,1:string} [vendor, product] */
	private function normaliseProduct(string $name): array
	{
		// Map well-known product strings to their canonical (vendor, product)
		// pair so the matcher's purl/CPE lookup hits regardless of casing or
		// vendor-prefix conventions in the response header.
		$key = strtolower($name);
		$known = [
			'nginx' => ['nginx', 'nginx'],
			'apache' => ['apache', 'httpd'],
			'apache-coyote' => ['apache', 'tomcat'],
			'iis' => ['microsoft', 'iis'],
			'microsoft-iis' => ['microsoft', 'iis'],
			'caddy' => ['caddyserver', 'caddy'],
			'litespeed' => ['litespeed', 'litespeed'],
			'openresty' => ['openresty', 'openresty'],
			'php' => ['php', 'php'],
			'cloudflare' => ['cloudflare', 'cloudflare'],
		];
		return $known[$key] ?? ['unknown', $name];
	}
}
