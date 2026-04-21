<?php

namespace App\Services;

use MaxMind\Db\Reader;
use Throwable;

class GeoIpService
{
	private ?Reader $cityReader = null;
	private ?Reader $asnReader = null;

	public function __construct(?string $cityMmdbPath = null, ?string $asnMmdbPath = null)
	{
		$cityMmdbPath ??= (string) config('geoip.city_mmdb');
		$asnMmdbPath ??= (string) config('geoip.asn_mmdb');

		if ($cityMmdbPath && is_file($cityMmdbPath) && is_readable($cityMmdbPath)) {
			try {
				$this->cityReader = new Reader($cityMmdbPath);
			} catch (Throwable) {
				$this->cityReader = null;
			}
		}

		if ($asnMmdbPath && is_file($asnMmdbPath) && is_readable($asnMmdbPath)) {
			try {
				$this->asnReader = new Reader($asnMmdbPath);
			} catch (Throwable) {
				$this->asnReader = null;
			}
		}
	}

	public function isAvailable(): bool
	{
		return $this->cityReader !== null || $this->asnReader !== null;
	}

	public function lookup(string $ip): array
	{
		$out = [
			'country_code' => null,
			'country_name' => null,
			'region_code' => null,
			'city' => null,
			'postal' => null,
			'timezone' => null,
			'lat' => null,
			'lng' => null,
			'asn' => null,
			'as_org' => null,
		];

		if (! $this->isPublicIp($ip)) {
			return $out;
		}

		if ($this->cityReader) {
			try {
				$rec = $this->cityReader->get($ip);
				if (is_array($rec)) {
					$out['country_code'] = $rec['country']['iso_code'] ?? null;
					$out['country_name'] = $rec['country']['names']['nl'] ?? $rec['country']['names']['en'] ?? null;
					if (! empty($rec['subdivisions'][0]['iso_code'])) {
						$out['region_code'] = $rec['subdivisions'][0]['iso_code'];
					}
					$names = $rec['city']['names'] ?? [];
					$out['city'] = $names['nl'] ?? $names['en'] ?? (is_array($names) ? reset($names) : null) ?: null;
					$out['postal'] = $rec['postal']['code'] ?? null;
					$out['timezone'] = $rec['location']['time_zone'] ?? null;
					$out['lat'] = $rec['location']['latitude'] ?? null;
					$out['lng'] = $rec['location']['longitude'] ?? null;
				}
			} catch (Throwable) {
				// ignore
			}
		}

		if ($this->asnReader) {
			try {
				$rec = $this->asnReader->get($ip);
				if (is_array($rec)) {
					$out['asn'] = isset($rec['autonomous_system_number']) ? (int) $rec['autonomous_system_number'] : null;
					$out['as_org'] = $rec['autonomous_system_organization'] ?? null;
				}
			} catch (Throwable) {
				// ignore
			}
		}

		return $out;
	}

	private function isPublicIp(string $ip): bool
	{
		if ($ip === '' || $ip === '::1' || $ip === '127.0.0.1') {
			return false;
		}
		$flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
		return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
	}
}
