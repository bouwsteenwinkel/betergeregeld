<?php

namespace App\Services\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Models\AccessGuard\Radar\Finding;
use App\Models\AccessGuard\Radar\Scan;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Scant een Asset op HTTP security-headers en maakt RadarFinding rows aan
 * voor ontbrekende/zwakke headers + verklikkers (X-Powered-By).
 *
 * Aanroep:
 *   $scanner = new SecurityHeadersScanner();
 *   $result  = $scanner->scan($asset);
 *   // ['scan_id' => 12, 'findings_count' => 3, 'pass' => 6, 'fail' => 3, 'warn' => 2]
 *
 * Wordt aangeroepen door:
 *   - Filament Action 'Scan headers' op RadarAsset (sync, modal-resultaat)
 *   - Toekomstige queued job voor scheduled daily scans
 *
 * Finding-strategie: per (asset, finding_key) is er max 1 actieve finding.
 *   - Header die mist en finding bestaat al → update last_detected_at
 *   - Header die mist en geen finding → maak nieuwe (status='new')
 *   - Header die nu wel goed is en finding 'new'/'confirmed' → status='resolved'
 */
final class SecurityHeadersScanner
{
	/**
	 * Headers die elk een eigen finding-rule hebben.
	 *
	 *   severity_missing → severity als header ontbreekt
	 *   severity_weak    → severity als header bestaat maar niet matcht
	 *   pattern          → regex die de waarde moet matchen
	 *   recommendation   → wat de admin moet doen
	 */
	private const REQUIRED_HEADERS = [
		'Strict-Transport-Security' => [
			'finding_key'      => 'missing_hsts',
			'severity_missing' => 'high',
			'severity_weak'    => 'medium',
			'pattern'          => '/max-age=\d{7,}/',
			'recommendation'   => 'Voeg toe in webserver-config: Strict-Transport-Security: max-age=31536000; includeSubDomains; preload',
		],
		'X-Frame-Options' => [
			'finding_key'      => 'missing_x_frame_options',
			'severity_missing' => 'medium',
			'severity_weak'    => 'low',
			'pattern'          => '/^(DENY|SAMEORIGIN)$/i',
			'recommendation'   => 'Stel X-Frame-Options: SAMEORIGIN in om clickjacking te voorkomen.',
		],
		'X-Content-Type-Options' => [
			'finding_key'      => 'missing_x_content_type_options',
			'severity_missing' => 'medium',
			'severity_weak'    => 'low',
			'pattern'          => '/^nosniff$/i',
			'recommendation'   => 'Stel X-Content-Type-Options: nosniff in om MIME-sniffing te blokkeren.',
		],
		'Referrer-Policy' => [
			'finding_key'      => 'missing_referrer_policy',
			'severity_missing' => 'medium',
			'severity_weak'    => 'low',
			'pattern'          => '/.+/',
			'recommendation'   => 'Stel Referrer-Policy: strict-origin-when-cross-origin in voor privacy + AVG.',
		],
		'Permissions-Policy' => [
			'finding_key'      => 'missing_permissions_policy',
			'severity_missing' => 'low',
			'severity_weak'    => 'low',
			'pattern'          => '/.+/',
			'recommendation'   => 'Stel Permissions-Policy in om ongebruikte browser-features uit te schakelen (camera, microphone, geolocation, etc.).',
		],
		'Cross-Origin-Opener-Policy' => [
			'finding_key'      => 'missing_coop',
			'severity_missing' => 'low',
			'severity_weak'    => 'low',
			'pattern'          => '/.+/',
			'recommendation'   => 'Stel Cross-Origin-Opener-Policy: same-origin-allow-popups in om browsing-context te isoleren.',
		],
	];

	/**
	 * Headers die juist NIET aanwezig moeten zijn (info-leak verklikkers).
	 */
	private const SHOULD_BE_ABSENT = [
		'X-Powered-By' => [
			'finding_key'  => 'exposed_x_powered_by',
			'severity'     => 'low',
			'recommendation' => 'Verwijder X-Powered-By header in webserver-config (lekt PHP-versie / framework).',
		],
		'X-AspNet-Version' => [
			'finding_key'  => 'exposed_aspnet_version',
			'severity'     => 'low',
			'recommendation' => 'Verwijder X-AspNet-Version header (lekt .NET versie).',
		],
		'Server' => [
			'finding_key'  => 'verbose_server_header',
			'severity'     => 'low',
			'pattern_bad'  => '/\d+\.\d+/',  // Server: Apache/2.4.41 → versie zichtbaar
			'recommendation' => 'Zet ServerTokens Prod in Apache / server_tokens off in nginx zodat versienummer niet meegestuurd wordt.',
		],
	];

	/**
	 * Scan een asset op security-headers. Slaat result op in Scan + Findings.
	 *
	 * @return array{scan_id:int,findings_count:int,pass:int,fail:int,warn:int,details:array}
	 */
	public function scan(Asset $asset): array
	{
		if (empty($asset->url)) {
			throw new \InvalidArgumentException("Asset {$asset->id} has no URL");
		}

		$scan = Scan::create([
			'tenant_id'  => $asset->tenant_id,
			'asset_id'   => $asset->id,
			'scan_type'  => 'http_probe',
			'status'     => 'running',
			'started_at' => Carbon::now(),
		]);

		try {
			// Snapshot active finding-keys vóór de scan — gebruikt door persistFindings()
			// om new_keys vs resolved_keys vs unchanged_keys te bepalen (diff-detect voor cron).
			$keysBefore = Finding::query()
				->where('asset_id', $asset->id)
				->where('check_type', 'security_headers')
				->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
				->pluck('finding_key')
				->all();

			$response = $this->fetchHeaders($asset->url);

			$result = $this->analyse($response['headers']);
			$result['url']             = $asset->url;
			$result['status_code']     = $response['status'];
			$result['final_url']       = $response['final_url'];
			$result['fetched_at']      = Carbon::now()->toIso8601String();

			[$persistedFindings, $diff] = $this->persistFindings($asset, $scan, $result, $keysBefore);
			$result['diff'] = $diff;

			$scan->update([
				'status'           => 'success',
				'finished_at'      => Carbon::now(),
				'detections_count' => count($result['details']),
				'findings_count'   => $persistedFindings,
				'raw_output'       => $result,
			]);

			$asset->update(['last_scanned_at' => Carbon::now()]);

			return [
				'scan_id'        => $scan->id,
				'findings_count' => $persistedFindings,
				'pass'           => $result['pass'],
				'fail'           => $result['fail'],
				'warn'           => $result['warn'],
				'details'        => $result['details'],
				'diff'           => $diff,
			];
		} catch (Throwable $e) {
			$scan->update([
				'status'        => 'error',
				'finished_at'   => Carbon::now(),
				'error_message' => $e->getMessage(),
			]);
			throw $e;
		}
	}

	/** Doe HTTP GET en lever response-headers + status terug. */
	private function fetchHeaders(string $url): array
	{
		// Windows PHP heeft geen CA-bundle; Composer's CaBundle bundelt 'm wel mee.
		$caBundle = class_exists(\Composer\CaBundle\CaBundle::class)
			? \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
			: true;

		$client = new Client([
			'timeout'         => 15,
			'allow_redirects' => ['track_redirects' => true, 'max' => 5],
			'http_errors'     => false,
			'verify'          => $caBundle,
			'headers'         => [
				'User-Agent' => 'Betergeregeld-Radar/1.0 (security-headers scan)',
			],
		]);

		$response = $client->get($url);
		$headers  = [];
		foreach ($response->getHeaders() as $name => $values) {
			// Eerste waarde volstaat — headers worden zelden meerdere keren gezet
			$headers[$name] = is_array($values) ? ($values[0] ?? '') : (string)$values;
		}
		// Final URL na redirects
		$redirects = $response->getHeader('X-Guzzle-Redirect-History');
		$finalUrl  = !empty($redirects) ? end($redirects) : $url;

		return [
			'status'    => $response->getStatusCode(),
			'headers'   => $headers,
			'final_url' => $finalUrl,
		];
	}

	/**
	 * Analyseer headers tegen onze regels. Geeft per check een verdict terug
	 * plus aggregaat (pass/fail/warn).
	 */
	private function analyse(array $headers): array
	{
		// Case-insensitive map
		$lcHeaders = [];
		foreach ($headers as $k => $v) {
			$lcHeaders[strtolower($k)] = $v;
		}

		$details = [];
		$pass = $fail = $warn = 0;

		foreach (self::REQUIRED_HEADERS as $name => $rule) {
			$val = $lcHeaders[strtolower($name)] ?? null;
			if ($val === null) {
				$details[] = [
					'check'          => $name,
					'verdict'        => 'fail',
					'severity'       => $rule['severity_missing'],
					'finding_key'    => $rule['finding_key'],
					'reason'         => 'Header ontbreekt',
					'recommendation' => $rule['recommendation'],
					'actual_value'   => null,
				];
				$fail++;
			} elseif (!preg_match($rule['pattern'], $val)) {
				$details[] = [
					'check'          => $name,
					'verdict'        => 'warn',
					'severity'       => $rule['severity_weak'],
					'finding_key'    => $rule['finding_key'] . '_weak',
					'reason'         => "Header aanwezig maar matcht niet '{$rule['pattern']}'",
					'recommendation' => $rule['recommendation'],
					'actual_value'   => $val,
				];
				$warn++;
			} else {
				$details[] = [
					'check'        => $name,
					'verdict'      => 'pass',
					'actual_value' => $val,
				];
				$pass++;
			}
		}

		foreach (self::SHOULD_BE_ABSENT as $name => $rule) {
			$val = $lcHeaders[strtolower($name)] ?? null;
			if ($val === null) {
				$details[] = [
					'check'   => $name,
					'verdict' => 'pass',
					'reason'  => 'Header niet aanwezig (correct)',
				];
				$pass++;
				continue;
			}
			// Server-header met versie-leak heeft pattern_bad
			if (!empty($rule['pattern_bad']) && !preg_match($rule['pattern_bad'], $val)) {
				$details[] = [
					'check'        => $name,
					'verdict'      => 'pass',
					'actual_value' => $val,
					'reason'       => 'Header aanwezig zonder versie-info',
				];
				$pass++;
				continue;
			}
			$details[] = [
				'check'          => $name,
				'verdict'        => 'fail',
				'severity'       => $rule['severity'],
				'finding_key'    => $rule['finding_key'],
				'reason'         => 'Verklikker-header aanwezig (lekt info)',
				'recommendation' => $rule['recommendation'],
				'actual_value'   => $val,
			];
			$fail++;
		}

		return [
			'pass'    => $pass,
			'fail'    => $fail,
			'warn'    => $warn,
			'details' => $details,
		];
	}

	/**
	 * Schrijf findings naar DB met dedup op (asset_id, finding_key, status≠resolved).
	 * - Bestaande finding voor zelfde key → update last_detected_at + risk_score
	 * - Nieuwe key → insert
	 * - Vorige fail/warn die nu pass is → status='resolved'
	 *
	 * @param  string[]  $keysBefore  Actieve finding-keys vóór deze scan (voor diff).
	 * @return array{0:int,1:array{new:string[],resolved:string[],unchanged:string[]}}
	 *         [aantal actieve findings, diff-snapshot]
	 */
	private function persistFindings(Asset $asset, Scan $scan, array $result, array $keysBefore): array
	{
		$now = Carbon::now();
		$activeKeysAfter = [];

		// Verzamel keys die nu fail/warn zijn
		$failingKeys = [];
		foreach ($result['details'] as $d) {
			if (in_array($d['verdict'], ['fail', 'warn'], true) && !empty($d['finding_key'])) {
				$failingKeys[$d['finding_key']] = $d;
			}
		}

		foreach ($failingKeys as $key => $d) {
			$severity   = $d['severity'] ?? 'low';
			$riskScore  = $this->severityToScore($severity);
			$title      = ($d['verdict'] === 'fail' ? 'Ontbrekende' : 'Zwakke') . ' header: ' . $d['check'];
			$detailText = ($d['reason'] ?? '')
				. ($d['actual_value'] ? "\n\nHuidige waarde: " . $d['actual_value'] : '')
				. "\n\nAanbeveling: " . ($d['recommendation'] ?? '-');

			$existing = Finding::query()
				->where('asset_id', $asset->id)
				->where('check_type', 'security_headers')
				->where('finding_key', $key)
				->whereNotIn('status', ['resolved', 'ignored', 'false_positive'])
				->first();

			if ($existing) {
				$existing->update([
					'last_detected_at' => $now,
					'risk_score'       => $riskScore,
					'severity'         => $severity,
					'title'            => $title,
					'detail'           => $detailText,
					'risk_factors'     => ['header' => $d['check'], 'verdict' => $d['verdict']],
				]);
			} else {
				Finding::create([
					'tenant_id'        => $asset->tenant_id,
					'asset_id'         => $asset->id,
					'check_type'       => 'security_headers',
					'finding_key'      => $key,
					'title'            => $title,
					'detail'           => $detailText,
					'severity'         => $severity,
					'risk_score'       => $riskScore,
					'risk_factors'     => ['header' => $d['check'], 'verdict' => $d['verdict']],
					'status'           => 'new',
					'first_detected_at' => $now,
					'last_detected_at'  => $now,
				]);
			}
			$activeKeysAfter[$key] = true;
		}

		// Auto-resolve oude findings die nu pass zijn
		Finding::query()
			->where('asset_id', $asset->id)
			->where('check_type', 'security_headers')
			->whereNotIn('status', ['resolved', 'ignored', 'false_positive', 'accepted_risk'])
			->whereNotIn('finding_key', array_keys($activeKeysAfter))
			->update([
				'status'      => 'resolved',
				'resolved_at' => $now,
				'resolution_notes' => 'Auto-resolved: header is nu correct gezet (scan #' . $scan->id . ')',
			]);

		$keysBeforeSet = array_fill_keys($keysBefore, true);
		$keysAfterSet  = $activeKeysAfter;
		$diff = [
			'new'       => array_values(array_diff(array_keys($keysAfterSet), array_keys($keysBeforeSet))),
			'resolved'  => array_values(array_diff(array_keys($keysBeforeSet), array_keys($keysAfterSet))),
			'unchanged' => array_values(array_intersect(array_keys($keysBeforeSet), array_keys($keysAfterSet))),
		];

		return [count($activeKeysAfter), $diff];
	}

	private function severityToScore(string $severity): int
	{
		return match ($severity) {
			'critical' => 90,
			'high'     => 70,
			'medium'   => 45,
			'low'      => 20,
			default    => 10,
		};
	}
}
