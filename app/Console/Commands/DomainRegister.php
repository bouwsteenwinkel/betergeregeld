<?php

namespace App\Console\Commands;

use App\Services\OpenProvider\OpenProviderClient;
use Illuminate\Console\Command;
use Throwable;

/**
 * Registreert één of meer domeinen via OpenProvider en zet meteen de A-records
 * (apex + www) naar de VPS. Losstaand van channel-sites: géén Plesk/SSL/GSC —
 * puur registratie + DNS. Idempotent: een domein dat al in ons account staat,
 * krijgt alleen (opnieuw) de A-records verzekerd.
 *
 *   php artisan domain:register bswbricks.com bswbricks.nl
 *   php artisan domain:register voorbeeld.nl --ip=85.215.166.3
 *
 * NB: draai dit op de VPS — OpenProvider whitelist het VPS-IP en de credentials
 * staan alleen in de .env op de VPS.
 */
class DomainRegister extends Command
{
    protected $signature = 'domain:register {domains* : één of meer domeinen} {--ip= : doel-IP voor de A-records (standaard CHANNEL_TARGET_IP)}';
    protected $description = 'Registreer domein(en) bij OpenProvider en zet de A-records (@ + www) naar de VPS.';

    public function handle(OpenProviderClient $op): int
    {
        if (! $op->isConfigured()) {
            $this->error('OpenProvider is niet geconfigureerd — zet OPENPROVIDER_USERNAME/PASSWORD/OWNER_HANDLE in de .env (op de VPS).');
            return self::FAILURE;
        }

        $ip = trim((string) ($this->option('ip') ?: config('openprovider.target_ip')));
        if ($ip === '') {
            $this->error('Geen doel-IP: geef --ip=… of zet CHANNEL_TARGET_IP in de .env.');
            return self::FAILURE;
        }

        $domains = array_values(array_unique(array_filter(array_map('trim', (array) $this->argument('domains')))));
        $this->info(sprintf('Doel-IP voor de A-records: %s', $ip));
        $this->newLine();

        $allOk = true;

        foreach ($domains as $domain) {
            $this->line("<comment>{$domain}</comment>");
            try {
                $res = $op->registerWithDns($domain, $ip);
                if (! empty($res['already'])) {
                    $this->line('  registratie          <info>stond al in ons account</info> (A-records verzekerd)');
                } else {
                    $this->line(sprintf('  registratie          <info>OK</info> (OpenProvider domain-id %d)', $res['domain_id']));
                    $this->line('  DNS-zone             <info>A @ + www → ' . $ip . '</info>');
                }

                // Publieke verificatie. Vlak na een .nl-registratie propageert dit
                // doorgaans nog niet — dan is nog-niet-live verwacht, geen fout.
                $pub = $op->dnsResolvesToTarget($domain, $ip);
                $mark = $pub['ok'] ? '<info>live</info>' : '<comment>nog niet gepropageerd</comment>';
                $this->line(sprintf('  publieke DNS         %s (@=%s · www=%s)', $mark, $pub['apex'], $pub['www']));
            } catch (Throwable $e) {
                $allOk = false;
                $this->line('  <error>FOUT: ' . $e->getMessage() . '</error>');
            }
            $this->newLine();
        }

        $this->line($allOk
            ? '<info>✓ Klaar. DNS-propagatie kan bij .nl enkele minuten duren — verifieer later opnieuw.</info>'
            : '<comment>Eén of meer domeinen faalden — zie hierboven.</comment>');

        return $allOk ? self::SUCCESS : self::FAILURE;
    }
}
