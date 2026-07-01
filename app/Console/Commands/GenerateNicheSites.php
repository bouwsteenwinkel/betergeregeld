<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use App\Services\ChannelSites\NicheSiteGenerator;
use Illuminate\Console\Command;

/**
 * Genereert per niche-branche een complete voorbeeldsite met AI-content (Claude).
 * Idempotent: slaat branches die al een site hebben over. Bestand tegen de API-cap:
 * stopt netjes bij herhaalde 400/401/429-fouten zodat een resterende cap niet in
 * honderden mislukte calls resulteert. Opnieuw draaien hervat waar het stopte.
 */
class GenerateNicheSites extends Command
{
    protected $signature = 'channel:generate-niche-sites
        {--only= : Alleen deze branche-keys (komma-gescheiden)}
        {--limit= : Maximaal N branches deze run}
        {--force : Herbouw ook branches die al een site hebben}
        {--model= : Override het Claude-model}
        {--sleep=1 : Seconden pauze tussen calls}';

    protected $description = 'Genereer per niche-branche een voorbeeldsite met AI-content';

    public function handle(NicheSiteGenerator $gen): int
    {
        $only  = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        $force = (bool) $this->option('force');
        $model = $this->option('model') ?: null;
        $sleep = (int) $this->option('sleep');

        $q = Branche::query()->where('active', true);
        if ($only) {
            $q->whereIn('key', $only);
        } elseif (! $force) {
            $q->whereDoesntHave('sites');
        }
        $branches = $q->orderBy('lead_branche')->orderBy('name')->get();
        if ($limit = (int) $this->option('limit')) {
            $branches = $branches->take($limit);
        }

        $total = $branches->count();
        if ($total === 0) {
            $this->info('Niets te doen (alle branches hebben al een site, of geen match).');
            return self::SUCCESS;
        }

        $this->info("Genereren voor {$total} branche(s)…");
        $done = 0; $fail = 0; $capHits = 0; $inTok = 0; $outTok = 0;

        foreach ($branches as $i => $branche) {
            $n = $i + 1;
            $res = $gen->generateForBranche($branche, $force, $model);

            if ($res['ok'] ?? false) {
                $done++;
                $u = $res['usage'] ?? [];
                $inTok += (int) ($u['input'] ?? 0);
                $outTok += (int) ($u['output'] ?? 0);
                $this->line(sprintf('  [%d/%d] ✓ %s → "%s"  (%d in / %d out)',
                    $n, $total, $branche->name, $res['brand'] ?? '?', $u['input'] ?? 0, $u['output'] ?? 0));
            } else {
                $err = (string) ($res['error'] ?? 'onbekend');
                if ($err === 'heeft-al-site') {
                    $this->line("  [{$n}/{$total}] · {$branche->name}: heeft al een site, overgeslagen");
                    continue;
                }
                $fail++;
                $this->warn("  [{$n}/{$total}] ✗ {$branche->name}: {$err}");

                // Cap/rate/auth → geen zin om door te blijven proberen.
                if (preg_match('/HTTP (400|401|402|429)|credit|rate|overloaded|cap/i', $err)) {
                    $capHits++;
                    if ($capHits >= 2) {
                        $this->error('Herhaalde cap/rate/auth-fout. Batch gestopt; draai later opnieuw om te hervatten.');
                        break;
                    }
                } else {
                    $capHits = 0;
                }
            }

            if ($sleep > 0 && $n < $total) {
                sleep($sleep);
            }
        }

        $this->newLine();
        $this->info("Klaar: {$done} gegenereerd, {$fail} mislukt. Tokens: {$inTok} in / {$outTok} out.");
        return self::SUCCESS;
    }
}
