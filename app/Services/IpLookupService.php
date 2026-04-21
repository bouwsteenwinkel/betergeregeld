<?php

namespace App\Services;

use Illuminate\Http\Request;
use Throwable;

/**
 * Slim port of V1's IP lookup. Returns the visitor's own IP plus geo/ASN
 * context when the GeoLite2 MMDBs are installed.
 */
class IpLookupService
{
	public function __construct(private readonly GeoIpService $geoIp) {}

	public function lookup(Request $request, bool $asnDetail = false): array
	{
		$ip = $request->ip() ?: '0.0.0.0';
		$version = $this->version($ip);
		$isValid = $version !== null;
		$isPrivate = $isValid ? $this->isPrivateOrReserved($ip) : null;

		$geo = $this->geoIp->lookup($ip);

		$rdns = null;
		if ($isValid && $isPrivate === false) {
			try {
				$h = @gethostbyaddr($ip);
				if (is_string($h) && $h !== $ip) {
					$rdns = mb_strimwidth($h, 0, 255, '…');
				}
			} catch (Throwable) {
				// ignore
			}
		}

		$network = $this->networkType($geo);
		$score = $this->score($network, $isPrivate);

		$server = $request->server();

		return [
			'ip' => $ip,
			'ip_version' => $version,
			'is_valid' => $isValid,
			'is_private' => $isPrivate,
			'reverse_dns' => $rdns,
			'geo_available' => $this->geoIp->isAvailable(),
			'geo' => [
				'country_code' => $geo['country_code'] ?? null,
				'country_name' => $geo['country_name'] ?? null,
				'region_code' => $geo['region_code'] ?? null,
				'city' => $geo['city'] ?? null,
				'postal' => $geo['postal'] ?? null,
				'timezone' => $geo['timezone'] ?? null,
			],
			'isp' => [
				'asn' => $geo['asn'] ?? null,
				'org' => $geo['as_org'] ?? null,
			],
			'asn_detail_visible' => $asnDetail,
			'network' => $network,
			'score' => $score,
			'signals' => [
				'tls' => ! empty($server['HTTPS']) && $server['HTTPS'] !== 'off',
				'xff_present' => ! empty($server['HTTP_X_FORWARDED_FOR']),
				'via_present' => ! empty($server['HTTP_VIA']),
			],
			'ts' => now()->toIso8601String(),
		];
	}

	private function version(string $ip): ?string
	{
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return 'ipv4';
		}
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return 'ipv6';
		}
		return null;
	}

	private function isPrivateOrReserved(string $ip): bool
	{
		if (! filter_var($ip, FILTER_VALIDATE_IP)) {
			return false;
		}
		return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
	}

	private function networkType(array $geo): array
	{
		$org = strtolower((string) ($geo['as_org'] ?? ''));
		$dcKeywords = ['amazon', 'aws', 'google', 'gcp', 'microsoft', 'azure', 'digitalocean', 'ovh', 'hetzner', 'linode', 'vultr', 'cloudflare'];
		$isDatacenter = false;
		$providerHint = null;
		foreach ($dcKeywords as $kw) {
			if ($org !== '' && str_contains($org, $kw)) {
				$isDatacenter = true;
				$providerHint = $kw;
				break;
			}
		}
		$isMobile = $org !== '' && (str_contains($org, 'mobile') || str_contains($org, 'vodafone') || str_contains($org, 't-mobile') || str_contains($org, 'kpn mob'));

		return [
			'label' => $isDatacenter ? 'datacenter' : ($isMobile ? 'mobile' : 'fixed-or-wifi'),
			'is_mobile' => $isMobile,
			'is_datacenter' => $isDatacenter,
			'provider_hint' => $providerHint,
		];
	}

	private function score(array $network, ?bool $isPrivate): array
	{
		$v = 85;
		$reasons = [];
		if ($isPrivate === true) {
			$v -= 30;
			$reasons[] = 'private_ip';
		}
		if ($network['is_datacenter']) {
			$v -= 10;
			$reasons[] = 'datacenter';
		}
		$v = max(0, min(100, $v));
		return [
			'value' => $v,
			'label' => $v >= 90 ? 'high' : ($v >= 75 ? 'good' : ($v >= 55 ? 'medium' : 'low')),
			'reasons' => $reasons,
		];
	}
}
