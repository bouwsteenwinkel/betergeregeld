<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use Illuminate\Console\Command;

/**
 * Spoort kapotte meervouden in de branche-tokens op, en herstelt ze.
 *
 * Aanleiding (05-09-2026): het `niches`-token is ooit machinaal gemaakt door er
 * "en" of "s" achter te plakken. Dat gaat bij het Nederlands vaak mis, en het
 * `trades`-token -- dat wél met de hand is nagelopen -- laat precies zien hoe
 * het had gemoeten:
 *
 *     advocaat      trades: advocaten        niches: advocaaten
 *     klusbedrijf   trades: klusbedrijven    niches: klusbedrijfen
 *     psycholoog    trades: psychologen      niches: psycholoogen
 *     yogastudio    trades: yogastudio's     niches: yogastudios
 *
 * 40 van de 204 branches. Vijf daarvan staan live, en op
 * jouw-klusbedrijf-website.nl stond het woord op elke pagina; bij de andere
 * vier op de plaatspagina's -- juist de enige pagina's die klikken opleveren.
 * Met 195 branches in de wachtrij loopt dat mee naar de hele uitbreiding.
 *
 * De regel is smal gehouden: alleen waar `trade` en `niche` hetzelfde woord
 * zijn, want dan hoort het meervoud dat ook te zijn. Verschillen ze (bakkerij /
 * brood), dan blijft het met de hand werk en meldt dit commando het alleen.
 *
 * Het foute woord zit ook vastgebakken in `business.label` en `business.intro`,
 * dus die worden meegenomen -- vandaar dat de hele places-JSON langsloopt en
 * niet alleen het token.
 *
 *   php artisan channel:tokens:controle              (alleen melden)
 *   php artisan channel:tokens:controle --herstel    (ook wegschrijven)
 */
class ChannelTokensControle extends Command
{
    protected $signature = 'channel:tokens:controle
        {--herstel : de gevonden meervouden ook echt wegschrijven}
        {--branche= : alleen deze branche-key}';

    protected $description = 'Controleert de branche-tokens op kapotte meervouden (en herstelt ze)';

    public function handle(): int
    {
        $standaard = (array) config('channel_places.defaults', []);
        $herstel   = (bool) $this->option('herstel');

        $query = Branche::orderBy('key');
        if ($key = $this->option('branche')) {
            $query->where('key', $key);
        }

        $rijen = [];
        $handwerk = [];

        foreach ($query->get() as $branche) {
            $places = (array) $branche->places;
            $t = array_merge($standaard, array_filter($places, fn ($v) => is_scalar($v) && $v !== ''));

            $niche  = (string) ($t['niche'] ?? '');
            $niches = (string) ($t['niches'] ?? '');
            $trade  = (string) ($t['trade'] ?? '');
            $trades = (string) ($t['trades'] ?? '');

            if ($niche === '' || $niches === '' || $trades === '' || $niches === $trades) {
                continue;
            }

            // Wijken enkelvoud en vakwoord af, dan levert "neem trades over"
            // onzin op ("badkamerbedrijven" waar "badkamers" hoort). Dan geldt
            // alleen een woord dat met de hand in de config is gezet.
            $goed = $trades;
            if (mb_strtolower($trade) !== mb_strtolower($niche)) {
                $goed = (string) config('channel_places.meervoud_uitzonderingen.' . $branche->key, '');
                if ($goed === '' || $goed === $niches) {
                    $handwerk[] = [$branche->key, $niche, $niches, $trades];
                    continue;
                }
            }

            $rijen[] = [$branche->key, $niches, $goed];

            if ($herstel) {
                $branche->places = $this->vervang($places, $niches, $goed);
                $branche->save();
            }
        }

        if ($rijen) {
            $this->table(['BRANCHE', 'FOUT', 'WORDT'], $rijen);
            $this->line(sprintf('%d branches met een kapot meervoud.', count($rijen)));
        } else {
            $this->info('Geen kapotte meervouden gevonden.');
        }

        if ($handwerk) {
            $this->newLine();
            $this->warn('Deze wijken af tussen trade en niche, en staan niet in');
            $this->warn('channel_places.meervoud_uitzonderingen — met de hand nakijken:');
            $this->table(['BRANCHE', 'niche', 'niches', 'trades'], $handwerk);
        }

        if ($rijen && ! $herstel) {
            $this->newLine();
            $this->line('Niets gewijzigd. Draai met --herstel om het weg te schrijven.');
        }
        if ($rijen && $herstel) {
            $this->newLine();
            $this->info('Weggeschreven. Vergeet de view-cache niet: php artisan view:clear');
        }

        return self::SUCCESS;
    }

    /**
     * Vervangt het foute meervoud overal in de places-JSON.
     *
     * Ook in business.label en business.intro, waar het als gewone tekst is
     * ingevroren ("Klusbedrijfen in :city"). Hoofdletter aan het woordbegin
     * blijft staan, want die zinnen beginnen er soms mee.
     *
     * @param  array<string,mixed> $places
     * @return array<string,mixed>
     */
    private function vervang(array $places, string $fout, string $goed): array
    {
        $paren = [
            $fout                => $goed,
            mb_strtoupper(mb_substr($fout, 0, 1)) . mb_substr($fout, 1)
                                 => mb_strtoupper(mb_substr($goed, 0, 1)) . mb_substr($goed, 1),
        ];

        array_walk_recursive($places, function (&$waarde) use ($paren) {
            if (is_string($waarde)) {
                $waarde = strtr($waarde, $paren);
            }
        });

        return $places;
    }
}
