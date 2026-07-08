<?php

namespace App\Console\Commands;

use App\Services\Plesk\PleskClient;
use Illuminate\Console\Command;

/**
 * Diagnose: kan de app de Plesk-API bereiken en authenticeren?
 * Draai op de VPS:  php artisan plesk:ping
 */
class PleskPing extends Command
{
    protected $signature = 'plesk:ping';
    protected $description = 'Test de verbinding + auth met de Plesk-API (read-only).';

    public function handle(PleskClient $plesk): int
    {
        $this->info('Plesk-API — verbinding & auth (read-only)');
        $this->line('  base_url : ' . config('plesk.base_url'));
        $this->line('  auth     : ' . (config('plesk.api_key') ? 'secret key' : 'admin basic-auth'));
        $this->newLine();

        $r = $plesk->ping();
        if ($r['ok']) {
            $this->line("  <info>OK</info>  HTTP {$r['status']}  hostname={$r['hostname']}  versie={$r['version']}");
            $this->newLine();

            // Exacte CLI-commandonamen ophalen — zoek de alias-utility.
            $ids = $plesk->listCommandIds();
            if ($ids) {
                $this->line('  Beschikbare CLI-commando\'s: ' . count($ids));

                $alias = array_values(array_filter($ids, fn ($id) => str_contains(strtolower($id), 'alias')));
                $this->line('  Met "alias":  ' . ($alias ? '<info>' . implode(', ', $alias) . '</info>' : '<comment>geen</comment>'));
                if ($alias) {
                    $preferred = array_values(array_filter($alias, fn ($id) => str_contains(strtolower($id), 'dom'))) ?: $alias;
                    $this->line('  → PLESK_ALIAS_UTILITY=' . $preferred[0]);
                }

                // Domein-/site-aanmaak-commando's (voor de aparte-domein-aanpak).
                $domain = array_values(array_filter($ids, fn ($id) => (bool) preg_match('/^(site|domain|subdomain|webspace)/i', $id)
                    || (bool) preg_match('/(site|domain|hosting|www|vhost)/i', $id)));
                $this->line('  Domein/site: ' . ($domain ? '<info>' . implode(', ', $domain) . '</info>' : '<comment>geen</comment>'));

                // SSL/Let's Encrypt-commando's.
                $ssl = array_values(array_filter($ids, fn ($id) => (bool) preg_match('/ssl|cert|encrypt|sslit|secure/i', $id)));
                $this->line('  SSL/cert:    ' . ($ssl ? '<info>' . implode(', ', $ssl) . '</info>' : '<comment>geen</comment>'));
            } else {
                $this->line('  <comment>Kon de CLI-commandolijst niet ophalen (/api/v2/cli/commands).</comment>');
            }

            $this->newLine();
            $this->line('Verbinding werkt. Volgende: `php artisan plesk:alias <site>` op een live domein.');
            return self::SUCCESS;
        }

        $this->line("  <error>FOUT</error>  HTTP {$r['status']} — {$r['error']}");
        $this->newLine();
        $this->line('Check: PLESK_BASE_URL/PLESK_API_KEY in .env, en of de key aan het juiste IP gebonden is.');
        return self::FAILURE;
    }
}
