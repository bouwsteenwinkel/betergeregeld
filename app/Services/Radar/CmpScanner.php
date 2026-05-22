<?php

namespace App\Services\Radar;

use App\Models\AccessGuard\Radar\Asset;
use App\Services\Radar\Concerns\HandlesRadarScans;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Cookie Management Platform / consent-scanner.
 *
 * Doet één HTTP GET op de asset-URL en zoekt in de HTML-body naar:
 *
 *   1. Bekende CMP-vendors (Cookiebot, OneTrust, Usercentrics, Iubenda,
 *      Complianz, Termly, Borlabs, Klaro!). Geen CMP-handtekening op een
 *      site met tracking → fail.
 *   2. Bekende tracker-scripts (GA4, GTM, Facebook Pixel, LinkedIn Insight,
 *      Hotjar, Microsoft Clarity). Aanwezigheid van trackers ZONDER CMP =
 *      AVG-risico.
 *
 * Server-side HTML parsen is een ondergrens-check: een CMP die alléén via
 * een JS-bundle inhakt zien we hier niet. Voor true consent-tracking is
 * later een headless browser nodig — out of scope voor #56.
 */
final class CmpScanner
{
	use HandlesRadarScans;

	/**
	 * Vendor → fingerprint patronen (case-insensitive). Eerste match wint.
	 */
	private const CMP_FINGERPRINTS = [
		'Cookiebot'    => ['cookiebot.com/uc.js', 'CookieConsent.run', 'data-cbid'],
		'OneTrust'     => ['cdn.cookielaw.org', 'OneTrust', 'onetrust-consent-sdk'],
		'Usercentrics' => ['usercentrics.eu', 'UC_UI', 'data-settings-id'],
		'Iubenda'      => ['iubenda.com', '_iub.csConfiguration'],
		'Complianz'    => ['complianz-gdpr', 'cmplz_consent_status'],
		'Termly'       => ['app.termly.io', 'termly.io/resource-blocker'],
		'Borlabs'      => ['borlabs-cookie'],
		'Klaro!'       => ['klaro.kiprotect.com', 'klaroConfig'],
		'Cookie Yes'   => ['cookie-law-info', 'cli_choice'],
		'TCF v2'       => ['__tcfapi'], // generieke IAB TCF
	];

	private const TRACKER_FINGERPRINTS = [
		'Google Analytics' => ['googletagmanager.com/gtag/js', 'google-analytics.com/analytics.js', "gtag('config'"],
		'Google Tag Manager' => ['googletagmanager.com/gtm.js', "googletagmanager.com/ns.html"],
		'Facebook Pixel'   => ['connect.facebook.net/en_US/fbevents.js', "fbq('init'"],
		'LinkedIn Insight' => ['snap.licdn.com/li.lms-analytics/insight.min.js', '_linkedin_partner_id'],
		'Hotjar'           => ['static.hotjar.com', 'hjid:'],
		'Microsoft Clarity' => ['clarity.ms/tag/', '(c,l,a,r,i,t,y)'],
		'TikTok Pixel'     => ['analytics.tiktok.com'],
	];

	protected function getCheckType(): string
	{
		return 'cmp';
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
			$body     = $response['body'];

			$cmps     = $this->detect($body, self::CMP_FINGERPRINTS);
			$trackers = $this->detect($body, self::TRACKER_FINGERPRINTS);

			$result = $this->analyse($cmps, $trackers);
			$result['url']         = $asset->url;
			$result['status_code'] = $response['status'];
			$result['cmps']        = $cmps;
			$result['trackers']    = $trackers;
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
				'cmps'           => $cmps,
				'trackers'       => $trackers,
			];
		} catch (Throwable $e) {
			$this->failScan($scan, $e);
			throw $e;
		}
	}

	/**
	 * @return array{status:int,body:string}
	 */
	private function fetch(string $url): array
	{
		$caBundle = class_exists(\Composer\CaBundle\CaBundle::class)
			? \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath()
			: true;

		$client = new Client([
			'timeout'         => 20,
			'allow_redirects' => true,
			'http_errors'     => false,
			'verify'          => $caBundle,
			'headers'         => [
				'User-Agent' => 'Betergeregeld-Radar/1.0 (CMP scan)',
				// Sommige sites tonen alleen consent-banner op een echte browser UA;
				// we vragen 'm expliciet op een desktop-Chrome string zodat we de
				// realistische HTML zien.
				'Accept'     => 'text/html,application/xhtml+xml',
			],
		]);

		$response = $client->get($url);
		// Body kan groot zijn — kap af op 1MB. CMP-fingerprints staan typisch in <head>
		// of vroeg in <body>, dus dat is ruim genoeg.
		$body = (string) $response->getBody();
		if (strlen($body) > 1_000_000) {
			$body = substr($body, 0, 1_000_000);
		}

		return ['status' => $response->getStatusCode(), 'body' => $body];
	}

	/**
	 * @param  array<string, string[]>  $fingerprints  vendor => [patterns]
	 * @return string[] gevonden vendor-namen
	 */
	private function detect(string $body, array $fingerprints): array
	{
		$haystack = strtolower($body);
		$hits = [];
		foreach ($fingerprints as $vendor => $patterns) {
			foreach ($patterns as $pattern) {
				if (str_contains($haystack, strtolower($pattern))) {
					$hits[] = $vendor;
					break;
				}
			}
		}
		return array_values(array_unique($hits));
	}

	private function analyse(array $cmps, array $trackers): array
	{
		$pass = $fail = $warn = 0;
		$details = [];

		if (! empty($cmps)) {
			$details[] = [
				'check'        => 'cmp_present',
				'verdict'      => 'pass',
				'actual_value' => implode(', ', $cmps),
				'reason'       => 'CMP gedetecteerd',
			];
			$pass++;
		} elseif (! empty($trackers)) {
			// Trackers zonder CMP = AVG-risico (geen consent-mechanisme)
			$details[] = [
				'check'        => 'cmp_present',
				'verdict'      => 'fail',
				'severity'     => 'high',
				'finding_key'  => 'cmp_missing_with_trackers',
				'title'        => 'Geen CMP gedetecteerd terwijl er trackers actief zijn',
				'detail'       => "Trackers in de pagina: " . implode(', ', $trackers) . "\n\nZonder consent-management worden deze trackers gezet vóór de gebruiker akkoord geeft → AVG/UAVG-overtreding. Integreer een CMP (Cookiebot, Complianz, OneTrust, etc.) en blokkeer trackers tot na consent.",
				'risk_factors' => ['trackers' => $trackers],
			];
			$fail++;
		} else {
			$details[] = [
				'check'        => 'cmp_present',
				'verdict'      => 'pass',
				'reason'       => 'Geen CMP nodig — er zijn geen trackers gedetecteerd',
			];
			$pass++;
		}

		if (! empty($trackers)) {
			$details[] = [
				'check'        => 'trackers_seen',
				'verdict'      => empty($cmps) ? 'fail' : 'pass',
				'actual_value' => implode(', ', $trackers),
				'reason'       => empty($cmps)
					? 'Tracker-scripts gevonden zonder CMP'
					: 'Tracker-scripts staan in de pagina (CMP zou consent moeten regelen)',
			];
			empty($cmps) ? $fail++ : $pass++;
		}

		return ['pass' => $pass, 'fail' => $fail, 'warn' => $warn, 'details' => $details];
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
}
