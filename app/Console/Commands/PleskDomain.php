<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\Plesk\PleskClient;
use Illuminate\Console\Command;

/**
 * Aparte-domein-aanpak: maakt een niche-domein als addon-domein binnen de
 * betergeregeld.com-subscription met de GEDEELDE app-docroot. Elk domein krijgt
 * zo z'n eigen hosting/certificaat. SSL via SSL It! auto-secure.
 *
 *   php artisan plesk:domain aannemer
 */
class PleskDomain extends Command
{
    protected $signature = 'plesk:domain {site : site-key of domein}';
    protected $description = 'Plesk: niche-domein als addon-domein met gedeelde docroot (eigen cert per domein).';

    public function handle(PleskClient $plesk): int
    {
        if (! $plesk->isConfigured()) {
            $this->error('Plesk niet geconfigureerd (.env: PLESK_BASE_URL + PLESK_API_KEY).');
            return self::FAILURE;
        }

        $arg  = trim((string) $this->argument('site'));
        $site = Site::query()->where('key', $arg)->orWhere('domain', $arg)->first();
        if (! $site || blank($site->domain)) {
            $this->error("Site/domein niet gevonden: {$arg}");
            return self::FAILURE;
        }

        $this->info("Addon-domein aanmaken: {$site->domain}");
        $this->line('  utility : ' . config('plesk.domain_utility'));
        $this->line('  docroot : ' . config('plesk.shared_docroot'));
        $this->newLine();

        try {
            $r = $plesk->provisionSharedDomain((string) $site->domain);
            foreach ($r['steps'] as $k => $v) {
                $this->line(sprintf('  %-7s %s', $k, $v));
            }
            $this->newLine();
            $this->line("<info>OK</info> — controleer: https://{$site->domain}");
            $this->line('SSL: zet in Plesk SSL It! "Domeinen automatisch beveiligen" aan, dan krijgt het domein z\'n eigen Let\'s Encrypt-cert.');
        } catch (\Throwable $e) {
            $this->error('FOUT: ' . $e->getMessage());
            $this->line('Klopt een parameter niet? Pas PLESK_DOMAIN_UTILITY / de params aan; stuur me de melding.');
        }

        return self::SUCCESS;
    }
}
