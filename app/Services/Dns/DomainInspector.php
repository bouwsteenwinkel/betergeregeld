<?php

namespace App\Services\Dns;

/**
 * Gedeelde helpers voor de DNS/security/cert-tools. Centraliseert
 * domain-validatie en DNS-resolutie zodat alle 5 tools (DNS-inspector,
 * SPF/DKIM/DMARC, SSL-checker, headers-analyzer, WHOIS) dezelfde
 * input-validatie en error-handling delen.
 */
class DomainInspector
{
	/**
	 * Normaliseer + valideer een door de user ingevoerd "domain". Accepteert
	 * https://example.com/pad → example.com, www.example.com → example.com.
	 * Returnt het schone domain of throws InvalidArgumentException.
	 */
	public function normalizeDomain(string $input): string
	{
		$d = trim($input);
		$d = preg_replace('#^https?://#i', '', $d);
		$d = explode('/', $d, 2)[0]; // strip path
		$d = explode(':', $d, 2)[0]; // strip port
		$d = strtolower(trim($d, '.'));
		$d = preg_replace('/^www\./', '', $d);

		if ($d === '' || strlen($d) > 253) {
			throw new \InvalidArgumentException('Domeinnaam is leeg of te lang.');
		}
		// Heel ruim: minstens één punt, alleen valid host-chars.
		if (!preg_match('/^[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?)+$/', $d)) {
			throw new \InvalidArgumentException('Dat ziet er niet uit als een geldige domeinnaam.');
		}
		return $d;
	}

	/**
	 * dns_get_record wrapper met try/catch en consistente shape. $type kan
	 * DNS_A, DNS_AAAA, DNS_MX, DNS_NS, DNS_TXT, DNS_CNAME, DNS_SOA, DNS_SRV
	 * (or-able met |).
	 *
	 * @return array list<array<string,mixed>>
	 */
	public function dnsRecords(string $domain, int $type): array
	{
		// dns_get_record kan op Windows en kapotte resolvers warnings emitten;
		// we onderdrukken die en geven een lege array terug bij failure.
		try {
			$records = @dns_get_record($domain, $type);
			return is_array($records) ? $records : [];
		} catch (\Throwable $e) {
			return [];
		}
	}

	/**
	 * Mensvriendelijke namen voor DNS_* constants. Voor headers in views.
	 */
	public static function recordTypeName(int $type): string
	{
		return match ($type) {
			DNS_A => 'A',
			DNS_AAAA => 'AAAA',
			DNS_MX => 'MX',
			DNS_NS => 'NS',
			DNS_TXT => 'TXT',
			DNS_CNAME => 'CNAME',
			DNS_SOA => 'SOA',
			DNS_SRV => 'SRV',
			default => '?',
		};
	}
}
