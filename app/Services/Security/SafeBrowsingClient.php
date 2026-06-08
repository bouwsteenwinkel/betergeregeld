<?php

namespace App\Services\Security;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Google Safe Browsing Lookup API (v4 threatMatches:find). Zonder API-key
 * wordt de check overgeslagen (status 'unknown').
 */
class SafeBrowsingClient
{
	public const API = 'https://safebrowsing.googleapis.com/v4/threatMatches:find';

	public ?string $lastError = null;

	/**
	 * @return array{status:string, threats:array<int,string>}  status = ok|flagged|unknown
	 */
	public function check(string $url): array
	{
		$key = config('security.safe_browsing_key');
		if (! $key) {
			return ['status' => 'unknown', 'threats' => []];
		}

		$payload = [
			'client'     => ['clientId' => 'beter-geregeld-monitor', 'clientVersion' => '1.0'],
			'threatInfo' => [
				'threatTypes'      => ['MALWARE', 'SOCIAL_ENGINEERING', 'UNWANTED_SOFTWARE', 'POTENTIALLY_HARMFUL_APPLICATION'],
				'platformTypes'    => ['ANY_PLATFORM'],
				'threatEntryTypes' => ['URL'],
				'threatEntries'    => [['url' => $url]],
			],
		];

		try {
			$resp = Http::timeout(20)->acceptJson()
				->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
				->post(self::API . '?key=' . urlencode((string) $key), $payload);

			if (! $resp->ok()) {
				$this->lastError = 'Safe Browsing HTTP ' . $resp->status();

				return ['status' => 'unknown', 'threats' => []];
			}

			$matches = $resp->json('matches') ?? [];
			if (empty($matches)) {
				return ['status' => 'ok', 'threats' => []];
			}

			$threats = array_values(array_unique(array_map(
				fn ($m) => (string) ($m['threatType'] ?? 'THREAT'),
				$matches,
			)));

			return ['status' => 'flagged', 'threats' => $threats];
		} catch (\Throwable $e) {
			$this->lastError = $e->getMessage();

			return ['status' => 'unknown', 'threats' => []];
		}
	}
}
