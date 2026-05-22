<?php

namespace App\Services\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\Concerns\HandlesRadarScans;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * TLS-certificate scanner.
 *
 * Opent ssl://host:443, vangt het peer-certificaat (verify_peer=false zodat
 * we ook self-signed/expired/mismatched certs binnenhalen om ze juist te
 * kunnen flaggen), parse'd het en kijkt naar:
 *
 *   - expiry (<14d critical, <30d high, <60d medium, expired critical)
 *   - self-signed (subject == issuer)
 *   - hostname/SAN mismatch
 *   - TLS-protocolversie (<1.2 = high)
 *
 * Cipher-strength / key-size / OCSP-stapling laten we voor later — eerst de
 * lekken die je daadwerkelijk op een dashboard wil zien.
 */
final class TlsScanner
{
	use HandlesRadarScans;

	protected function getCheckType(): string
	{
		return 'tls';
	}

	/**
	 * @return array{scan_id:int,findings_count:int,pass:int,fail:int,warn:int,details:array,diff:array}
	 */
	public function scan(Asset $asset): array
	{
		if (empty($asset->url)) {
			throw new \InvalidArgumentException("Asset {$asset->id} has no URL");
		}

		$scan = $this->openScan($asset);

		try {
			$keysBefore = $this->snapshotKeysBefore($asset);

			$host = parse_url($asset->url, PHP_URL_HOST);
			if (! $host) {
				throw new \RuntimeException("Could not parse host from {$asset->url}");
			}
			$port = parse_url($asset->url, PHP_URL_PORT) ?: 443;

			$probe   = $this->probeTls($host, $port);
			$result  = $this->analyse($host, $probe);
			$result['host']       = $host;
			$result['port']       = $port;
			$result['fetched_at'] = Carbon::now()->toIso8601String();

			[$count, $diff] = $this->persistFindingsWithDiff(
				$asset, $scan, $this->failingKeysFromDetails($result['details']), $keysBefore,
			);
			$result['diff'] = $diff;

			$this->closeScan($scan, $asset, $result, $count);

			return [
				'scan_id'        => $scan->id,
				'findings_count' => $count,
				'pass'           => $result['pass'],
				'fail'           => $result['fail'],
				'warn'           => $result['warn'],
				'details'        => $result['details'],
				'diff'           => $diff,
			];
		} catch (Throwable $e) {
			$this->failScan($scan, $e);
			throw $e;
		}
	}

	/**
	 * Connect met TLS naar host:port en lever cert + protocol/cipher info terug.
	 *
	 * @return array{cert:array,protocol:string,cipher:string}
	 */
	private function probeTls(string $host, int $port): array
	{
		$ctx = stream_context_create([
			'ssl' => [
				'capture_peer_cert' => true,
				'capture_peer_cert_chain' => true,
				'verify_peer'      => false,
				'verify_peer_name' => false,
				'SNI_enabled'      => true,
				'peer_name'        => $host,
			],
		]);

		$errno = 0;
		$errstr = '';
		$socket = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$ctx,
		);

		if (! $socket) {
			throw new \RuntimeException("TLS connect failed: {$errstr} (errno {$errno})");
		}

		$params = stream_context_get_params($socket);
		$meta   = stream_get_meta_data($socket);
		$cert   = $params['options']['ssl']['peer_certificate'] ?? null;
		$parsed = $cert ? openssl_x509_parse($cert) : null;

		fclose($socket);

		if (! $parsed) {
			throw new \RuntimeException('Could not parse peer certificate');
		}

		return [
			'cert'     => $parsed,
			'protocol' => $meta['crypto']['protocol'] ?? 'unknown',
			'cipher'   => $meta['crypto']['cipher_name'] ?? 'unknown',
		];
	}

	private function analyse(string $host, array $probe): array
	{
		$cert     = $probe['cert'];
		$protocol = $probe['protocol'];
		$details  = [];
		$pass = $fail = $warn = 0;

		// Expiry
		$validTo = $cert['validTo_time_t'] ?? 0;
		$daysLeft = (int) floor(($validTo - time()) / 86400);
		if ($daysLeft < 0) {
			$details[] = [
				'check'        => 'tls_expiry',
				'verdict'      => 'fail',
				'severity'     => 'critical',
				'finding_key'  => 'tls_cert_expired',
				'title'        => 'TLS-certificaat is verlopen',
				'reason'       => 'Certificaat is verlopen op ' . date('Y-m-d', $validTo),
				'detail'       => "Verlopen op " . date('Y-m-d', $validTo) . ' (' . abs($daysLeft) . " dagen geleden). Browsers tonen NET::ERR_CERT_DATE_INVALID. Verleng of vervang het certificaat onmiddellijk.",
				'risk_factors' => ['days_overdue' => abs($daysLeft)],
			];
			$fail++;
		} elseif ($daysLeft < 14) {
			$details[] = [
				'check'        => 'tls_expiry',
				'verdict'      => 'fail',
				'severity'     => 'critical',
				'finding_key'  => 'tls_cert_expiring_soon',
				'title'        => "TLS-certificaat verloopt over {$daysLeft} dagen",
				'reason'       => "Verloopt op " . date('Y-m-d', $validTo),
				'detail'       => "Slechts {$daysLeft} dagen tot expiry — verlengen vandaag aan te bevelen, idealiter auto-renew via Let's Encrypt of ACME.",
				'risk_factors' => ['days_left' => $daysLeft],
			];
			$fail++;
		} elseif ($daysLeft < 30) {
			$details[] = [
				'check'        => 'tls_expiry',
				'verdict'      => 'warn',
				'severity'     => 'high',
				'finding_key'  => 'tls_cert_expiring_soon',
				'title'        => "TLS-certificaat verloopt over {$daysLeft} dagen",
				'reason'       => "Verloopt op " . date('Y-m-d', $validTo),
				'detail'       => "Plan verlenging binnen 30 dagen om uitval te voorkomen.",
				'risk_factors' => ['days_left' => $daysLeft],
			];
			$warn++;
		} elseif ($daysLeft < 60) {
			$details[] = [
				'check'        => 'tls_expiry',
				'verdict'      => 'warn',
				'severity'     => 'medium',
				'finding_key'  => 'tls_cert_expiring_soon',
				'title'        => "TLS-certificaat verloopt over {$daysLeft} dagen",
				'reason'       => "Verloopt op " . date('Y-m-d', $validTo),
				'detail'       => "Nog ruim, maar zet auto-renew aan zodat het niet vergeten wordt.",
				'risk_factors' => ['days_left' => $daysLeft],
			];
			$warn++;
		} else {
			$details[] = [
				'check'        => 'tls_expiry',
				'verdict'      => 'pass',
				'actual_value' => "{$daysLeft} dagen geldig",
			];
			$pass++;
		}

		// Self-signed
		$subject = $cert['subject']['CN'] ?? '';
		$issuer  = $cert['issuer']['CN'] ?? '';
		if ($subject !== '' && $subject === $issuer) {
			$details[] = [
				'check'        => 'tls_self_signed',
				'verdict'      => 'fail',
				'severity'     => 'high',
				'finding_key'  => 'tls_self_signed',
				'title'        => 'Self-signed TLS-certificaat',
				'reason'       => "Subject CN ({$subject}) gelijk aan issuer CN",
				'detail'       => 'Browsers tonen een waarschuwing en zoekmachines downranken self-signed certs. Vervang door een publiek-vertrouwd cert (Let\'s Encrypt is gratis).',
				'risk_factors' => ['subject' => $subject, 'issuer' => $issuer],
			];
			$fail++;
		} else {
			$details[] = [
				'check'        => 'tls_self_signed',
				'verdict'      => 'pass',
				'actual_value' => "Uitgegeven door {$issuer}",
			];
			$pass++;
		}

		// Hostname / SAN match
		$names = $this->collectCertNames($cert);
		if (! $this->hostMatchesAny($host, $names)) {
			$details[] = [
				'check'        => 'tls_hostname',
				'verdict'      => 'fail',
				'severity'     => 'high',
				'finding_key'  => 'tls_hostname_mismatch',
				'title'        => 'TLS-certificaat matcht hostname niet',
				'reason'       => "Host {$host} komt niet voor in CN/SAN",
				'detail'       => 'Cert is uitgegeven voor: ' . implode(', ', $names) . '. Browsers blokkeren dit als NET::ERR_CERT_COMMON_NAME_INVALID.',
				'risk_factors' => ['host' => $host, 'cert_names' => $names],
			];
			$fail++;
		} else {
			$details[] = [
				'check'        => 'tls_hostname',
				'verdict'      => 'pass',
				'actual_value' => $subject ?: implode(', ', $names),
			];
			$pass++;
		}

		// Protocol version
		if (preg_match('/TLSv?1\.([0-3])/i', $protocol, $m)) {
			$minor = (int) $m[1];
			if ($minor < 2) {
				$details[] = [
					'check'        => 'tls_protocol',
					'verdict'      => 'fail',
					'severity'     => 'high',
					'finding_key'  => 'tls_weak_protocol',
					'title'        => "Zwakke TLS-versie in gebruik: {$protocol}",
					'reason'       => 'TLS < 1.2 wordt afgekeurd door browsers en PCI-DSS',
					'detail'       => 'Server onderhandelde over een verouderde TLS-versie. Schakel TLS 1.0/1.1 uit en sta alleen 1.2 + 1.3 toe in je webserver-config.',
					'risk_factors' => ['protocol' => $protocol],
				];
				$fail++;
			} else {
				$details[] = [
					'check'        => 'tls_protocol',
					'verdict'      => 'pass',
					'actual_value' => $protocol,
				];
				$pass++;
			}
		} else {
			$details[] = [
				'check'        => 'tls_protocol',
				'verdict'      => 'warn',
				'actual_value' => $protocol,
				'reason'       => 'Onbekende protocol-string — kon versie niet bepalen',
			];
			$warn++;
		}

		return ['pass' => $pass, 'fail' => $fail, 'warn' => $warn, 'details' => $details];
	}

	/**
	 * Vertaal details[] (verdict in / fail/warn) naar de failingKeys-map die
	 * persistFindingsWithDiff() wil — dedupe op finding_key.
	 */
	private function failingKeysFromDetails(array $details): array
	{
		$out = [];
		foreach ($details as $d) {
			if (! in_array($d['verdict'], ['fail', 'warn'], true)) continue;
			if (empty($d['finding_key'])) continue;
			$out[$d['finding_key']] = [
				'finding_key'  => $d['finding_key'],
				'title'        => $d['title'] ?? $d['check'],
				'detail'       => $d['detail'] ?? ($d['reason'] ?? ''),
				'severity'     => $d['severity'] ?? 'low',
				'risk_factors' => $d['risk_factors'] ?? [],
			];
		}
		return $out;
	}

	/** @return string[] CN + alle DNS:SAN waarden */
	private function collectCertNames(array $cert): array
	{
		$names = [];
		if (! empty($cert['subject']['CN'])) {
			$names[] = $cert['subject']['CN'];
		}
		$san = $cert['extensions']['subjectAltName'] ?? '';
		foreach (explode(',', $san) as $entry) {
			$entry = trim($entry);
			if (str_starts_with($entry, 'DNS:')) {
				$names[] = substr($entry, 4);
			}
		}
		return array_values(array_unique($names));
	}

	private function hostMatchesAny(string $host, array $names): bool
	{
		$host = strtolower($host);
		foreach ($names as $name) {
			$name = strtolower($name);
			if ($name === $host) return true;
			if (str_starts_with($name, '*.')) {
				$suffix = substr($name, 1); // ".example.com"
				if (str_ends_with($host, $suffix) && substr_count($host, '.') === substr_count($name, '.')) {
					return true;
				}
			}
		}
		return false;
	}
}
