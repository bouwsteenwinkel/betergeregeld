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
		return $this->findWebResource($domain) !== null;
	}

	/**
	 * Voegt e-mailadressen toe als delegated owner van de geverifieerde property,
	 * zodat die accounts het domein óók in hun eigen Search Console zien (het
	 * service-account blijft technisch eigenaar). Idempotent: al-eigenaars worden
	 * overgeslagen. Vereist dat het domein al geverifieerd is.
	 *
	 * @param  string[] $emails
	 * @return array{status:int, added:string[], already?:bool, skipped?:bool, json?:array|null}
	 */
	public function addOwners(string $domain, array $emails): array
	{
		$emails = array_values(array_filter(array_map('trim', $emails)));
		if ($emails === []) {
			return ['status' => 0, 'added' => [], 'skipped' => true];
		}

		$resource = $this->findWebResource($domain);
		if ($resource === null) {
			throw new RuntimeException("Kan geen eigenaar toevoegen: {$domain} is (nog) niet geverifieerd bij het service-account.");
		}

		$current = array_map('strtolower', (array) data_get($resource, 'owners', []));
		$toAdd   = array_values(array_filter($emails, fn ($e) => ! in_array(strtolower($e), $current, true)));
		if ($toAdd === []) {
			return ['status' => 200, 'added' => [], 'already' => true];
		}

		$id     = (string) data_get($resource, 'id');
		$owners = array_values(array_unique(array_merge((array) data_get($resource, 'owners', []), $toAdd)));

		// webResource.update: PUT mét body (de volledige resource + nieuwe owners).
		$resp = $this->http()->put(self::API_BASE . '/webResource/' . rawurlencode($id), [
			'id'     => $id,
			'site'   => data_get($resource, 'site'),
			'owners' => $owners,
		]);

		return ['status' => $resp->status(), 'added' => $toAdd, 'json' => $resp->json()];
	}

	/** De geverifieerde webResource (id/site/owners) voor dit INET_DOMAIN, of null. */
	private function findWebResource(string $domain): ?array
	{
		$resp = $this->http()->get(self::API_BASE . '/webResource');
		foreach ((array) data_get($resp->json(), 'items', []) as $item) {
			if ((string) data_get($item, 'site.type') === 'INET_DOMAIN'
				&& (string) data_get($item, 'site.identifier') === $domain) {
				return (array) $item;
			}
		}
		return null;
	}

	private function http()
	{
		return Http::withToken($this->auth->accessToken(GoogleApiAuth::SITEVERIFY_SCOPE))
			->timeout(30)
			->acceptJson()
			->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()]);
	}
}
