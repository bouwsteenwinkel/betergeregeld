<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Kijkt in de sessiebestanden: wie laat ze achter, en waar?
 *
 * Toen bleek dat er 342.220 sessiebestanden stonden was de eerste vraag terecht
 * "wat vóór sessies zijn dat dan?". Dat is te beantwoorden zonder te gokken: elk
 * bestand bevat het laatst bezochte adres (`_previous.url`) en, als iemand is
 * ingelogd, een sleutel die met `login_` begint.
 *
 * Alleen lezen, niets verwijderen. Een steekproef volstaat — bij honderdduizenden
 * bestanden hoeft niemand ze allemaal open te doen om het patroon te zien.
 *
 *     php artisan sessions:inspect
 *     php artisan sessions:inspect --steekproef=5000
 */
class SessionsInspect extends Command
{
    protected $signature = 'sessions:inspect {--steekproef=2000 : Hoeveel bestanden bekijken}';

    protected $description = 'Analyseer de sessiebestanden: herkomst, inhoud en ouderdom';

    public function handle(): int
    {
        $map = (string) config('session.files');
        if (! is_dir($map)) {
            $this->error('Sessiemap niet gevonden: ' . $map);

            return self::FAILURE;
        }

        $max = max(1, (int) $this->option('steekproef'));

        $totaal = 0; $bekeken = 0;
        $metLogin = 0; $leeg = 0; $metBeacon = 0; $metHerkomst = 0; $onleesbaar = 0;
        $perHost = []; $perSoort = []; $perDag = [];

        $dir = opendir($map);
        while (($naam = readdir($dir)) !== false) {
            if ($naam === '.' || $naam === '..' || $naam === '.gitignore') {
                continue;
            }
            $pad = $map . DIRECTORY_SEPARATOR . $naam;
            if (! is_file($pad)) {
                continue;
            }
            $totaal++;
            // De eerste N zijn hier een prima steekproef: een sessiebestand heet
            // naar zijn willekeurige token, en de map staat op naam gesorteerd.
            // De volgorde zegt dus niets over tijd of inhoud. Daarna alleen nog
            // doortellen, zodat het totaal wél klopt.
            if ($bekeken >= $max) {
                continue;
            }

            $tijd = @filemtime($pad) ?: 0;
            $perDag[date('Y-m-d', $tijd)] = ($perDag[date('Y-m-d', $tijd)] ?? 0) + 1;

            $inhoud = @file_get_contents($pad, false, null, 0, 4096);
            if ($inhoud === false) { $onleesbaar++; continue; }
            $bekeken++;

            if (str_contains($inhoud, 'login_') || str_contains($inhoud, 'password_hash_')) $metLogin++;
            if (str_contains($inhoud, 'bg_ev_ref'))    $metBeacon++;
            if (str_contains($inhoud, 'bg_attributie') || str_contains($inhoud, 'gclid')) $metHerkomst++;

            // Alleen een token en een vorige-pagina = iemand die niets deed.
            $sleutels = [];
            if (preg_match_all('/"([a-z0-9_]+)"\s*:/i', $inhoud, $m)) $sleutels = array_unique($m[1]);
            $inhoudelijk = array_diff($sleutels, ['_token', '_previous', 'url', 'route', '_flash', 'old', 'new']);
            if (! $inhoudelijk) $leeg++;

            if (preg_match('~"url"\s*:\s*"([^"]+)"~', $inhoud, $m)) {
                $url  = str_replace('\\/', '/', $m[1]);
                $host = parse_url($url, PHP_URL_HOST) ?: 'onbekend';
                $pad2 = parse_url($url, PHP_URL_PATH) ?: '/';
                $perHost[$host] = ($perHost[$host] ?? 0) + 1;

                $eerste = explode('/', trim($pad2, '/'))[0] ?? '';
                $soort = match (true) {
                    $eerste === ''            => 'homepage',
                    $eerste === 'plaatsen'    => 'plaatspagina',
                    $eerste === 'blog'        => 'blog',
                    $eerste === '_site'       => 'preview',
                    default                   => '/' . $eerste,
                };
                $perSoort[$soort] = ($perSoort[$soort] ?? 0) + 1;
            }
        }
        closedir($dir);

        $this->info(sprintf("\n%s sessiebestanden in totaal, %s bekeken.\n",
            number_format($totaal, 0, ',', '.'), number_format($bekeken, 0, ',', '.')));

        if ($bekeken === 0) {
            $this->warn('Geen bestanden om te bekijken.');

            return self::SUCCESS;
        }

        $pct = fn (int $n) => sprintf('%5.1f%%', 100 * $n / $bekeken);
        $this->line('  Ingelogd                 ' . str_pad((string) $metLogin, 8, ' ', STR_PAD_LEFT) . '  ' . $pct($metLogin));
        $this->line('  Alleen bekeken, niets gedaan ' . str_pad((string) $leeg, 4, ' ', STR_PAD_LEFT) . '  ' . $pct($leeg));
        $this->line('  Funnel-beacon (echte klik)   ' . str_pad((string) $metBeacon, 4, ' ', STR_PAD_LEFT) . '  ' . $pct($metBeacon));
        $this->line('  Advertentie-herkomst         ' . str_pad((string) $metHerkomst, 4, ' ', STR_PAD_LEFT) . '  ' . $pct($metHerkomst));
        if ($onleesbaar) $this->line('  Onleesbaar                   ' . $onleesbaar);

        $this->info("\n  Laatst bezochte pagina:");
        arsort($perSoort);
        foreach (array_slice($perSoort, 0, 8, true) as $soort => $n) {
            $this->line(sprintf('    %-24s %6d  %s', $soort, $n, $pct($n)));
        }

        $this->info("\n  Per site:");
        arsort($perHost);
        foreach (array_slice($perHost, 0, 10, true) as $host => $n) {
            $this->line(sprintf('    %-38s %6d  %s', $host, $n, $pct($n)));
        }

        $this->info("\n  Aangemaakt op (laatste dagen):");
        krsort($perDag);
        foreach (array_slice($perDag, 0, 7, true) as $dag => $n) {
            $this->line(sprintf('    %-12s %6d', $dag, $n));
        }
        $this->newLine();

        return self::SUCCESS;
    }
}
