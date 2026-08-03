<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Ruimt verlopen sessiebestanden op, buiten het verzoek om.
 *
 * Laravel doet dit standaard tíjdens een verzoek: bij 2 van de 100 hits gooit de
 * file-driver de hele sessiemap door en verwijdert wat te oud is. Met een handvol
 * bestanden merk je daar niets van. Op deze site krijgt élke bezoeker en élke bot
 * een eigen sessiebestand — ook op een pagina die uit de cache komt — en blijven
 * die 30 dagen staan. Dan is het geen opruimklusje meer maar een mappenscan van
 * tienduizenden bestanden, en de bezoeker die dat lot trof stond te wachten.
 *
 * Gemeten aanleiding: ongeveer 1 op de 50 verzoeken aan een channel-site gaf geen
 * enkele byte terug binnen een minuut, terwijl dezelfde pagina er direct erna in
 * een seconde stond. Op /up — de enige route zonder sessie — gebeurde dat 0 van de
 * 90 keer, tegen 4 van de 90 op een gewone pagina.
 *
 * Deze taak doet hetzelfde werk op een vast moment. Zet daarbij session.lottery op
 * [0, 100], anders blijft de opruiming óók in het verzoek zitten.
 */
class SessionsPrune extends Command
{
    protected $signature = 'sessions:prune
                            {--dry-run : Alleen tellen, niets verwijderen}
                            {--max-leeftijd= : Overschrijf de levensduur in minuten}
                            {--anoniem-na= : Minuten waarna een sessie zónder login weg mag (0 = uit)}';

    protected $description = 'Verwijder verlopen sessiebestanden (buiten het verzoek om)';

    public function handle(): int
    {
        if ((string) config('session.driver') !== 'file') {
            $this->info('Sessiedriver is niet "file" — niets te doen.');

            return self::SUCCESS;
        }

        $map = (string) config('session.files');
        if ($map === '' || ! is_dir($map)) {
            $this->error('Sessiemap niet gevonden: ' . $map);

            return self::FAILURE;
        }

        $minuten = (int) ($this->option('max-leeftijd') ?: config('session.lifetime', 43200));
        $grens   = time() - ($minuten * 60);
        $droog   = (bool) $this->option('dry-run');

        // De sessieduur staat bewust op minimaal 30 dagen, zodat de Filament-admin
        // ingelogd blijft. Voor iemand die alleen een pagina bekeek is dat onzin: dat
        // bestand bevat niets dan een CSRF-token en houdt een maand lang plek bezet.
        // Zulke sessies mogen veel eerder weg — herkenbaar doordat er geen login in
        // staat. Wie wél is ingelogd houdt gewoon de volle termijn.
        $anoniemNa    = $this->option('anoniem-na') !== null
            ? (int) $this->option('anoniem-na')
            : (int) config('session.anoniem_opruimen_na', 1440);
        $anoniemGrens = $anoniemNa > 0 ? time() - ($anoniemNa * 60) : null;

        $gezien = 0; $verwijderd = 0; $fouten = 0; $anoniemWeg = 0;
        $start  = microtime(true);

        // Bewust geen glob() of scandir(): die lezen de hele map eerst in het
        // geheugen. Bij honderdduizenden bestanden is dat precies het probleem dat
        // we hier oplossen.
        $dir = @opendir($map);
        if ($dir === false) {
            $this->error('Kan de sessiemap niet openen.');

            return self::FAILURE;
        }

        while (($naam = readdir($dir)) !== false) {
            if ($naam === '.' || $naam === '..' || $naam === '.gitignore') {
                continue;
            }
            $pad = $map . DIRECTORY_SEPARATOR . $naam;
            if (! is_file($pad)) {
                continue;
            }
            $gezien++;

            $tijd = @filemtime($pad);
            if ($tijd === false) {
                continue;
            }

            $verlopen = $tijd < $grens;
            $anoniem  = false;

            // Alleen als hij nog niet verlopen is, maar wél oud genoeg, kijken we in
            // het bestand. Dat scheelt: op een verse map hoeft er bijna nooit iets
            // gelezen te worden, en na de eerste opruiming blijft dat zo.
            if (! $verlopen && $anoniemGrens !== null && $tijd < $anoniemGrens) {
                $anoniem = ! $this->heeftLogin($pad);
            }

            if (! $verlopen && ! $anoniem) {
                continue;
            }
            if ($droog) {
                $verwijderd++;
                if ($anoniem) $anoniemWeg++;
                continue;
            }
            // Een sessie die net tijdens het opruimen wordt aangeraakt kan al weg
            // zijn; dat is geen fout die iemand hoeft te zien.
            if (@unlink($pad)) {
                $verwijderd++;
                if ($anoniem) $anoniemWeg++;
            } elseif (is_file($pad)) {
                $fouten++;
            }
        }
        closedir($dir);

        $duur = round(microtime(true) - $start, 1);
        $this->info(sprintf('%s: %d van %d sessiebestanden %s (%ss).',
            $droog ? 'DRY-RUN' : 'Opgeruimd', $verwijderd, $gezien,
            $droog ? 'zouden weg kunnen' : 'verwijderd', $duur));
        if ($anoniemWeg > 0) {
            $this->line(sprintf('  waarvan %d zonder login (ouder dan %d minuten); wie ingelogd is houdt de volle termijn.',
                $anoniemWeg, $anoniemNa));
        }

        if ($fouten > 0) {
            $this->warn($fouten . ' bestanden konden niet verwijderd worden.');
        }

        // Een map die na het opruimen nog steeds enorm is, betekent dat de
        // levensduur te lang staat of dat er te veel sessies worden aangemaakt.
        // Dat wil je weten vóór het weer scheef gaat lopen.
        $over = $gezien - $verwijderd;
        if ($over > 50000) {
            Log::warning('sessions:prune — nog ' . $over . ' sessiebestanden over; overweeg een kortere session.lifetime.');
            $this->warn('Let op: er staan er nog ' . number_format($over, 0, ',', '.') . '. Kortere levensduur overwegen.');
        }

        return self::SUCCESS;
    }

    /**
     * Zit er een ingelogde gebruiker in dit sessiebestand?
     *
     * Laravel bewaart de ingelogde gebruiker onder een sleutel die begint met
     * `login_` (bijvoorbeeld `login_web_<hash>`). Die naam staat leesbaar in het
     * geserialiseerde bestand, dus een tekstcontrole volstaat — het bestand
     * uitpakken zou onnodig en risicovol zijn.
     *
     * We lezen maar een stuk van het begin: sessiebestanden zijn klein, en een
     * login-sleutel staat er altijd in de payload zelf. Kunnen we het bestand niet
     * lezen, dan zeggen we "wél ingelogd" — dan blijft hij staan. Bij twijfel iets
     * te lang bewaren is beter dan iemand uitloggen.
     */
    private function heeftLogin(string $pad): bool
    {
        $fh = @fopen($pad, 'rb');
        if ($fh === false) {
            return true;
        }
        $inhoud = (string) fread($fh, 8192);
        fclose($fh);

        return str_contains($inhoud, 'login_') || str_contains($inhoud, 'password_hash_');
    }
}
