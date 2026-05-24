<?php

namespace App\Services\Dns;

/**
 * SPF, DKIM en DMARC inspecteren voor een domein. Server-side via
 * dns_get_record (TXT-lookups). Geeft per record een verdict (ok /
 * warn / err / missing) met menselijke uitleg.
 *
 * DKIM-detectie is heuristisch — DKIM staat onder een selector
 * (selector._domainkey.domain.tld) en die selector kennen we niet
 * vooraf. We proberen de meest-gebruikte selectors. Detectie is
 * "best-effort"; geen DKIM gevonden ≠ DKIM is niet ingesteld.
 */
class MailAuthChecker
{
	public const COMMON_DKIM_SELECTORS = [
		'default',          // generieke default
		'google',           // Google Workspace
		'selector1',        // Microsoft 365 (primair)
		'selector2',        // Microsoft 365 (secundair)
		'k1',               // Mailchimp / etc.
		's1', 's2',         // SocketLabs / SendGrid
		'mxvault',          // MXroute
		'mandrill',         // Mandrill
		'mail',             // generieke 'mail'
		'mailo',            // soms
		'dkim',             // generieke 'dkim'
		'smtpapi',          // SendGrid
		'pm', 'pm1',        // Postmark
	];

	public function __construct(private readonly DomainInspector $inspector) {}

	/**
	 * Compleet rapport voor een domein.
	 *
	 * @return array{domain:string,spf:array,dmarc:array,dkim:array}
	 */
	public function check(string $domain): array
	{
		return [
			'domain' => $domain,
			'spf'    => $this->checkSpf($domain),
			'dmarc'  => $this->checkDmarc($domain),
			'dkim'   => $this->checkDkim($domain),
		];
	}

	// ---- SPF ----------------------------------------------------------

	public function checkSpf(string $domain): array
	{
		$txt = $this->inspector->dnsRecords($domain, DNS_TXT);
		$spf = null;
		foreach ($txt as $r) {
			$value = (string) ($r['txt'] ?? '');
			if (stripos($value, 'v=spf1') === 0) {
				$spf = $value;
				break;
			}
		}
		if ($spf === null) {
			return [
				'present' => false,
				'value'   => null,
				'verdict' => 'missing',
				'notes'   => ['Geen SPF-record gevonden. E-mail vanaf dit domein wordt door ontvangers minder vertrouwd.'],
			];
		}

		$notes = [];
		$verdict = 'ok';

		// Eind-mechanisme
		if (preg_match('/(?:^|\s)-all\b/', $spf)) {
			$notes[] = 'Eindigt met -all (hard fail) — strictste optie, aanbevolen.';
		} elseif (preg_match('/(?:^|\s)~all\b/', $spf)) {
			$notes[] = 'Eindigt met ~all (soft fail) — meeste ontvangers tolereren dit, maar -all is strikter.';
			$verdict = 'warn';
		} elseif (preg_match('/(?:^|\s)\?all\b/', $spf)) {
			$notes[] = '?all is neutraal — geeft géén bescherming. Wijzig naar -all of ~all.';
			$verdict = 'err';
		} elseif (preg_match('/(?:^|\s)\+all\b/', $spf)) {
			$notes[] = '+all betekent: iedereen mag namens dit domein mailen. Onveilig — wijzig naar -all.';
			$verdict = 'err';
		} else {
			$notes[] = 'Geen expliciet "all"-mechanisme — voeg -all of ~all toe als afsluiting.';
			$verdict = 'warn';
		}

		// DNS-lookup-budget (RFC 7208 — max 10 lookups)
		$lookups = $this->countSpfLookups($spf);
		if ($lookups > 10) {
			$notes[] = 'Dit SPF-record vereist ' . $lookups . ' DNS-lookups (max 10 volgens RFC 7208). Veel ontvangers behandelen dit als PermError.';
			$verdict = 'err';
		} elseif ($lookups >= 8) {
			$notes[] = $lookups . ' DNS-lookups — dichtbij de limiet van 10. Consolideer includes als je nog iets toevoegt.';
			if ($verdict === 'ok') $verdict = 'warn';
		}

		return [
			'present' => true,
			'value'   => $spf,
			'verdict' => $verdict,
			'lookups' => $lookups,
			'notes'   => $notes,
		];
	}

	/** Tel het aantal DNS-lookups dat dit SPF-record kost (include, a, mx, exists, redirect). */
	private function countSpfLookups(string $spf): int
	{
		preg_match_all('/\b(include:|a:|mx:|exists:|redirect=|a\b|mx\b)/i', $spf, $m);
		return count($m[0]);
	}

	// ---- DMARC --------------------------------------------------------

	public function checkDmarc(string $domain): array
	{
		$txt = $this->inspector->dnsRecords('_dmarc.' . $domain, DNS_TXT);
		$dmarc = null;
		foreach ($txt as $r) {
			$value = (string) ($r['txt'] ?? '');
			if (stripos($value, 'v=DMARC1') === 0) {
				$dmarc = $value;
				break;
			}
		}
		if ($dmarc === null) {
			return [
				'present' => false,
				'value'   => null,
				'verdict' => 'missing',
				'notes'   => ['Geen DMARC-record op _dmarc.' . $domain . ' — overweeg minimaal v=DMARC1; p=none; rua=mailto:dmarc@' . $domain . ' om rapportage te ontvangen.'],
			];
		}

		$tags = $this->parseDmarcTags($dmarc);
		$notes = [];
		$verdict = 'ok';

		$policy = strtolower($tags['p'] ?? 'none');
		if ($policy === 'reject') {
			$notes[] = 'Policy p=reject — hoogste bescherming. Goed gedaan.';
		} elseif ($policy === 'quarantine') {
			$notes[] = 'Policy p=quarantine — verdacht mail belandt in spam. Bij beproefde flow kun je doorzetten naar p=reject.';
			$verdict = 'warn';
		} else {
			$notes[] = 'Policy p=none — alleen monitoring, geen blokkering. Zet door naar quarantine of reject zodra je rua-rapporten weken stabiel zijn.';
			$verdict = 'warn';
		}

		if (empty($tags['rua'])) {
			$notes[] = 'Geen rua-tag (aggregate reports) — voeg rua=mailto:dmarc@' . $domain . ' toe om te zien wie er namens jou mailt.';
			if ($verdict === 'ok') $verdict = 'warn';
		}
		if (!empty($tags['pct']) && (int) $tags['pct'] < 100) {
			$notes[] = 'pct=' . $tags['pct'] . '% — beleid wordt maar op een deel van het verkeer toegepast.';
		}

		return [
			'present' => true,
			'value'   => $dmarc,
			'tags'    => $tags,
			'verdict' => $verdict,
			'notes'   => $notes,
		];
	}

	private function parseDmarcTags(string $dmarc): array
	{
		$tags = [];
		foreach (preg_split('/;\s*/', $dmarc) as $pair) {
			if (!str_contains($pair, '=')) continue;
			[$k, $v] = array_map('trim', explode('=', $pair, 2));
			$tags[strtolower($k)] = $v;
		}
		return $tags;
	}

	// ---- DKIM ---------------------------------------------------------

	public function checkDkim(string $domain): array
	{
		$found = [];
		foreach (self::COMMON_DKIM_SELECTORS as $selector) {
			$host = $selector . '._domainkey.' . $domain;
			$txt = $this->inspector->dnsRecords($host, DNS_TXT);
			foreach ($txt as $r) {
				$value = (string) ($r['txt'] ?? '');
				if (str_contains($value, 'v=DKIM1') || str_contains($value, 'k=rsa')) {
					$found[] = ['selector' => $selector, 'value' => $value];
					break;
				}
			}
		}

		if (empty($found)) {
			return [
				'present' => false,
				'value'   => null,
				'verdict' => 'missing',
				'selectors_tried' => self::COMMON_DKIM_SELECTORS,
				'notes'   => [
					'Geen DKIM-record gevonden op gangbare selectors (' . implode(', ', self::COMMON_DKIM_SELECTORS) . '). Mogelijk gebruikt jouw mail-provider een andere selector — niet noodzakelijk een echt probleem.',
				],
			];
		}

		return [
			'present' => true,
			'records' => $found,
			'verdict' => 'ok',
			'notes'   => ['DKIM-record(s) gevonden via selector(s): ' . implode(', ', array_column($found, 'selector')) . '.'],
		];
	}
}
