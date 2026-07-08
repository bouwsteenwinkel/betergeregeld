<?php

namespace App\Services\ChannelSites;

use App\Models\Channel\Site;
use App\Services\OpenProvider\OpenProviderClient;
use App\Services\Plesk\PleskClient;
use App\Services\Seo\GscProvisioner;

/**
 * Zet een channel-site in één keer volledig live:
 *   1. OpenProvider  — domein registreren + DNS (A @ / www) naar de VPS
 *   2. Plesk         — domein-alias onder betergeregeld.com + Let's Encrypt
 *   3. Status        — site op 'live'
 *   4. Search        — GSC-property verifiëren/toevoegen + sitemap indienen
 *
 * Elke stap is idempotent (al-gedaan wordt overgeslagen). Bij een fout stopt de
 * keten en wordt teruggegeven wat wél lukte, zodat je na een fix opnieuw kunt
 * draaien zonder dubbel werk.
 */
class GoLiveOrchestrator
{
    public function __construct(
        private readonly OpenProviderClient $op,
        private readonly PleskClient $plesk,
        private readonly GscProvisioner $gsc,
    ) {}

    /**
     * @return array{ok:bool, steps:array<string,string>}
     */
    public function run(Site $site): array
    {
        $steps  = [];
        $domain = strtolower(trim(preg_replace('#^https?://#', '', (string) $site->domain), '/. '));

        if ($domain === '') {
            return ['ok' => false, 'steps' => ['domein' => 'ontbreekt op deze site']];
        }

        // 1. OpenProvider: registreren + DNS (idempotent op meta.domain_registered_at)
        try {
            if (blank(data_get($site->meta, 'domain_registered_at'))) {
                $r = $this->op->registerWithDns($domain);
                $meta = (array) $site->meta;
                $meta['domain_registered_at'] = $r['registered_at'];
                $meta['openprovider'] = ['domain_id' => $r['domain_id'], 'ip' => $r['ip']];
                $site->meta = $meta;
                $site->save();
                $steps['1. openprovider'] = ($r['already'] ?? false)
                    ? 'was al geregistreerd; DNS gecontroleerd'
                    : "geregistreerd + DNS naar {$r['ip']}";
            } else {
                $steps['1. openprovider'] = 'al geregistreerd';
            }
        } catch (\Throwable $e) {
            $steps['1. openprovider'] = 'FOUT: ' . $e->getMessage();
            return ['ok' => false, 'steps' => $steps];
        }

        // 2. Plesk: alias + Let's Encrypt
        try {
            $this->plesk->provisionAlias($domain);
            $meta = (array) $site->meta;
            $meta['plesk_provisioned_at'] = now()->toIso8601String();
            $site->meta = $meta;
            $site->save();
            $steps['2. plesk'] = "alias van {$this->plesk->parentDomain()} + Let's Encrypt";
        } catch (\Throwable $e) {
            $steps['2. plesk'] = 'FOUT: ' . $e->getMessage();
            return ['ok' => false, 'steps' => $steps];
        }

        // 3. Status op live
        if ($site->status !== 'live') {
            $site->status = 'live';
            $site->save();
            $steps['3. status'] = 'op live gezet';
        } else {
            $steps['3. status'] = 'was al live';
        }

        // 4. Search Console
        try {
            $g = $this->gsc->provision($site);
            if ($g['ok']) {
                $meta = (array) $site->meta;
                $meta['gsc_provisioned_at'] = now()->toIso8601String();
                $site->meta = $meta;
                $site->save();
            }
            foreach ($g['steps'] as $k => $v) {
                $steps["4. search · {$k}"] = $v;
            }
            return ['ok' => $g['ok'], 'steps' => $steps];
        } catch (\Throwable $e) {
            $steps['4. search'] = 'FOUT: ' . $e->getMessage();
            return ['ok' => false, 'steps' => $steps];
        }
    }
}
