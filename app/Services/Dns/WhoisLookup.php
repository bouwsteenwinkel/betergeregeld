<?php

namespace App\Services\Dns;

/**
 * Eenvoudige WHOIS-lookup. Per TLD een ander whois-server-endpoint.
 * TCP-call op poort 43, vraag stellen + lees response. Parse de meest
 * voorkomende velden (registrant, registrar, registratie-datum, expiry,
 * nameservers). Per TLD varieert de output flink — we doen best-effort.
 */
class WhoisLookup
{
	private const SERVERS = [
		'nl'  => 'whois.domain-registry.nl',
		'com' => 'whois.verisign-grs.com',
		'net' => 'whois.verisign-grs.com',
		'org' => 'whois.publicinterestregistry.org',
		'io'  => 'whois.nic.io',
		'app' => 'whois.nic.google',
		'dev' => 'whois.nic.google',
		'de'  => 'whois.denic.de',
		'be'  => 'whois.dns.be',
		'fr'  => 'whois.nic.fr',
		'es'  => 'whois.nic.es',
		'eu'  => 'whois.eu',
		'uk'  => 'whois.nic.uk',
		'shop' => 'whois.nic.shop',
	];

	public function query(string $domain): array
	{
		$parts = explode('.', $domain);
		$tld = strtolower(end($parts));
		$server = self::SERVERS[$tld] ?? 'whois.iana.org';

		$raw = $this->rawWhois($server, $domain);
		if ($raw === null) {
			return ['domain' => $domain, 'success' => false, 'error' => "Kon geen WHOIS-server bereiken ({$server})."];
		}

		$parsed = $this->parse($raw, $tld);
		$parsed['domain']      = $domain;
		$parsed['tld']         = $tld;
		$parsed['whois_server']= $server;
		$parsed['success']     = true;
		$parsed['raw_excerpt'] = mb_substr($raw, 0, 4000);

		// Verdict
		$verdict = 'ok';
		$notes = [];
		if (!empty($parsed['expiry_at'])) {
			$days = (int) ceil((strtotime($parsed['expiry_at']) - time()) / 86400);
			$parsed['expiry_days'] = $days;
			if ($days < 0) {
				$verdict = 'err';
				$notes[] = 'Domein is ' . abs($days) . ' dagen geleden verlopen.';
			} elseif ($days <= 30) {
				$verdict = 'err';
				$notes[] = "Verloopt over {$days} dagen — verleng of zet auto-renew aan.";
			} elseif ($days <= 90) {
				$verdict = 'warn';
				$notes[] = "Verloopt over {$days} dagen.";
			}
		}
		$parsed['verdict'] = $verdict;
		$parsed['notes']   = $notes;

		return $parsed;
	}

	private function rawWhois(string $server, string $domain): ?string
	{
		$errno = 0; $errstr = '';
		$fp = @fsockopen($server, 43, $errno, $errstr, 8);
		if (!$fp) return null;
		stream_set_timeout($fp, 8);
		fwrite($fp, $domain . "\r\n");
		$out = '';
		while (!feof($fp)) {
			$out .= fread($fp, 4096);
		}
		fclose($fp);
		return $out !== '' ? $out : null;
	}

	private function parse(string $raw, string $tld): array
	{
		$result = [
			'registrar'    => null,
			'registrant'   => null,
			'created_at'   => null,
			'updated_at'   => null,
			'expiry_at'    => null,
			'nameservers'  => [],
			'status'       => [],
		];

		$lines = preg_split('/\r?\n/', $raw);
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line === '' || str_starts_with($line, '%') || str_starts_with($line, '#')) continue;

			// Generic patterns die meeste registries gebruiken
			if (preg_match('/^Registrar:\s*(.+)$/i', $line, $m))            $result['registrar']  ??= trim($m[1]);
			if (preg_match('/^Registrant(?: Name)?:\s*(.+)$/i', $line, $m)) $result['registrant'] ??= trim($m[1]);
			if (preg_match('/^Creation Date:\s*(.+)$/i', $line, $m))        $result['created_at'] ??= $this->normalizeDate($m[1]);
			if (preg_match('/^(?:Registered on|Registered):\s*(.+)$/i', $line, $m)) $result['created_at'] ??= $this->normalizeDate($m[1]);
			if (preg_match('/^Updated Date:\s*(.+)$/i', $line, $m))         $result['updated_at'] ??= $this->normalizeDate($m[1]);
			if (preg_match('/^(?:Registry Expiry Date|Expiry Date|Expires on|Expiration Time):\s*(.+)$/i', $line, $m)) {
				$result['expiry_at'] ??= $this->normalizeDate($m[1]);
			}
			if (preg_match('/^Name Server:\s*(\S+)/i', $line, $m))  $result['nameservers'][] = strtolower($m[1]);
			if (preg_match('/^Nameservers?:\s*(\S+)/i', $line, $m)) $result['nameservers'][] = strtolower($m[1]);
			if (preg_match('/^Domain Status:\s*(.+)$/i', $line, $m)) $result['status'][] = trim($m[1]);
			if (preg_match('/^Status:\s*(.+)$/i', $line, $m))        $result['status'][] = trim($m[1]);
		}

		// .nl-specific patterns
		if ($tld === 'nl') {
			foreach ($lines as $line) {
				if (preg_match('/^Registrar:\s*$/i', $line)) continue;
				if (preg_match('/^\s+Name:\s*(.+)$/i', $line, $m)) {
					$result['registrar'] ??= trim($m[1]);
				}
			}
		}

		$result['nameservers'] = array_values(array_unique($result['nameservers']));
		$result['status']      = array_values(array_unique($result['status']));

		return $result;
	}

	private function normalizeDate(string $input): ?string
	{
		$input = trim($input);
		$ts = strtotime($input);
		return $ts ? date('Y-m-d', $ts) : null;
	}
}
