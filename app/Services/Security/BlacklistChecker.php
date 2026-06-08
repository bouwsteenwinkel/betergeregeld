<?php

namespace App\Services\Security;

/**
 * Controleert een host tegen DNS-blacklists (DNSBL): domein-gebaseerd op de
 * host en IP-gebaseerd op het opgeloste A-record. Een "hit" is een A-record in
 * 127.0.0.0/8 — behalve 127.255.255.x, dat zijn fout-/blokkadecodes van de
 * lijst zelf (bijv. Spamhaus bij publieke resolvers).
 *
 * Let op: Spamhaus' gratis lijsten blokkeren grootschalig gebruik via publieke
 * resolvers; voor volume is een eigen DQS-key nodig. Bij blokkade telt het hier
 * (terecht) niet als hit.
 */
class BlacklistChecker
{
	/**
	 * @return array{listed:bool, hits:array<int,array{list:string,target:string}>}
	 */
	public function check(string $host): array
	{
		$host = strtolower(trim($host));
		$config = (array) config('security.dnsbl');
		$hits = [];

		foreach (($config['domain'] ?? []) as $zone) {
			if ($this->listed($host . '.' . $zone)) {
				$hits[] = ['list' => $zone, 'target' => $host];
			}
		}

		$ip = @gethostbyname($host);
		if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$reversed = implode('.', array_reverse(explode('.', $ip)));
			foreach (($config['ip'] ?? []) as $zone) {
				if ($this->listed($reversed . '.' . $zone)) {
					$hits[] = ['list' => $zone, 'target' => $ip];
				}
			}
		}

		return ['listed' => ! empty($hits), 'hits' => $hits];
	}

	private function listed(string $query): bool
	{
		$records = @dns_get_record($query, DNS_A);
		if (empty($records)) {
			return false;
		}

		foreach ($records as $record) {
			$ip = $record['ip'] ?? '';
			if (str_starts_with($ip, '127.') && ! str_starts_with($ip, '127.255.255.')) {
				return true;
			}
		}

		return false;
	}
}
