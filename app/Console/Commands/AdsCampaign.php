<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;

/**
 * Beheer van bestaande campagnes: lijst tonen, pauzeren, activeren en het
 * dagbudget wijzigen. Deelt de logica met het admin-paneel via GoogleAdsManager.
 *
 *   php artisan ads:campaign --list
 *   php artisan ads:campaign 24046650881 --budget=25
 *   php artisan ads:campaign 24046650881 --pause
 *   php artisan ads:campaign 24046650881 --enable   (LET OP: zet 'm live)
 */
class AdsCampaign extends Command
{
    protected $signature = 'ads:campaign
        {id? : campagne-ID (leeg = lijst tonen)}
        {--list : toon alle campagnes met ID, status en budget}
        {--pause : zet de campagne op gepauzeerd}
        {--enable : zet de campagne op actief (gaat live en geeft geld uit)}
        {--budget= : nieuw dagbudget in euro}';

    protected $description = 'Beheer campagnes: lijst tonen, pauzeren, activeren of het dagbudget wijzigen.';

    public function handle(GoogleAdsManager $mgr): int
    {
        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Zie ads:status.');

            return self::FAILURE;
        }

        $id = $this->argument('id');

        if (! $id || $this->option('list')) {
            return $this->lijst($mgr);
        }

        if ($this->option('enable') && ! $this->confirm('Campagne ' . $id . ' ACTIVEREN? Hij gaat dan live en geeft budget uit.', false)) {
            $this->line('Afgebroken.');

            return self::SUCCESS;
        }

        $did = false;

        foreach ([
            $this->option('pause') ? fn () => $mgr->setStatus($id, 'PAUSED') : null,
            $this->option('enable') ? fn () => $mgr->setStatus($id, 'ENABLED') : null,
            $this->option('budget') !== null ? fn () => $mgr->setBudget($id, (float) str_replace(',', '.', (string) $this->option('budget'))) : null,
        ] as $actie) {
            if (! $actie) {
                continue;
            }
            $res = $actie();
            if (! $res['ok']) {
                $this->error('Mislukt: ' . $res['error']);

                return self::FAILURE;
            }
            $did = true;
        }

        if (! $did) {
            $this->warn('Niets te doen. Geef --pause, --enable of --budget=<euro> mee.');

            return self::SUCCESS;
        }

        $this->info('Bijgewerkt: campagne ' . $id . '.');

        return $this->lijst($mgr);
    }

    private function lijst(GoogleAdsManager $mgr): int
    {
        $rows = [];
        foreach ($mgr->listCampaigns() as $c) {
            $rows[] = [
                $c['id'],
                mb_strimwidth($c['name'], 0, 40, '…'),
                ucfirst(strtolower($c['status'])),
                '€ ' . number_format($c['budget'], 2, ',', '.'),
            ];
        }

        $this->table(['ID', 'Naam', 'Status', 'Dagbudget'], $rows);

        return self::SUCCESS;
    }
}
