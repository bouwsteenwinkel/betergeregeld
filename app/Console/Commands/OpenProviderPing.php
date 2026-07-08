<?php

namespace App\Console\Commands;

use Composer\CaBundle\CaBundle;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Diagnose: kan DEZE server de OpenProvider-API bereiken (IPv4 / IPv6 / auto)?
 * Draai op de VPS:  php artisan openprovider:ping
 * Gebruikt exact de PHP-curl-stack van de app, dus representatief voor de fout.
 */
class OpenProviderPing extends Command
{
    protected $signature = 'openprovider:ping';
    protected $description = 'Test de bereikbaarheid van de OpenProvider-API (IPv4/IPv6/auto) vanaf deze server.';

    public function handle(): int
    {
        $base = rtrim((string) config('openprovider.base_url', 'https://api.openprovider.eu'), '/');
        $this->info("OpenProvider-bereikbaarheid vanaf deze server → {$base}\n");

        $modes = [
            'IPv4 (geforceerd)' => \CURL_IPRESOLVE_V4,
            'IPv6 (geforceerd)' => \CURL_IPRESOLVE_V6,
            'auto'              => \CURL_IPRESOLVE_WHATEVER,
        ];

        foreach ($modes as $label => $mode) {
            $t0 = microtime(true);
            try {
                $r = Http::withOptions([
                        'verify' => CaBundle::getSystemCaRootBundlePath(),
                        'curl'   => [\CURLOPT_IPRESOLVE => $mode],
                    ])
                    ->timeout(20)
                    ->get($base . '/');
                $ms = round((microtime(true) - $t0) * 1000);
                $this->line(sprintf('  %-20s <info>OK</info>  HTTP %d  (%d ms)', $label, $r->status(), $ms));
            } catch (\Throwable $e) {
                $ms = round((microtime(true) - $t0) * 1000);
                $this->line(sprintf('  %-20s <error>FOUT</error>  %s  (%d ms)', $label, $e->getMessage(), $ms));
            }
        }

        $this->newLine();
        $this->line('Duiding:');
        $this->line('  • IPv4 hangt, IPv6 OK   → zet OPENPROVIDER_FORCE_IPV4=false (+ config:clear); whitelist evt. het VPS-IPv6.');
        $this->line('  • Beide hangen          → uitgaand 443 naar OpenProvider is geblokkeerd (Windows/hosting-firewall).');
        $this->line('  • Beide OK              → het is de OpenProvider IP-whitelist; voeg het VPS-IP toe.');

        return self::SUCCESS;
    }
}
