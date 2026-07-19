<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsClient;
use Illuminate\Console\Command;

/**
 * Beheer van bestaande campagnes in het gekoppelde account: pauzeren, activeren
 * en het dagbudget wijzigen. Zonder ID (of met --list) toont het alle campagnes
 * met hun ID en budget.
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

    public function handle(GoogleAdsClient $ads): int
    {
        if (! $ads->connected()) {
            $this->error('Niet gekoppeld. Zie ads:status.');

            return self::FAILURE;
        }

        $cid = (string) config('google_ads.customer_id');
        $id  = $this->argument('id');

        if (! $id || $this->option('list')) {
            return $this->lijst($ads);
        }

        $camp = "customers/{$cid}/campaigns/{$id}";
        $ops  = [];

        if ($this->option('pause')) {
            $ops[] = ['campaignOperation' => ['update' => ['resourceName' => $camp, 'status' => 'PAUSED'], 'updateMask' => 'status']];
        }
        if ($this->option('enable')) {
            $ops[] = ['campaignOperation' => ['update' => ['resourceName' => $camp, 'status' => 'ENABLED'], 'updateMask' => 'status']];
        }
        if ($this->option('budget') !== null) {
            $mic = (int) round(((float) $this->option('budget')) * 1_000_000);
            $q = $ads->search("SELECT campaign_budget.resource_name FROM campaign WHERE campaign.id = {$id}");
            $bud = data_get($q, 'results.0.campaignBudget.resourceName');
            if (! $q['ok'] || ! $bud) {
                $this->error('Budget-resource niet gevonden voor campagne ' . $id . ($q['error'] ? ' (' . $q['error'] . ')' : ''));

                return self::FAILURE;
            }
            $ops[] = ['campaignBudgetOperation' => ['update' => ['resourceName' => $bud, 'amountMicros' => (string) $mic], 'updateMask' => 'amount_micros']];
        }

        if (! $ops) {
            $this->warn('Niets te doen. Geef --pause, --enable of --budget=<euro> mee.');

            return self::SUCCESS;
        }

        if ($this->option('enable') && ! $this->confirm('Campagne ' . $id . ' ACTIVEREN? Hij gaat dan live en geeft budget uit.', false)) {
            $this->line('Afgebroken.');

            return self::SUCCESS;
        }

        $res = $ads->mutate($ops);
        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info('Bijgewerkt: campagne ' . $id . '.');

        return $this->lijst($ads);
    }

    private function lijst(GoogleAdsClient $ads): int
    {
        $q = $ads->search('SELECT campaign.id, campaign.name, campaign.status, campaign_budget.amount_micros FROM campaign ORDER BY campaign.id');
        if (! $q['ok']) {
            $this->error('Ophalen mislukt: ' . $q['error']);

            return self::FAILURE;
        }

        $rows = [];
        foreach ($q['results'] as $r) {
            $c = $r['campaign'] ?? [];
            $mic = (int) data_get($r, 'campaignBudget.amountMicros', 0);
            $rows[] = [
                $c['id'] ?? '—',
                mb_strimwidth((string) ($c['name'] ?? '—'), 0, 40, '…'),
                ucfirst(strtolower((string) ($c['status'] ?? ''))),
                '€ ' . number_format($mic / 1_000_000, 2, ',', '.'),
            ];
        }

        $this->table(['ID', 'Naam', 'Status', 'Dagbudget'], $rows);

        return self::SUCCESS;
    }
}
