<?php

namespace App\Services\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\Concerns\HandlesRadarScans;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Cookies-scanner. Doet één HTTP GET op de asset-URL en kijkt naar ALLE
 * Set-Cookie response-headers. Per cookie:
 *
 *   - Secure flag ontbreekt op HTTPS-site → fail
 *   - HttpOnly ontbreekt → fail (medium, session-cookies meestal kritiek)
 *   - SameSite ontbreekt → warn
 *   - SameSite=None zonder Secure → fail (browser dropt 'm)
 *
 * Findings aggregeren: één finding-key per probleem-type, met details
 * over WELKE cookies het betreft (in risk_factors). Zo houden we de
 * findings-tabel compact zonder dat informatie weglekt.
 */
final class CookiesScanner
{
	use HandlesRadarScans;

	protected function getCheckType(): string
	{
		return 'cookies';
	}

	public function scan(Asset $asset): array
	{
		if (empty($asset->url)) {
			throw new \InvalidArgumentException("Asset {$asset->id} has no URL");
		}

		$scan = $this->openScan($asset);

		try {
			$keysBefore = $this->snapshotKeysBefore($asset);

			$response = $this->fetch($asset->url);
			$isHttps  = str_starts_with(strtolower($asset->url), 'https://');

			$result = $this->analyse($response['set_cookies'], $isHttps);
			$result['url']         = $asset->url;
			$result['status_code'] = $response['status'];
			$result['fetched_at']  = Carbon::now()->toIso8601String();

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
	 * @return array{status:int,set_cookies:string[]}
	 */
	private function fetch(string $url): array
	{
		$caBundle = class_exists(\Composer\CaBundle\CaBundle::class)
			? \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
			: true;

		$client = new Client([
			'timeout'         => 15,
			'allow_redirects' => true,
			'http_errors'     => false,
			'verify'          => $caBundle,
			'headers'         => [
				'User-Agent' => 'Betergeregeld-Radar/1.0 (cookies scan)',
			],
		]);

		$response = $client->get($url);
		return [
			'status'      => $response->getStatusCode(),
			'set_cookies' => $response->getHeader('Set-Cookie'),
		];
	}

	/**
	 * @param  string[]  $setCookies  Ruwe Set-Cookie waarden
	 */
	private function analyse(array $setCookies, bool $isHttps): array
	{
		$pass = $fail = $warn = 0;

		if (empty($setCookies)) {
			return [
				'pass'    => 1,
				'fail'    => 0,
				'warn'    => 0,
				'details' => [[
					'check'   => 'cookies_set',
					'verdict' => 'pass',
					'reason'  => 'Geen Set-Cookie headers in response',
				]],
			];
		}

		$cookies = array_map([$this, 'parseCookie'], $setCookies);

		$missingSecure   = [];
		$missingHttpOnly = [];
		$missingSameSite = [];
		$sameSiteNoneNoSecure = [];

		foreach ($cookies as $c) {
			if ($isHttps && ! $c['secure']) {
				$missingSecure[] = $c['name'];
			}
			if (! $c['httponly']) {
				$missingHttpOnly[] = $c['name'];
			}
			if ($c['samesite'] === null) {
				$missingSameSite[] = $c['name'];
			}
			if (strtolower((string) $c['samesite']) === 'none' && ! $c['secure']) {
				$sameSiteNoneNoSecure[] = $c['name'];
			}
		}

		$details = [];

		if ($missingSecure) {
			$details[] = $this->mkFail(
				'cookies_secure', 'cookies_missing_secure',
				'high',
				'Cookies zonder Secure-flag op HTTPS-site',
				"Cookies: " . implode(', ', $missingSecure) . "\n\nVoeg Secure toe in Set-Cookie zodat ze niet over HTTP gelekt worden.",
				['cookies' => $missingSecure],
			);
			$fail++;
		} else {
			$details[] = $this->mkPass('cookies_secure', 'Alle cookies hebben Secure-flag');
			$pass++;
		}

		if ($missingHttpOnly) {
			$details[] = $this->mkFail(
				'cookies_httponly', 'cookies_missing_httponly',
				'medium',
				'Cookies zonder HttpOnly-flag',
				"Cookies: " . implode(', ', $missingHttpOnly) . "\n\nHttpOnly voorkomt dat client-side JavaScript bij de cookie kan — essentieel voor session-cookies om XSS-impact te beperken.",
				['cookies' => $missingHttpOnly],
			);
			$fail++;
		} else {
			$details[] = $this->mkPass('cookies_httponly', 'Alle cookies hebben HttpOnly');
			$pass++;
		}

		if ($sameSiteNoneNoSecure) {
			$details[] = $this->mkFail(
				'cookies_samesite_none_no_secure', 'cookies_samesite_none_no_secure',
				'high',
				'SameSite=None zonder Secure-flag',
				"Cookies: " . implode(', ', $sameSiteNoneNoSecure) . "\n\nChromium-browsers gooien deze cookies weg — kies SameSite=Lax of voeg Secure toe.",
				['cookies' => $sameSiteNoneNoSecure],
			);
			$fail++;
		}

		if ($missingSameSite) {
			$details[] = $this->mkWarn(
				'cookies_samesite', 'cookies_missing_samesite',
				'low',
				'Cookies zonder expliciete SameSite-attribute',
				"Cookies: " . implode(', ', $missingSameSite) . "\n\nModerne browsers nemen Lax als default, maar expliciet zetten voorkomt verrassingen bij oudere clients en third-party context.",
				['cookies' => $missingSameSite],
			);
			$warn++;
		} else {
			$details[] = $this->mkPass('cookies_samesite', 'Alle cookies hebben SameSite-attribute');
			$pass++;
		}

		return ['pass' => $pass, 'fail' => $fail, 'warn' => $warn, 'details' => $details];
	}

	/**
	 * Parse één Set-Cookie waarde naar attributes.
	 *
	 * @return array{name:string,value:string,secure:bool,httponly:bool,samesite:?string}
	 */
	private function parseCookie(string $raw): array
	{
		$parts = array_map('trim', explode(';', $raw));
		$nameValue = array_shift($parts);
		[$name, $value] = array_pad(explode('=', $nameValue, 2), 2, '');

		$out = [
			'name'     => $name,
			'value'    => $value,
			'secure'   => false,
			'httponly' => false,
			'samesite' => null,
		];

		foreach ($parts as $attr) {
			$lc = strtolower($attr);
			if ($lc === 'secure') $out['secure'] = true;
			elseif ($lc === 'httponly') $out['httponly'] = true;
			elseif (str_starts_with($lc, 'samesite=')) $out['samesite'] = substr($attr, 9);
		}
		return $out;
	}

	private function failingKeysFromDetails(array $details): array
	{
		$out = [];
		foreach ($details as $d) {
			if (! in_array($d['verdict'], ['fail', 'warn'], true)) continue;
			if (empty($d['finding_key'])) continue;
			$out[$d['finding_key']] = [
				'finding_key'  => $d['finding_key'],
				'title'        => $d['title'] ?? $d['check'],
				'detail'       => $d['detail'] ?? '',
				'severity'     => $d['severity'] ?? 'low',
				'risk_factors' => $d['risk_factors'] ?? [],
			];
		}
		return $out;
	}

	private function mkFail(string $check, string $key, string $sev, string $title, string $detail, array $risk): array
	{
		return ['check' => $check, 'verdict' => 'fail', 'finding_key' => $key, 'severity' => $sev, 'title' => $title, 'detail' => $detail, 'risk_factors' => $risk];
	}

	private function mkWarn(string $check, string $key, string $sev, string $title, string $detail, array $risk): array
	{
		return ['check' => $check, 'verdict' => 'warn', 'finding_key' => $key, 'severity' => $sev, 'title' => $title, 'detail' => $detail, 'risk_factors' => $risk];
	}

	private function mkPass(string $check, string $reason): array
	{
		return ['check' => $check, 'verdict' => 'pass', 'reason' => $reason];
	}
}
