<?php

namespace App\Services\AccessGuard\Radar\Fingerprint;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\AccessGuard\Radar\Http\HttpClient;
use Throwable;

/**
 * WordPress-specific deep fingerprinter. Only runs (non-trivially) on
 * assets where we already suspect WordPress — checked via either a hint
 * in the URL/path or by short-circuiting on /wp-login.php existence.
 *
 * Detects:
 *   - WP core version          ← /readme.html (the file ships with `Version X.Y.Z`)
 *   - Active plugins + version ← <link>/<script> tags pointing into /wp-content/plugins/<slug>/...
 *   - Active theme + version   ← /wp-content/themes/<slug>/style.css `Version: X.Y.Z`
 *
 * All probes are passive. We never POST, never try logging in, never
 * touch admin endpoints — only assets the server willingly serves anonymously.
 */
final class WordPressFingerprinter implements Fingerprinter
{
	public function fingerprint(Asset $asset, HttpClient $http): array
	{
		if ($asset->url === null) {
			return [];
		}

		// Only proceed if WP is plausible. We check this cheaply via a HEAD
		// on /wp-login.php — most live WP installs return 200/302/403, never
		// 404. Sites that don't run WP cost us a single HEAD request.
		$base = rtrim($asset->url, '/');
		if (! $this->looksLikeWordPress($http, $base)) {
			return [];
		}

		$out = [];

		if ($core = $this->probeCore($http, $base)) {
			$out[] = $core;
		}

		try {
			$home = $http->request('GET', $asset->url);
			if ($home->isHtml()) {
				$out = array_merge($out, $this->parsePluginsFromHtml($home->body));
				$out = array_merge($out, $this->probeThemes($http, $base, $home->body));
			}
		} catch (Throwable) {
			// Already have core (if any) — keep it.
		}

		return $out;
	}

	private function looksLikeWordPress(HttpClient $http, string $base): bool
	{
		try {
			$resp = $http->request('HEAD', "{$base}/wp-login.php");
			// 200 = login page; 302 = redirect (common); 403 = blocked but exists.
			// 404 = definitely not WP.
			return in_array($resp->status, [200, 301, 302, 303, 307, 308, 403], true);
		} catch (Throwable) {
			return false;
		}
	}

	private function probeCore(HttpClient $http, string $base): ?DetectedSoftware
	{
		try {
			$resp = $http->request('GET', "{$base}/readme.html");
			if ($resp->status !== 200) return null;
			// readme.html ships with "Version X.Y.Z" in the page header.
			if (! preg_match('~Version\s+([0-9]+(?:\.[0-9]+){0,2})~i', $resp->body, $m)) {
				return null;
			}
			return new DetectedSoftware(
				vendor: 'wordpress',
				product: 'wordpress',
				version: $m[1],
				detectionMethod: 'wp_json',
				confidence: 0.90,
			);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Parses /wp-content/plugins/<slug>/... URLs out of HTML — every
	 * enqueued plugin asset goes through this path and many include a
	 * ?ver=X.Y.Z query so we get the version for free.
	 *
	 * @return DetectedSoftware[]
	 */
	private function parsePluginsFromHtml(string $html): array
	{
		$pattern = '~/wp-content/plugins/([a-z0-9][a-z0-9_\-]{0,80})/[^"\'?\s]+(?:\?(?:[^"\'\s]*?\bver=([0-9][0-9A-Za-z.\-_]*))?)?~i';
		if (! preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
			return [];
		}

		$bySlug = [];
		foreach ($matches as $m) {
			$slug = strtolower($m[1]);
			$ver = $m[2] ?? null;
			// Keep the first non-empty version we see for each slug;
			// don't overwrite a version with null on subsequent matches.
			if (! isset($bySlug[$slug]) || ($bySlug[$slug] === null && $ver !== null)) {
				$bySlug[$slug] = $ver;
			}
		}

		$out = [];
		foreach ($bySlug as $slug => $ver) {
			$out[] = new DetectedSoftware(
				vendor: 'wordpress',
				product: $slug,
				version: $ver,
				detectionMethod: 'wp_json',
				confidence: $ver !== null ? 0.80 : 0.55,
				path: "/wp-content/plugins/{$slug}/",
			);
		}
		return $out;
	}

	/**
	 * Find the active theme slug from HTML, then GET its style.css to read
	 * the official "Version: X.Y.Z" header that all WP themes ship.
	 *
	 * @return DetectedSoftware[]
	 */
	private function probeThemes(HttpClient $http, string $base, string $html): array
	{
		if (! preg_match_all('~/wp-content/themes/([a-z0-9][a-z0-9_\-]{0,80})/~i', $html, $m)) {
			return [];
		}

		$slugs = array_unique(array_map('strtolower', $m[1]));
		$out = [];
		foreach ($slugs as $slug) {
			try {
				$css = $http->request('GET', "{$base}/wp-content/themes/{$slug}/style.css");
				if ($css->status !== 200) continue;
				// Standard WP theme header. Spec defines "Version:" on its own line.
				$ver = null;
				if (preg_match('~^\s*Version:\s*([0-9][0-9A-Za-z.\-_]*)~mi', $css->body, $vm)) {
					$ver = $vm[1];
				}
				$out[] = new DetectedSoftware(
					vendor: 'wordpress-theme',
					product: $slug,
					version: $ver,
					detectionMethod: 'wp_json',
					confidence: $ver !== null ? 0.85 : 0.55,
					path: "/wp-content/themes/{$slug}/",
				);
			} catch (Throwable) {
				continue;
			}
		}
		return $out;
	}
}
