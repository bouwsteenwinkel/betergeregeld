<?php

namespace App\Console\Commands;

use App\Models\WebsiteLead;
use App\Services\ChannelSites\ChannelImageGenerator;
use Illuminate\Console\Command;

/**
 * Genereert de documentaire AI-beelden PER SECTOR (lead-branche), niet per site.
 * Eén set (hero + detail) per sector bedient alle niche-sites in die sector
 * (channel-media/<sector>/). Idempotent: bestaande beelden worden overgeslagen
 * tenzij --force. Bewust per sector om kosten + opslag beheersbaar te houden.
 */
class GenerateSectorImages extends Command
{
    protected $signature = 'channel:generate-sector-images
        {--only= : Alleen deze sector-keys (komma-gescheiden)}
        {--slots=hero,detail : Welke slots}
        {--force : Herbouw ook bestaande beelden}';

    protected $description = 'Genereer documentaire AI-beelden per sector (hero + detail)';

    public function handle(ChannelImageGenerator $gen): int
    {
        if ($gen->isFake()) {
            $this->error('Fake-mode: geen OpenAI-key. Zet OPENAI_API_KEY in .env + config:clear.');
            return self::FAILURE;
        }

        $only  = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        $slots = array_filter(array_map('trim', explode(',', (string) $this->option('slots'))));
        $force = (bool) $this->option('force');

        $sectors = array_keys(WebsiteLead::BRANCHES);
        if ($only) {
            $sectors = array_values(array_intersect($sectors, $only));
        }

        $done = 0; $fail = 0;
        foreach ($sectors as $sector) {
            foreach ($slots as $slot) {
                $r = $gen->generate($sector, $sector, $slot, null, $force);
                $ok = in_array($r['status'], ['gegenereerd', 'bestaat-al'], true);
                $ok ? $done++ : $fail++;
                $this->line(sprintf('  %s/%s: %s', $sector, $slot, $r['status']));
            }
        }

        $this->newLine();
        $this->info("Klaar: {$done} ok, {$fail} mislukt.");
        return self::SUCCESS;
    }
}
