<?php

namespace App\Services\Seo;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper rond GSC searchAnalytics/query. Auth via GoogleApiAuth.
 *
 * Voor de daily-import is alleen searchAnalytics/query nodig (read-only
 * scope volstaat). Sitemap-PUT/DELETE en URL-inspection vereisen RW-scope
 * en zijn voor latere iteraties.
 */
class GoogleSearchConsoleClient
{
	public const API_BASE = 'https://www.googleapis.com/webmasters/v3';
	public const ROW_LIMIT = 5000;

	public function __construct(private readonly GoogleApiAuth $auth) {}

	/**
	 * Raw searchAnalytics/query call.
	 *
	 * @param string $siteUrl 'sc-domain:example.com' of 'https://example.com/'
	 * @param array  $body    POST-body conform GSC API
	 * @return array{status:int, json:array|null}
	 */
	public function searchAnalyticsQuery(string $siteUrl, array $body): array
	{
		$token = $this->auth->accessToken();
		$url = self::API_BASE . '/sites/' . rawurlencode($siteUrl) . '/searchAnalytics/query';

		$resp = Http::withToken($token)
			->timeout(60)
			->acceptJson()
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->post($url, $body);

		return [
			'status' => $resp->status(),
			'json'   => $resp->json(),
		];
	}

	/**
	 * Lijst alle properties die ons service-account mag lezen.
	 * GET /sites → {siteEntry: [{siteUrl, permissionLevel}, ...]}.
	 * Gebruikt voor onboarding-verificatie (toegang verleend?) zonder te
	 * hoeven wachten op de geplande import om een 403 te ontdekken.
	 *
	 * @return array{status:int, json:array|null}
	 */
	public function listSites(): array
	{
		$token = $this->auth->accessToken();

		$resp = Http::withToken($token)
			->timeout(30)
			->acceptJson()
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
			->get(self::API_BASE . '/sites');

		return [
			'status' => $resp->status(),
			'json'   => $resp->json(),
		];
	}
}
