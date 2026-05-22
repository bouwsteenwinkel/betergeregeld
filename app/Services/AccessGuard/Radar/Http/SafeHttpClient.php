<?php

namespace App\Services\AccessGuard\Radar\Http;

use Composer\CaBundle\CaBundle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use InvalidArgumentException;
use RuntimeException;

/**
 * Outbound HTTP client used by the Vulnerability Radar fingerprinters.
 * Hardened against SSRF, slowloris, and resource-exhaustion abuse:
 *
 *   - Only safe-by-spec methods (GET / HEAD / OPTIONS) — POST is unreachable
 *   - Hostname pre-resolved + checked against SsrfGuard's blocklist
 *   - Resolved IP pinned via curl.resolve so DNS-rebind cannot re-point us
 *   - Redirects followed *manually* so each new hop re-runs SSRF checks
 *     (Guzzle's built-in follower would let a 302 → http://10.0.0.5/ slip past)
 *   - Body capped at 5 MiB
 *   - Total timeout 10 s per hop, connect 5 s, max 3 redirects total
 *   - Distinct User-Agent so admins can identify radar traffic in their logs
 *   - Verifies TLS using composer/ca-bundle on Windows (system store on *nix)
 *
 * The client is *intentionally* small. Anything non-radar (auth, retries,
 * pagination) belongs in the regular Http facade — keeping this surface
 * narrow makes the SSRF audit story easy.
 */
final class SafeHttpClient implements HttpClient
{
	private const ALLOWED_METHODS = ['GET', 'HEAD', 'OPTIONS'];
	private const MAX_BODY_BYTES = 5 * 1024 * 1024;
	private const USER_AGENT = 'AccessGuardRadar/1.0 (+https://betergeregeld.nl/radar)';
	private const CONNECT_TIMEOUT_SEC = 5;
	private const TOTAL_TIMEOUT_SEC = 10;
	private const MAX_REDIRECTS = 3;

	public function __construct(
		private readonly HttpFactory $http,
		private readonly SsrfGuard $ssrf,
	) {}

	/**
	 * @param  array<string, string>  $extraHeaders
	 *
	 * @throws InvalidArgumentException  on disallowed method or SSRF violation
	 * @throws ConnectionException       on network failure
	 * @throws RuntimeException          on too many redirects
	 */
	public function request(string $method, string $url, array $extraHeaders = []): HttpResponse
	{
		$method = strtoupper($method);
		if (! in_array($method, self::ALLOWED_METHODS, true)) {
			throw new InvalidArgumentException("Method not allowed: {$method}");
		}

		$current = $url;
		$hops = 0;

		while (true) {
			[$host, $port] = $this->validateUrl($current);
			$ip = $this->ssrf->resolve($host);

			$resp = $this->base($extraHeaders)
				->withOptions([
					'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"]],
				])
				->send($method, $current);

			$status = $resp->status();
			if ($status >= 300 && $status < 400 && $resp->header('Location')) {
				if (++$hops > self::MAX_REDIRECTS) {
					throw new RuntimeException("Too many redirects from {$url}");
				}
				$location = $resp->header('Location');
				// Resolve relative redirects against the URL we just hit.
				$current = $this->resolveRelative($current, $location);
				continue;
			}

			$body = $resp->body();
			if (strlen($body) > self::MAX_BODY_BYTES) {
				$body = substr($body, 0, self::MAX_BODY_BYTES);
			}

			$lowerHeaders = [];
			foreach ($resp->headers() as $k => $v) {
				$lowerHeaders[strtolower($k)] = is_array($v) ? implode(', ', $v) : (string) $v;
			}

			return new HttpResponse(
				status: $status,
				headers: $lowerHeaders,
				body: $body,
				finalUrl: $current,
				resolvedIp: $ip,
			);
		}
	}

	/** @return array{0: string, 1: int} [host, port] */
	private function validateUrl(string $url): array
	{
		$parts = parse_url($url);
		if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
			throw new InvalidArgumentException("Invalid URL: {$url}");
		}
		$scheme = strtolower($parts['scheme']);
		if (! in_array($scheme, ['http', 'https'], true)) {
			throw new InvalidArgumentException("Unsupported scheme: {$parts['scheme']}");
		}
		$port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
		return [$parts['host'], (int) $port];
	}

	private function resolveRelative(string $base, string $location): string
	{
		// Absolute URL — return as-is, the next loop iteration will re-validate.
		if (preg_match('~^https?://~i', $location)) {
			return $location;
		}
		$parts = parse_url($base);
		$scheme = $parts['scheme'] ?? 'https';
		$host = $parts['host'] ?? '';
		$port = isset($parts['port']) ? ':' . $parts['port'] : '';
		if (str_starts_with($location, '/')) {
			return "{$scheme}://{$host}{$port}{$location}";
		}
		// Relative path — strip the basename of the original path, append.
		$path = $parts['path'] ?? '/';
		$dir = rtrim(substr($path, 0, strrpos($path, '/') ?: 0), '/');
		return "{$scheme}://{$host}{$port}{$dir}/{$location}";
	}

	/** @param  array<string, string>  $extraHeaders */
	private function base(array $extraHeaders): PendingRequest
	{
		// CaBundle is a transitive dep of Guzzle; on Windows the curl shipped
		// with scoop-php has no built-in CA store so we must hand it one.
		$verify = class_exists(CaBundle::class)
			? CaBundle::getSystemCaRootBundlePath()
			: true;

		return $this->http
			->withUserAgent(self::USER_AGENT)
			->withHeaders($extraHeaders)
			->connectTimeout(self::CONNECT_TIMEOUT_SEC)
			->timeout(self::TOTAL_TIMEOUT_SEC)
			->withoutRedirecting()
			->withOptions(['verify' => $verify]);
	}
}
