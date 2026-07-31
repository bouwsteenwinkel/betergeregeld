<?php

namespace App\Services\Monitor;

use App\Models\Monitor\Check;
use App\Models\Monitor\CheckResult;
use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Voert één check uit (HTTP of TCP), schrijft een CheckResult weg en werkt de
 * last_*-velden van de check bij. Gedeeld door het cron-command en de
 * "Check nu"-actie in de UI. Outbound HTTPS gebruikt de Composer CA-bundle
 * (Windows-PHP heeft geen eigen CA-store).
 */
class CheckRunner
{
	/**
	 * Waarmee de checker zich bekendmaakt. Een herkenbare naam plus een URL waar een
	 * beheerder kan opzoeken wie er klopt: dat is wat je van een nette bot verwacht, en
	 * het scheelt de klant een raadsel in zijn logboek.
	 */
	public const USER_AGENT = 'BeterGeregeld-Monitor/1.0 (+https://betergeregeld.com/monitoring)';

	public function run(Check $check): CheckResult
	{
		$start = microtime(true);
		$status = 'down';
		$code = null;
		$error = null;
		$timeout = $check->timeout_seconds ?: 10;

		try {
			if ($check->type === 'tcp') {
				[$host, $port] = $this->parseTarget($check->target);
				$conn = @fsockopen($host, $port, $errno, $errstr, $timeout);

				if ($conn) {
					$status = 'up';
					fclose($conn);
				} else {
					$error = trim("{$errno} {$errstr}") ?: 'verbinding mislukt';
				}
			} else {
				// Eigen User-Agent, want zonder stuurt Guzzle "GuzzleHttp/7" en dat is voor een
				// WAF een botsignaal. Gemeten 31-07-2026: bouwsteenwinkel.nl (achter Cloudflare)
				// gaf 6.411 keer op rij HTTP 403 sinds 9 juli — de site was al die tijd gewoon in
				// de lucht, de checker werd geblokkeerd. Met deze UA komt dezelfde URL op 200.
				// De check stond dus drie weken op "down" te roepen, en dat is erger dan geen
				// check: niemand kijkt er meer naar als hij altijd rood staat.
				$resp = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
					->withHeaders(['User-Agent' => self::USER_AGENT])
					->timeout($timeout)
					->get($check->target);

				$code = $resp->status();
				$ok = $check->expected_code
					? ($code === $check->expected_code)
					: ($code >= 200 && $code < 400);

				if ($ok) {
					$status = 'up';
				} else {
					$error = "HTTP {$code}";
				}
			}
		} catch (Throwable $e) {
			$status = 'down';
			$error = mb_substr($e->getMessage(), 0, 500);
		}

		$latency = (int) round((microtime(true) - $start) * 1000);
		$now = now();

		$result = CheckResult::create([
			'check_id' => $check->id,
			'checked_at' => $now,
			'status' => $status,
			'latency_ms' => $latency,
			'http_code' => $code,
			'error' => $error,
		]);

		$check->forceFill([
			'last_status' => $status,
			'last_code' => $code,
			'last_latency_ms' => $latency,
			'last_checked_at' => $now,
			'last_error' => $error,
		])->save();

		return $result;
	}

	/**
	 * @return array{0:string,1:int}
	 */
	private function parseTarget(string $target): array
	{
		$target = preg_replace('#^\w+://#', '', trim($target));
		$parts = explode(':', $target, 2);

		return [$parts[0], isset($parts[1]) ? (int) $parts[1] : 80];
	}
}
