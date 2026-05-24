<?php

namespace App\Services\Dns;

/**
 * SSL/TLS-certificaat van een hostname inspecteren. Maakt een echte
 * TLS-handshake via stream_socket_client en leest de peer-cert. Geen
 * externe API nodig.
 *
 * Returnt subject, issuer, valid-from, valid-to, dagen tot expiry,
 * SANs, fingerprint, signature-algo, public-key-bits.
 */
class SslCertChecker
{
	public function check(string $host, int $port = 443): array
	{
		$ctx = stream_context_create([
			'ssl' => [
				'capture_peer_cert'       => true,
				'capture_peer_cert_chain' => true,
				'verify_peer'             => false,    // we willen ook expired/invalid certs zien
				'verify_peer_name'        => false,
				'SNI_enabled'             => true,
				'peer_name'               => $host,
			],
		]);

		$errno = 0; $errstr = '';
		$client = @stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno, $errstr,
			10, // timeout
			STREAM_CLIENT_CONNECT,
			$ctx
		);

		if ($client === false) {
			return [
				'host'    => $host,
				'port'    => $port,
				'error'   => "Kon geen TLS-verbinding maken: {$errstr}" . ($errno ? " (#{$errno})" : ''),
				'success' => false,
			];
		}

		$params = stream_context_get_params($client);
		fclose($client);

		$cert = $params['options']['ssl']['peer_certificate'] ?? null;
		$chain = $params['options']['ssl']['peer_certificate_chain'] ?? [];

		if (!$cert) {
			return ['host' => $host, 'port' => $port, 'error' => 'Geen certificaat ontvangen', 'success' => false];
		}

		$parsed = openssl_x509_parse($cert);
		if (!$parsed) {
			return ['host' => $host, 'port' => $port, 'error' => 'Kon certificaat niet parsen', 'success' => false];
		}

		$daysLeft = isset($parsed['validTo_time_t'])
			? (int) ceil(((int) $parsed['validTo_time_t'] - time()) / 86400)
			: null;

		// SANs uit extensions
		$sans = [];
		$extSan = $parsed['extensions']['subjectAltName'] ?? '';
		if ($extSan) {
			foreach (preg_split('/\s*,\s*/', $extSan) as $entry) {
				if (str_starts_with($entry, 'DNS:')) {
					$sans[] = substr($entry, 4);
				}
			}
		}

		$pkey = openssl_pkey_get_public($cert);
		$pkeyDetails = $pkey ? openssl_pkey_get_details($pkey) : null;

		// Verdict
		$verdict = 'ok';
		$notes = [];
		if ($daysLeft !== null) {
			if ($daysLeft < 0) {
				$verdict = 'err';
				$notes[] = "Certificaat is " . abs($daysLeft) . " dagen geleden verlopen.";
			} elseif ($daysLeft <= 14) {
				$verdict = 'err';
				$notes[] = "Verloopt over {$daysLeft} dag(en) — vernieuwen of auto-renew controleren.";
			} elseif ($daysLeft <= 30) {
				$verdict = 'warn';
				$notes[] = "Verloopt over {$daysLeft} dag(en) — binnenkort vernieuwen.";
			}
		}

		// Check signature algorithm
		$sigAlg = $parsed['signatureTypeSN'] ?? ($parsed['signatureTypeLN'] ?? null);
		if ($sigAlg && stripos($sigAlg, 'sha1') !== false) {
			$verdict = 'err';
			$notes[] = "Gebruikt verouderd SHA-1 — moderne browsers vertrouwen deze niet.";
		}

		// Key-size
		if ($pkeyDetails && ($pkeyDetails['bits'] ?? 0) < 2048 && ($pkeyDetails['type'] ?? -1) === OPENSSL_KEYTYPE_RSA) {
			$verdict = 'err';
			$notes[] = "RSA-key is " . $pkeyDetails['bits'] . " bits — minimaal 2048 vereist.";
		}

		return [
			'host'        => $host,
			'port'        => $port,
			'success'     => true,
			'subject_cn'  => $parsed['subject']['CN'] ?? null,
			'issuer_cn'   => $parsed['issuer']['CN'] ?? null,
			'issuer_o'    => $parsed['issuer']['O']  ?? null,
			'valid_from'  => isset($parsed['validFrom_time_t']) ? date('Y-m-d H:i:s', $parsed['validFrom_time_t']) : null,
			'valid_to'    => isset($parsed['validTo_time_t'])   ? date('Y-m-d H:i:s', $parsed['validTo_time_t'])   : null,
			'days_left'   => $daysLeft,
			'sans'        => $sans,
			'serial'      => $parsed['serialNumber'] ?? null,
			'signature'   => $sigAlg,
			'key_type'    => $pkeyDetails ? ['rsa', 'dsa', 'dh', 'ec'][$pkeyDetails['type']] ?? '?' : null,
			'key_bits'    => $pkeyDetails['bits'] ?? null,
			'chain_count' => count($chain),
			'verdict'     => $verdict,
			'notes'       => $notes,
		];
	}
}
