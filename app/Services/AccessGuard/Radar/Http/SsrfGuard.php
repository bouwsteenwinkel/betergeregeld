<?php

namespace App\Services\AccessGuard\Radar\Http;

use InvalidArgumentException;

/**
 * Validates that a hostname resolves to a public IP address before any
 * outbound request fires. Pulled out of SafeHttpClient so it can be unit-
 * tested without mocking the HTTP layer.
 *
 * Blocked CIDR ranges (RFC 1918 + special-use):
 *   10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16  (private IPv4)
 *   127.0.0.0/8                                  (loopback)
 *   169.254.0.0/16                               (link-local / cloud metadata)
 *   100.64.0.0/10                                (CGNAT)
 *   0.0.0.0/8, 224.0.0.0/4, 240.0.0.0/4         (this-net, multicast, reserved)
 *   ::1/128, fc00::/7, fe80::/10, ::/128         (IPv6 equivalents)
 *
 * resolve() returns the chosen IP — callers should pass that IP back into
 * curl via CURLOPT_RESOLVE so the eventual TCP connection cannot be
 * redirected to a different address by a DNS-rebind attack.
 */
final class SsrfGuard
{
	/** @var string[] */
	private const BLOCKED_V4 = [
		'10.0.0.0/8',
		'172.16.0.0/12',
		'192.168.0.0/16',
		'127.0.0.0/8',
		'169.254.0.0/16',
		'100.64.0.0/10',
		'0.0.0.0/8',
		'224.0.0.0/4',
		'240.0.0.0/4',
	];

	/** @var string[] */
	private const BLOCKED_V6 = [
		'::1/128',
		'fc00::/7',
		'fe80::/10',
		'::/128',
	];

	/**
	 * Resolve $host to a single safe IP, or throw if any resolved address
	 * is private/internal. Picks the first public IPv4 if available, else
	 * the first public IPv6 — IPv4 is preferred because most fingerprinted
	 * webservers answer dual-stack but log v4.
	 *
	 * @throws InvalidArgumentException when host is empty, fails to resolve,
	 *         or any address falls in a blocked range
	 */
	public function resolve(string $host): string
	{
		$host = trim($host);
		if ($host === '') {
			throw new InvalidArgumentException('Empty host');
		}

		// Reject suspicious literals up-front (no point resolving)
		if (str_contains($host, ' ') || str_contains($host, "\n")) {
			throw new InvalidArgumentException("Invalid host: {$host}");
		}

		// If host is already an IP literal, validate it directly.
		if (filter_var($host, FILTER_VALIDATE_IP)) {
			$this->assertPublic($host);
			return $host;
		}

		// .local / .internal / .lan / .home / .corp are blocked by convention —
		// these names are commonly used for intranet hosts and have no business
		// being scanned even if they happen to resolve to a public IP.
		$lower = strtolower($host);
		foreach (['.local', '.internal', '.lan', '.home', '.corp', '.intra'] as $suffix) {
			if (str_ends_with($lower, $suffix)) {
				throw new InvalidArgumentException("Blocked TLD/suffix: {$host}");
			}
		}

		$v4 = @gethostbynamel($host) ?: [];
		$v6 = [];
		$records = @dns_get_record($host, DNS_AAAA) ?: [];
		foreach ($records as $r) {
			if (! empty($r['ipv6'])) $v6[] = $r['ipv6'];
		}

		$all = array_merge($v4, $v6);
		if ($all === []) {
			throw new InvalidArgumentException("Cannot resolve host: {$host}");
		}

		// Hard-fail if ANY resolved address is blocked. This catches the
		// "happy-eyeballs" attack where v4 is public but v6 points at ::1.
		foreach ($all as $ip) {
			$this->assertPublic($ip);
		}

		// Pick first v4 if present (most webservers), else first v6.
		return $v4[0] ?? $v6[0];
	}

	/** @throws InvalidArgumentException */
	private function assertPublic(string $ip): void
	{
		if (! filter_var($ip, FILTER_VALIDATE_IP)) {
			throw new InvalidArgumentException("Not an IP: {$ip}");
		}

		$ranges = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
			? self::BLOCKED_V4
			: self::BLOCKED_V6;

		foreach ($ranges as $cidr) {
			if ($this->ipInCidr($ip, $cidr)) {
				throw new InvalidArgumentException("Blocked address: {$ip} (in {$cidr})");
			}
		}

		// FILTER_FLAG_NO_PRIV_RANGE/NO_RES_RANGE catches anything our list
		// missed (e.g. future RFC-reserved blocks).
		if (! filter_var($ip, FILTER_VALIDATE_IP,
			FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			throw new InvalidArgumentException("Reserved/private address: {$ip}");
		}
	}

	private function ipInCidr(string $ip, string $cidr): bool
	{
		[$subnet, $bits] = explode('/', $cidr);
		$bits = (int) $bits;

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ipLong = ip2long($ip);
			$subnetLong = ip2long($subnet);
			if ($ipLong === false || $subnetLong === false) return false;
			$mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;
			return ($ipLong & $mask) === ($subnetLong & $mask);
		}

		// IPv6 — compare bit-by-bit on the binary representation
		$ipBin = inet_pton($ip);
		$subnetBin = inet_pton($subnet);
		if ($ipBin === false || $subnetBin === false) return false;

		$bytes = intdiv($bits, 8);
		$rem = $bits % 8;
		if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($subnetBin, 0, $bytes)) {
			return false;
		}
		if ($rem === 0) return true;

		$mask = chr(0xFF << (8 - $rem) & 0xFF);
		return (ord($ipBin[$bytes]) & ord($mask)) === (ord($subnetBin[$bytes]) & ord($mask));
	}
}
