<?php

namespace App\Console\Commands;

use App\Services\ChannelSites\ChannelImageGenerator;
use Illuminate\Console\Command;

/**
 * Genereert de art-directed beelden voor channel-sites.
 *
 *   php artisan channels:images installateur      # één kanaal
 *   php artisan channels:images --all             # alle kanalen
 *   php artisan channels:images installateur --slot=hero --force
 *
 * Zonder OPENAI_API_KEY draait het in fake-mode (SVG-preview met de prompt).
 */
class GenerateChannelImages extends Command
{
    protected $signature = 'channels:images
        {key? : kanaal-key uit config/channel_sites.php}
        {--all : alle kanalen}
        {--slot=* : alleen deze slot(s), standaard alle}
        {--force : opnieuw genereren ook als het al bestaat}
        {--optimize : alleen (her)optimaliseren naar WebP, geen API-call}';

    protected $description = 'Genereer per branche de art-directed beelden voor de channel-sites';

    public function handle(ChannelImageGenerator $gen): int
    {
        $channels = (array) config('channel_sites.channels', []);
        $key = $this->argument('key');

        if (! $key && ! $this->option('all')) {
            $this->error('Geef een kanaal-key op, of gebruik --all.');
            return self::FAILURE;
        }

        $targets = $this->option('all') ? array_keys($channels) : [$key];
        $slots   = $this->option('slot') ?: array_keys($gen->slots());
        $force   = (bool) $this->option('force');

        if ($gen->isFake()) {
            $this->warn('Fake-mode (geen OPENAI_API_KEY): er worden SVG-previews met de prompt geschreven.');
        }

        foreach ($targets as $k) {
            $cfg = $channels[$k] ?? null;
            if (! $cfg) {
                $this->error("Onbekend kanaal: {$k}");
                continue;
            }
            // Site-specifiek beeldrecept gaat vóór het sector-recept: bestaat er een
            // recept met de naam van de channel-key (bv. 'rijschool'), gebruik dat.
            $branche = config('channel_images.branches.' . $k)
                ? $k
                : (string) ($cfg['branche'] ?? 'overig');
            $accent  = data_get($cfg, 'theme.accent');

            $this->line("<info>{$k}</info> (branche: {$branche})");

            if ($this->option('optimize')) {
                foreach ($slots as $slot) {
                    $made = $gen->optimize($k, $slot);
                    $this->line($made ? "  <info>✓</info> {$slot}: WebP-varianten " . implode('/', $made) . 'px' : "  · {$slot}: geen bron-PNG");
                }
                continue;
            }

            foreach ($slots as $slot) {
                $r = $gen->generate($k, $branche, $slot, $accent, $force);
                $tag = $r['status'] === 'gegenereerd' ? '<info>✓</info>'
                     : ($r['status'] === 'fake-preview' ? '<comment>≈</comment>'
                     : ($r['status'] === 'bestaat-al' ? '·' : '<error>✗</error>'));
                $this->line("  {$tag} {$slot}: {$r['status']}" . ($r['file'] ? "  → {$r['file']}" : ''));
            }
        }

        $this->newLine();
        $this->info('Klaar. Controleer de beelden in de admin/preview voordat een site live gaat.');
        return self::SUCCESS;
    }
}
