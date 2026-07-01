<?php

namespace App\Console\Commands;

use App\Services\ChannelSites\ChannelContentGenerator;
use App\Services\ChannelSiteResolver;
use Illuminate\Console\Command;

/**
 * Genereert unieke plaats-teksten per channel-site (tegen duplicate content).
 *
 *   php artisan channels:content installateur            # eerste 10 plaatsen
 *   php artisan channels:content installateur --limit=25
 *   php artisan channels:content --all --limit=10 --force
 *
 * Zonder ANTHROPIC_API_KEY draait het in fake-mode (gevarieerde placeholder).
 */
class GenerateChannelContent extends Command
{
    protected $signature = 'channels:content
        {key? : kanaal-key}
        {--all : alle kanalen}
        {--limit=10 : aantal plaatsen}
        {--faq : genereer de FAQ i.p.v. plaatsen}
        {--force : opnieuw genereren}';

    protected $description = 'Genereer unieke plaats-teksten per channel-site via Claude';

    public function handle(ChannelContentGenerator $gen, ChannelSiteResolver $resolver): int
    {
        $key = $this->argument('key');
        if (! $key && ! $this->option('all')) {
            $this->error('Geef een kanaal-key op, of gebruik --all.');
            return self::FAILURE;
        }

        if ($gen->isFake()) {
            $this->warn('Fake-mode (geen ANTHROPIC_API_KEY): gevarieerde placeholder-teksten.');
        }

        $keys   = $this->option('all') ? array_keys($resolver->all()) : [$key];
        $places = array_slice($resolver->places(), 0, (int) $this->option('limit'), true);
        $force  = (bool) $this->option('force');

        foreach ($keys as $k) {
            $site = $resolver->byKey($k);
            if (! $site) {
                $this->error("Onbekend kanaal: {$k}");
                continue;
            }
            if ($this->option('faq')) {
                $r = $gen->faq($site, $force);
                $this->line("<info>{$k}</info> — FAQ: {$r['status']} (" . count($r['items']) . ' vragen)');
                continue;
            }

            $this->line("<info>{$k}</info> — " . count($places) . ' plaatsen');
            $made = 0;
            foreach ($places as $slug => $city) {
                $r = $gen->placeIntro($site, $slug, $city, $force);
                if ($r['status'] !== 'bestaat-al') {
                    $made++;
                }
            }
            $this->line("  ✓ {$made} nieuw, " . (count($places) - $made) . ' bestonden al');
        }

        $this->newLine();
        $this->info('Klaar. Controleer de teksten in de preview voordat een site live gaat.');
        return self::SUCCESS;
    }
}
