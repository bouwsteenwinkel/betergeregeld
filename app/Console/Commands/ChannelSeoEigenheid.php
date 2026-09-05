<?php

namespace App\Console\Commands;

use App\Models\Channel\Site;
use Illuminate\Console\Command;

/**
 * Meet hoeveel van de tekst op de channel-sites ECHT per branche verschilt.
 *
 * Aanleiding (05-09-2026): de live channels worden op precies de goede
 * commerciele zoekopdrachten gevonden -- "website laten maken rijschool" haalt
 * 488 vertoningen -- maar staan op gemiddelde positie 39, en op die term zelfs
 * op 58. Nul klikken. De pagina's zijn technisch in orde: 200, geindexeerd,
 * geen serverfouten, en de bak "gecrawld maar niet geindexeerd" staat op nul.
 *
 * Wat wel opviel: over 16 sites en 10 paginasoorten was maar 15% van de tekst
 * branche-specifiek. De rest is op elke site woord voor woord gelijk. Op 16
 * domeinen valt dat Google misschien niet op; bij de geplande ~200 wel, en dan
 * is het het patroon van een doorway-netwerk.
 *
 * Dit commando maakt dat meetbaar, zodat "meer branchetekst" een controleerbaar
 * doel wordt in plaats van een voornemen. Zie
 * docs/SEO-channels-eigenheid-2026-09-05.md voor de nulmeting en het advies.
 *
 *   php artisan channel:seo:eigenheid
 *   php artisan channel:seo:eigenheid --paden=home,/prijzen,/diensten
 *   php artisan channel:seo:eigenheid --limiet=6 --details=rijschool
 */
class ChannelSeoEigenheid extends Command
{
    protected $signature = 'channel:seo:eigenheid
        {--paden= : komma-gescheiden paden, standaard de tien vaste paginasoorten}
        {--limiet=0 : hooguit N live sites ophalen (0 = alle)}
        {--details= : channel-key waarvan de eigen blokken getoond worden}
        {--drempel=80 : vanaf welk percentage sites een blok als sjabloon telt}';

    protected $description = 'Meet per paginasoort hoeveel tekst branche-specifiek is';

    /** De vaste paginasoorten van een channel-site. */
    private const STANDAARD_PADEN = [
        '/', '/website', '/webshop', '/klantenportaal', '/automatisering',
        '/ai', '/diensten', '/prijzen', '/werkwijze', '/vergelijken',
    ];

    public function handle(): int
    {
        $paden = $this->option('paden')
            ? array_map([$this, 'normaliseerPad'], explode(',', (string) $this->option('paden')))
            : self::STANDAARD_PADEN;

        $sites = Site::where('status', 'live')
            ->whereNotNull('domain')
            ->orderBy('key')
            ->get(['key', 'domain']);

        if ($limiet = (int) $this->option('limiet')) {
            $sites = $sites->take($limiet);
        }
        if ($sites->count() < 3) {
            $this->error('Minder dan 3 live sites: een vergelijking zegt dan niets.');
            return self::FAILURE;
        }

        $this->info(sprintf('%d live sites, %d paginasoorten', $sites->count(), count($paden)));
        $this->newLine();

        $rijen = [];
        $totaalWoorden = 0;
        $totaalEigen = 0;

        foreach ($paden as $pad) {
            $teksten = [];
            foreach ($sites as $site) {
                $blokken = $this->blokken('https://' . $site->domain . $pad);
                if ($blokken) {
                    $teksten[$site->key] = $blokken;
                }
            }
            if (count($teksten) < 3) {
                $this->warn("  $pad — te weinig sites bereikbaar, overgeslagen");
                continue;
            }

            // Een blok telt als SJABLOON zodra het op >= drempel% van de sites staat.
            // Bewust niet "op alle sites": een enkele site die een blok mist zou de
            // hele meting anders optimistisch kleuren.
            $voorkomens = [];
            foreach ($teksten as $blokken) {
                foreach (array_unique($blokken) as $blok) {
                    $voorkomens[$blok] = ($voorkomens[$blok] ?? 0) + 1;
                }
            }
            $grens = max(2, (int) floor(count($teksten) * ((int) $this->option('drempel') / 100)));
            $sjabloon = array_keys(array_filter($voorkomens, fn ($n) => $n >= $grens));
            $sjabloon = array_flip($sjabloon);

            $somTot = $somSj = 0;
            foreach ($teksten as $blokken) {
                foreach ($blokken as $blok) {
                    $n = str_word_count($blok);
                    $somTot += $n;
                    if (isset($sjabloon[$blok])) $somSj += $n;
                }
            }
            $gemTot = (int) round($somTot / count($teksten));
            $gemSj  = (int) round($somSj / count($teksten));
            $eigen  = $gemTot > 0 ? ($gemTot - $gemSj) / $gemTot * 100 : 0;

            $rijen[] = [$pad, count($teksten), $gemTot, sprintf('%d%%', round(100 - $eigen)), sprintf('%d%%', round($eigen))];
            $totaalWoorden += $gemTot;
            $totaalEigen   += $gemTot * $eigen;

            if (($key = $this->option('details')) && isset($teksten[$key])) {
                $this->line("  eigen blokken op $pad ($key):");
                $eigenBlokken = array_filter($teksten[$key], fn ($b) => ! isset($sjabloon[$b]));
                usort($eigenBlokken, fn ($a, $b) => str_word_count($b) <=> str_word_count($a));
                foreach (array_slice($eigenBlokken, 0, 5) as $b) {
                    $this->line(sprintf('    [%3dw] %s', str_word_count($b), mb_substr($b, 0, 90)));
                }
            }
        }

        $this->table(['PAD', 'SITES', 'WOORDEN', 'SJABLOON', 'EIGEN'], $rijen);

        $gewogen = $totaalWoorden > 0 ? $totaalEigen / $totaalWoorden : 0;
        $this->newLine();
        $this->info(sprintf('Gewogen over alle paginasoorten: %.0f%% eigen tekst per branche', $gewogen));
        $this->line('  nulmeting 05-09-2026: 15%   ·   doel: boven de 40%');

        return self::SUCCESS;
    }

    /**
     * Maakt van losse invoer een bruikbaar pad.
     *
     * Git Bash op Windows verbouwt een kaal "/" argument tot het installatiepad
     * ("C:/Program Files/Git/"), waardoor de homepage stilletjes wegvalt uit de
     * meting. Vandaar: alles met een dubbele punt erin, en "home", en leeg,
     * betekent de homepage.
     */
    private function normaliseerPad(string $pad): string
    {
        $pad = trim($pad);
        if ($pad === '' || $pad === 'home' || str_contains($pad, ':')) return '/';
        return str_starts_with($pad, '/') ? $pad : '/' . $pad;
    }

    /**
     * Zichtbare tekst van een pagina, opgeknipt in blokken van >= 4 woorden.
     *
     * Blokken en niet losse woorden, want je verwijdert of herschrijft secties,
     * geen woordenschat. Korte fragmenten vallen af: die zijn vrijwel altijd
     * navigatie of knoppen en zouden het sjabloon-aandeel kunstmatig opdrijven.
     */
    private function blokken(string $url): array
    {
        $ctx = stream_context_create(['http' => [
            'timeout' => 25,
            'header'  => "User-Agent: betergeregeld-seo-meting/1.0\r\n",
        ], 'ssl' => ['verify_peer' => true]]);
        $html = @file_get_contents($url, false, $ctx);
        if ($html === false || $html === '') return [];

        $html = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html);
        $html = preg_replace('#<br\s*/?>|</(p|div|li|h[1-6]|td|section|span)>#i', "\n", $html);
        $html = preg_replace('#<[^>]+>#s', ' ', $html);
        $html = html_entity_decode((string) $html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $uit = [];
        foreach (explode("\n", (string) $html) as $regel) {
            $regel = trim((string) preg_replace('/\s+/u', ' ', $regel));
            if ($regel !== '' && str_word_count($regel) >= 4) $uit[] = $regel;
        }
        return $uit;
    }
}
