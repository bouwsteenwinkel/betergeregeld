<?php

namespace App\Services\Seo;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper rond de Google Site Verification API (v1). Auth via GoogleApiAuth
 * met de siteverification-scope. Gebruikt om een domein-property automatisch te
 * verifiëren via een DNS-TXT-record (dat we via OpenProvider plaatsen), zodat het
 * service-account daarna de Search Console-property kan beheren.
 */
class GoogleSiteVerificationClient
{
	public const API_BASE = 'https://www.googleapis.com/siteVerification/v1';

	public function __construct(private readonly GoogleApiAuth $auth) {}

	/**
	 * Vraagt het DNS-TXT-verificatietoken op voor een domein-property.
	 * Retourneert de TXT-waarde, bijv. 'google-site-verification=AbC...'.
	 */
	public function getDnsToken(string $domain): string
	{
		$resp = $this->http()->post(self::API_BASE . '/token', [
			'verificationMethod' => 'DNS_TXT',
			'site'               => ['type' => 'INET_DOMAIN', 'identifier' => $domain],
		]);

		$token = (string) data_get($resp->json(), 'token');
		if (! $resp->successful() || $token === '') {
			throw new RuntimeException('Site-verificatietoken ophalen mislukt (' . $resp->status() . '): ' . $resp->body());
		}
		return $token;
	}

	/**
	 * Verifieert eigendom via DNS_TXT (het TXT-record moet al live staan).
	 * 200/201 = geslaagd; een andere status betekent meestal "TXT nog niet
	 * zichtbaar" (DNS propageert nog) — dan later opnieuw proberen.
	 *
	 * @return array{status:int, json:array|null}
	 */
	public function verify(string $domain): array
	{
		$resp = $this->http()->post(self::API_BASE . '/webResource?verificationMethod=DNS_TXT', [
			'site' => ['type' => 'INET_DOMAIN', 'identifier' => $domain],
		]);

		return ['status' => $resp->status(), 'json' => $resp->json()];
	}

	/** Is dit domein al als INET_DOMAIN geverifieerd door ons service-account? */
	public function isVerified(string $domain): bool
	{
		$resp = $this->http()->get(self::API_BASE . '/webResource');
		foreach ((array) data_get($resp->json(), 'items', []) as $item) {
			if ((string) data_get($item, 'site.type') === 'INET_DOMAIN'
				&& (string) data_get($item, 'site.identifier') === $domain) {
				return true;
			}
		}
		return false;
	}

	private function http()
	{
		return Http::withToken($this->auth->accessToken(GoogleApiAuth::SITEVERIFY_SCOPE))
			->timeout(30)
			->acceptJson()
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()]);
	}
}
