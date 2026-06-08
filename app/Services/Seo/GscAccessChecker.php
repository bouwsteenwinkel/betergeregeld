<?php

namespace App\Services\Seo;

use App\Models\Seo\SeoProperty;
use Throwable;

/**
 * Verifieert of ons gedeelde service-account leestoegang heeft tot een
 * GSC-property. Leunt op de sites.list-endpoint zodat onboarding direct
 * feedback geeft i.p.v. te wachten op de geplande import (die anders pas
 * met een 403 in last_import_error laat zien dat de klant ons account nog
 * niet heeft toegevoegd).
 */
class GscAccessChecker
{
	public function __construct(
		private readonly GoogleSearchConsoleClient $gsc,
		private readonly GoogleApiAuth $auth,
	) {
	}

	/**
	 * @return array{status:string, message:string, sites:array<string,string>, service_account:?string}
	 *   status = granted | pending | error
	 *   sites  = [siteUrl => permissionLevel] van alles wat ons account mag lezen
	 */
	public function check(SeoProperty $property): array
	{
		$email = $this->auth->serviceAccountEmail();
		$base = ['sites' => [], 'service_account' => $email];

		try {
			$resp = $this->gsc->listSites();
		} catch (Throwable $e) {
			return $base + ['status' => 'error', 'message' => $e->getMessage()];
		}

		if (($resp['status'] ?? 0) !== 200) {
			$err = $resp['json']['error']['message'] ?? ('HTTP ' . ($resp['status'] ?? '?'));

			return $base + ['status' => 'error', 'message' => $err];
		}

		$sites = [];
		foreach (($resp['json']['siteEntry'] ?? []) as $entry) {
			$url = (string) ($entry['siteUrl'] ?? '');
			if ($url !== '') {
				$sites[$url] = (string) ($entry['permissionLevel'] ?? '');
			}
		}

		$target = (string) $property->site_url;

		if (isset($sites[$target])) {
			return [
				'status'          => 'granted',
				'message'         => "Toegang verleend ({$sites[$target]}).",
				'sites'           => $sites,
				'service_account' => $email,
			];
		}

		return [
			'status'          => 'pending',
			'message'         => "Nog geen toegang tot '{$target}'. Voeg ons service-account toe als gebruiker in de Search Console van deze property.",
			'sites'           => $sites,
			'service_account' => $email,
		];
	}
}
