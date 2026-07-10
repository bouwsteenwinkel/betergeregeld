<?php

namespace App\Services\Seo;

use App\Models\Channel\Site;
use App\Services\OpenProvider\OpenProviderClient;

/**
 * Regelt Google Search (Console) automatisch in voor een channel-site-domein:
 *   1. DNS-TXT-verificatietoken ophalen (Site Verification API)
 *   2. TXT-record plaatsen via OpenProvider
 *   3. eigendom verifiëren (service-account wordt eigenaar)
 *   4. property toevoegen (Search Console API)
 *   5. sitemap indienen
 *
 * Volledig idempotent en re-runnable: al-geverifieerd/al-toegevoegd wordt
 * netjes overgeslagen; als DNS nog propageert wordt 'verify: pending' gemeld
 * zodat je later opnieuw kunt draaien.
 */
class GscProvisioner
{
	public function __construct(
		private readonly GoogleSiteVerificationClient $verify,
		private readonly GoogleSearchConsoleClient $gsc,
		private readonly OpenProviderClient $op,
	) {}

	/**
	 * @return array{domain:string, steps:array<string,string>, ok:bool}
	 */
	public function provision(Site $site, bool $dryRun = false): array
	{
		$domain  = strtolower(trim(preg_replace('#^https?://#', '', (string) $site->domain), '/. '));
		$steps   = [];

		if ($domain === '') {
			return ['domain' => '', 'steps' => ['skip' => 'geen domein op deze site'], 'ok' => false];
		}

		// URL-prefix-property i.p.v. domein-property: sites.add (Webmasters v3)
		// ondersteunt geen 'sc-domain:'. DNS-verificatie dekt ook de URL-prefix.
		$prop    = 'https://' . $domain . '/';
		$sitemap = 'https://' . $domain . '/sitemap.xml';

		if ($site->status !== 'live') {
			$steps['let_op'] = "site-status is '{$site->status}', niet 'live' (Google indexeert alleen live, bereikbare domeinen)";
		}

		if ($dryRun) {
			$steps['plan'] = "token → OpenProvider TXT → verify → addSite {$prop} → sitemap {$sitemap}";
			return ['domain' => $domain, 'steps' => $steps, 'ok' => true];
		}

		// 1–3. verificatie
		if ($this->verify->isVerified($domain)) {
			$steps['verify'] = 'al geverifieerd';
		} else {
			$token = $this->verify->getDnsToken($domain);
			$steps['token'] = 'opgehaald';

			// TXT plaatsen via OpenProvider. Staat de DNS-zone daar niet (domein
			// beheerd bij een andere provider), dan faalt dit — geen showstopper:
			// toon de TXT-waarde zodat 'ie handmatig geplaatst kan worden en draai
			// daarna opnieuw. De verify hieronder slaagt zodra het record live is.
			try {
				$this->op->addTxtRecord($domain, $token);
				$steps['dns_txt'] = 'geplaatst via OpenProvider';
			} catch (\Throwable $e) {
				$steps['dns_txt'] = 'HANDMATIG plaatsen (OpenProvider: ' . $e->getMessage() . ') → TXT op @ met waarde: ' . $token;
			}

			$v = $this->verify->verify($domain);
			if (in_array($v['status'], [200, 201], true)) {
				$steps['verify'] = 'geslaagd';
			} else {
				$steps['verify'] = "pending (status {$v['status']}) — TXT nog niet zichtbaar (DNS propageert of nog niet geplaatst); draai later opnieuw";
				return ['domain' => $domain, 'steps' => $steps, 'ok' => false];
			}
		}

		// 4. property toevoegen
		$a = $this->gsc->addSite($prop);
		$addOk = in_array($a['status'], [200, 204], true);
		$steps['add_site'] = $addOk
			? "toegevoegd ({$prop})"
			: "mislukt (status {$a['status']}): " . \Illuminate\Support\Str::limit(json_encode($a['json']), 180);

		// 5. sitemap indienen
		$s = $this->gsc->submitSitemap($prop, $sitemap);
		$sitemapOk = in_array($s['status'], [200, 204], true);
		$steps['sitemap'] = $sitemapOk
			? 'ingediend'
			: "mislukt (status {$s['status']}): " . \Illuminate\Support\Str::limit(json_encode($s['json']), 180);

		// 6. mede-eigenaars: voeg de eigen Google-accounts toe als delegated owner
		// zodat de property ook in hún Search Console verschijnt. Niet-fataal.
		$owners = (array) config('seo.gsc_owner_emails', []);
		if ($owners !== []) {
			try {
				$o = $this->verify->addOwners($domain, $owners);
				$steps['owners'] = ! empty($o['added'])
					? 'toegevoegd: ' . implode(', ', $o['added'])
					: (($o['skipped'] ?? false) ? 'geen ingesteld' : 'al eigenaar');
			} catch (\Throwable $e) {
				$steps['owners'] = 'mislukt: ' . $e->getMessage();
			}
		}

		return ['domain' => $domain, 'steps' => $steps, 'ok' => $addOk && $sitemapOk];
	}
}
