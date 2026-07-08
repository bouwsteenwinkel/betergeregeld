<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use App\Services\ChannelSites\GoLiveOrchestrator;
use Illuminate\Console\Command;

/**
 * Zet een channel-site in één keer volledig live:
 * OpenProvider (registratie + DNS) → Plesk (alias + Let's Encrypt) → status live
 * → Google Search Console. Idempotent/re-runnable.
 *
 *   php artisan channel:go-live aannemer
 */
class ChannelGoLive extends Command
{
    protected $signature = 'channel:go-live {site : site-key of domein}';
    protected $description = 'Volledige go-live-keten: OpenProvider-DNS, Plesk-alias + SSL, status live, Search Console.';

    public function handle(GoLiveOrchestrator $orch): int
    {
        $arg  = trim((string) $this->argument('site'));
        $site = Site::query()->where('key', $arg)->orWhere('domain', $arg)->first();

        if (! $site) {
            $this->error("Site niet gevonden: {$arg}");
            return self::FAILURE;
        }

        $this->info("Volledig live: {$site->key} ({$site->domain})\n");
        $res = $orch->run($site);

        foreach ($res['steps'] as $k => $v) {
            $isError = str_starts_with($v, 'FOUT');
            $this->line(sprintf('  %-22s %s', $k, $isError ? "<error>{$v}</error>" : $v));
        }

        $this->newLine();
        $this->line($res['ok'] ? '<info>✓ Volledig live.</info>' : '<comment>Keten gestopt — fix en draai opnieuw.</comment>');

        return self::SUCCESS;
    }
}
