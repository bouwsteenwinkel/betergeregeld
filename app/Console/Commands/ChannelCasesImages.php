<?php

namespace App\Console\Commands;

use App\Services\ChannelSiteResolver;
use App\Services\ChannelSites\ChannelImageGenerator;
use Illuminate\Console\Command;

/**
 * Genereert een resultaat-beeld per voorbeeldcase (config/cases_{channel}.php)
 * via de OpenAI-image-pijplijn. Slot 'case-{slug}'. Idempotent (--force).
 *
 *   php artisan channel:cases:images badkamerspecialist
 */
class ChannelCasesImages extends Command
{
    protected $signature = 'channel:cases:images {channel : channel-key} {--force}';
    protected $description = 'Beelden voor de voorbeeldcases genereren (OpenAI)';

    public function handle(ChannelSiteResolver $resolver, ChannelImageGenerator $img): int
    {
        $key  = (string) $this->argument('channel');
        $site = $resolver->byKey($key);
        if (! $site) {
            $this->error("Onbekend channel: {$key}");
            return self::FAILURE;
        }
        $cases = (array) config("cases_{$key}.cases", []);
        if (! $cases) {
            $this->error("Geen cases in config/cases_{$key}.php.");
            return self::FAILURE;
        }

        $tokens = (array) $site->get('places', []);
        $niche  = (string) ($tokens['niche'] ?? 'vak');
        $done = 0;

        foreach ($cases as $case) {
            $slug = (string) ($case['slug'] ?? '');
            if ($slug === '') {
                continue;
            }
            $prompt = "Professionele, fotorealistische foto van een prachtig afgeleverd resultaat rond {$niche}. "
                . 'Modern, licht, strak en stijlvol, natuurlijk daglicht, hoge kwaliteit, magazine-waardig. '
                . 'Geen tekst, geen logo, geen watermerk, geen mensen.';
            $res = $img->generateRaw($key, 'case-' . $slug, $prompt, '1536x1024', (bool) $this->option('force'));
            if (! empty($res['file'])) {
                $this->line("  ✓ {$slug}  [{$res['status']}]");
                $done++;
            } else {
                $this->warn("  ✗ {$slug}  [{$res['status']}]");
            }
        }

        $this->info("Klaar. {$done} case-beelden.");
        return self::SUCCESS;
    }
}
