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

	/**
	 * Het doorlaatbewijs voor een WAF, maar alléén naar hosts die we zelf beheren.
	 *
	 * De monitor controleert ook sites van klanten. Zou hij het geheim overal meesturen, dan
	 * geeft hij zijn sleutel weg aan elke partij die hij aanroept — en daarmee aan iedereen die
	 * hun logs kan lezen. Vandaar de allowlist uit config/monitor.php: geen host in de lijst,
	 * geen header. Zonder ingesteld geheim stuurt hij sowieso niets extra's.
	 *
	 * De vergelijking gaat op de hostnaam, niet op de hele URL: een pad kan verschillen en een
	 * poort hoort er niet toe te doen. Subdomeinen tellen mee, zodat één entry
	 * (bouwsteenwinkel.nl) ook www. en shop. dekt zonder dat je ze los moet opsommen.
	 *
	 * @return array<string,string> leeg als er niets meegestuurd mag worden
	 */
	public function bypassHeaderFor(string $url): array
	{
		$secret = (string) config('monitor.bypass.secret', '');
		$header = (string) config('monitor.bypass.header', 'X-BG-Monitor');
		$hosts  = (array) config('monitor.bypass.hosts', []);

		if ($secret === '' || $header === '' || $hosts === []) {
			return [];
		}

		$host = strtolower((string) parse_url($url, PHP_URL_HOST));
		if ($host === '') {
			return [];
		}

		foreach ($hosts as $toegestaan) {
			$toegestaan = strtolower(trim((string) $toegestaan));
			if ($toegestaan !== '' && ($host === $toegestaan || str_ends_with($host, '.' . $toegestaan))) {
				return [$header => $secret];
			}
		}

		return [];
	}

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
				// Eigen User-Agent: een checker hoort zich bekend te maken, zodat een beheerder
				// in zijn logboek kan zien wie er klopt. Zonder stuurt Guzzle "GuzzleHttp/7".
				//
				// LET OP — dit lost een WAF-blokkade NIET op. Gemeten 31-07-2026 op
				// bouwsteenwinkel.nl (achter Cloudflare), dat sinds 9 juli 6.411 keer op rij
				// HTTP 403 gaf terwijl de site gewoon in de lucht was: vanuit deze PHP-client
				// geeft / een 403 met élke User-Agent, ook een volledige browser-UA, en ook met
				// Accept-headers of een afgedwongen HTTP/1.1 of HTTP/2. Dezelfde UA vanaf curl
				// op dezelfde machine geeft 200. Cloudflare kijkt dus naar de TLS-vingerafdruk
				// van de client, niet naar wat wij meesturen.
				//
				// Uitwijken naar /robots.txt of /sitemap.xml werkt wél (200), maar die worden
				// door Cloudflare zélf uit de edge-cache geserveerd (cf-cache-status: HIT) —
				// dan staat de check groen terwijl de server plat ligt. Dat is erger dan het
				// valse rood. De echte oplossing is een skip-regel in Cloudflare voor deze
				// monitor; tot die er is blijft die ene check onterecht rood.
				$resp = Http::withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
					->withHeaders(['User-Agent' => self::USER_AGENT] + $this->bypassHeaderFor($check->target))
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
