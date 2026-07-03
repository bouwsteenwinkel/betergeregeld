<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Rolt de /plaatsen-opzet uit naar ÁLLE niche-branches. Zet per branche default
 * plaatsen-tokens (afgeleid van naam/key) zodat elke niche-site meteen werkende,
 * branche-gerichte plaatsen-pagina's heeft. De zware copy is generiek gedeeld
 * (config/channel_places.php); per niche kunnen de tokens later verfijnd worden.
 *
 * Slaat branches met al ingevulde tokens over (tenzij --force), zodat curated
 * branches (bv. badkamer) niet overschreven worden.
 */
class ChannelPlacesRollout extends Command
{
    protected $signature = 'channel:places:rollout {--force : Overschrijf ook branches die al tokens hebben}';
    protected $description = 'Default plaatsen-tokens op alle niche-branches zetten';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $done = 0;
        $skipped = 0;

        foreach (Branche::all() as $branche) {
            if (! empty($branche->places) && ! $force) {
                $skipped++;
                continue;
            }

            $trade  = $this->trade($branche);             // trade-noun uit de branche-naam
            $plural = $this->pluralize($trade);

            $branche->places = array_merge((array) $branche->places, [
                'trade'   => $trade,
                'trades'  => $plural,
                'niche'   => $trade,
                'niches'  => $plural,
                'service' => 'website',
                'business' => [
                    'label'       => Str::ucfirst($plural) . ' in :city',
                    'intro'       => 'Dit zijn ' . $plural . ' in :city en omgeving — het landschap waarin jij online moet opvallen. Met een sterke website sta jij bovenaan als iemand in :city een ' . $trade . ' zoekt.',
                    'query'       => $trade . ' :city',
                    'limit'       => 8,
                    'min_reviews' => 3,
                ],
            ]);
            $branche->save();
            $done++;
            $this->line("  ✓ {$branche->key}  (trade: {$trade}, plural: {$plural})");
        }

        $this->info("Klaar. {$done} branches ingesteld, {$skipped} overgeslagen (al ingevuld).");
        $this->line('Let op: trade/meervoud zijn afgeleid uit de branche-naam. Verfijn per niche waar nodig (zie badkamer als voorbeeld).');
        return self::SUCCESS;
    }

    /** Trade-noun (enkelvoud) uit de branche-naam: "CV-installateur" → "cv-installateur". */
    private function trade(Branche $b): string
    {
        $raw = (string) ($b->name ?: $b->key);
        $raw = preg_replace('/\s*\(.*?\)\s*/', '', $raw);   // "Kapper (algemeen)" → "Kapper"
        $raw = trim(mb_strtolower($raw));
        return $raw !== '' ? $raw : 'bedrijf';
    }

    /** Ruwe NL-pluralisatie (voldoende voor een eerste rollout; per niche te verfijnen). */
    private function pluralize(string $w): string
    {
        if (preg_match('/(ij|ing)$/u', $w)) {
            return $w . 'en';                                // bakkerij→bakkerijen, ontharing→ontharingen
        }
        if (preg_match('/(er|eur|aar|oor|ar|el|em|en|on|e|a|o|u|y|nt)$/u', $w)) {
            return $w . 's';                                 // installateur→...s, hotel→hotels, restaurant→restaurants
        }
        return $w . 'en';                                    // rest → ...en (fysiotherapeut→...en)
    }
}
