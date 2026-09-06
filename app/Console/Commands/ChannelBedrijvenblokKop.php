<?php

namespace App\Console\Commands;

use App\Models\Channel\Branche;
use Illuminate\Console\Command;

/**
 * Haalt de bedrijvengids-toon uit het "bedrijven in de regio"-blok.
 *
 * Aanleiding (06-09-2026). De plaatspagina's trokken 93% verkeerd publiek: van
 * de 7.123 vertoningen daar kwamen er 6.619 van de KLANTEN van de branche
 * ("loodgieter amstelveen", "badkamer 's gravendeel") en maar 504 van
 * ondernemers die een website zoeken. Zie de herziening in
 * docs/SEO-channels-eigenheid-2026-09-05.md.
 *
 * De oorzaak zat in de tekst, niet in Google. Op elke plaatspagina stond:
 *
 *     h2  "Loodgieters in Amstelveen"
 *         Jansen Loodgietersbedrijf · Dorpsstraat 1 · 4,7 (63)
 *         ...
 *
 * Dat IS een lokale bedrijvengids, en Google matchte daar volkomen terecht op.
 * De kop en de openingszin zijn de twee plekken die dat signaal afgeven; de
 * inhoud eronder is juist waardevol en blijft ongemoeid.
 *
 * Wat dit commando doet:
 *  - **verwijdert** `business.label` uit de branche-tokens, zodat de kop weer
 *    uit `config/channel_places.php` komt ("Je concurrentie in :city"). Alle
 *    204 branches hadden een eigen kop van de vorm ":trades in :city", en die
 *    droeg geen informatie die de config niet ook heeft. Zo staat de formulering
 *    weer op één plek in git in plaats van in 204 databaserijen.
 *  - **herschrijft de openingszin** van `business.intro` van "Dit zijn
 *    loodgieters in :city en omgeving" naar "Zo ziet het ondernemerslandschap
 *    in :city eruit". Die tweede vorm is niet verzonnen: twee branches
 *    gebruikten hem al. De rest van de zin blijft staan, want die spreekt de
 *    ondernemer al aan.
 *
 *   php artisan channel:bedrijvenblok:kop              (alleen melden)
 *   php artisan channel:bedrijvenblok:kop --herstel    (ook wegschrijven)
 */
class ChannelBedrijvenblokKop extends Command
{
    protected $signature = 'channel:bedrijvenblok:kop
        {--herstel : de wijzigingen ook echt wegschrijven}
        {--branche= : alleen deze branche-key}';

    protected $description = 'Haalt de bedrijvengids-toon uit de kop van het bedrijvenblok';

    /** De vorm die twee branches al gebruikten, en die niemands klant aantrekt. */
    private const NIEUWE_OPENING = 'Zo ziet het ondernemerslandschap in :city eruit';

    public function handle(): int
    {
        $herstel = (bool) $this->option('herstel');

        $query = Branche::orderBy('key');
        if ($key = $this->option('branche')) {
            $query->where('key', $key);
        }

        $rijen      = [];
        $onbekend   = [];
        $labelWeg   = 0;
        $introNieuw = 0;

        foreach ($query->get() as $branche) {
            $places   = (array) $branche->places;
            $business = (array) ($places['business'] ?? []);
            if (! $business) {
                continue;
            }

            $wijzig = false;
            $wat    = [];

            if (isset($business['label'])) {
                $wat[] = 'kop: "' . $business['label'] . '" -> config';
                unset($business['label']);
                $labelWeg++;
                $wijzig = true;
            }

            $intro = (string) ($business['intro'] ?? '');
            if ($intro !== '') {
                $nieuw = $this->herschrijfOpening($intro);
                if ($nieuw === null) {
                    // Onbekende vorm: melden, niet raden. Zes branches wijken af.
                    $onbekend[] = [$branche->key, mb_substr($intro, 0, 60)];
                } elseif ($nieuw !== $intro) {
                    $wat[] = 'intro herschreven';
                    $business['intro'] = $nieuw;
                    $introNieuw++;
                    $wijzig = true;
                }
            }

            if (! $wijzig) {
                continue;
            }

            $rijen[] = [$branche->key, implode(' | ', $wat)];

            if ($herstel) {
                $places['business'] = $business;
                $branche->places    = $places;
                $branche->save();
            }
        }

        if ($rijen) {
            $this->table(['BRANCHE', 'WIJZIGING'], array_slice($rijen, 0, 12));
            if (count($rijen) > 12) {
                $this->line(sprintf('  ... en nog %d.', count($rijen) - 12));
            }
        }

        $this->newLine();
        $this->line(sprintf('%d koppen naar de config, %d intro-openingen herschreven.', $labelWeg, $introNieuw));

        if ($onbekend) {
            $this->newLine();
            $this->warn('Deze intro-openingen herken ik niet — met de hand nakijken:');
            $this->table(['BRANCHE', 'BEGIN VAN DE INTRO'], $onbekend);
        }

        if ($rijen && ! $herstel) {
            $this->newLine();
            $this->line('Niets gewijzigd. Draai met --herstel om het weg te schrijven.');
        }
        if ($rijen && $herstel) {
            $this->newLine();
            $this->info('Weggeschreven. Daarna: php artisan cache:clear && php artisan view:clear');
        }

        return self::SUCCESS;
    }

    /**
     * Vervangt "Dit zijn <wat dan ook> in :city en omgeving" door de neutrale
     * opening. Geeft null terug als de zin niet met "Dit zijn" begint, zodat
     * een afwijkende tekst gemeld wordt in plaats van half herschreven.
     */
    private function herschrijfOpening(string $intro): ?string
    {
        if (str_starts_with($intro, self::NIEUWE_OPENING)) {
            return $intro;   // al goed
        }
        if (! str_starts_with($intro, 'Dit zijn ')) {
            return null;
        }

        // Alles tot en met "in :city en omgeving" vervangen; wat erachter staat
        // (het deel dat de ondernemer aanspreekt) blijft ongemoeid.
        $vervangen = preg_replace(
            '/^Dit zijn .*?in :city en omgeving/u',
            self::NIEUWE_OPENING,
            $intro,
            1
        );

        return $vervangen === $intro ? null : $vervangen;
    }
}
