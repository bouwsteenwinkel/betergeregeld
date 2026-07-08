<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\Plesk\PleskClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Voegt een niche-domein als domein-alias van betergeregeld.com toe in Plesk
 * en (her)geeft het Let's Encrypt-certificaat uit.
 *
 *   php artisan plesk:alias aannemer        # één site (key of domein)
 *   php artisan plesk:alias --all           # alle live sites met een domein
 */
class PleskAlias extends Command
{
    protected $signature = 'plesk:alias {site? : site-key of domein} {--all : alle live sites met een domein}';
    protected $description = 'Plesk: domein-alias toevoegen onder betergeregeld.com + Let\'s Encrypt (her)uitgeven.';

    public function handle(PleskClient $plesk): int
    {
        if (! $plesk->isConfigured()) {
            $this->error('Plesk niet geconfigureerd: zet PLESK_BASE_URL + PLESK_API_KEY (of admin-creds) in .env.');
            return self::FAILURE;
        }

        $sites = $this->targets();
        if ($sites->isEmpty()) {
            $this->error('Geen sites gevonden. Geef een site-key/domein of gebruik --all.');
            return self::FAILURE;
        }

        $ok = 0;
        foreach ($sites as $site) {
            $this->line("── <options=bold>{$site->key}</> ({$site->domain})");
            if (blank($site->domain)) {
                $this->line('   <comment>geen domein — overgeslagen</comment>');
                continue;
            }
            try {
                $r = $plesk->provisionAlias((string) $site->domain);
                foreach ($r['steps'] as $k => $v) {
                    $this->line(sprintf('   %-6s %s', $k, $v));
                }
                $this->line('   => <info>OK</info>');
                $ok++;
            } catch (\Throwable $e) {
                $this->line('   <error>FOUT: ' . $e->getMessage() . '</error>');
            }
        }

        $this->info("Klaar: {$ok}/{$sites->count()} gelukt.");
        return self::SUCCESS;
    }

    private function targets(): Collection
    {
        if ($this->option('all')) {
            return Site::query()
                ->whereNotNull('domain')->where('domain', '!=', '')
                ->where('status', 'live')->orderBy('key')->get();
        }
        $arg = trim((string) $this->argument('site'));
        if ($arg === '') {
            return collect();
        }
        return Site::query()->where('key', $arg)->orWhere('domain', $arg)->get();
    }
}
