<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Maakt een gepauzeerde Search-campagne aan uit een profiel (config/ads_campaigns.php).
 *
 *   php artisan ads:create-campaign --profile=bouwsteenwinkel --dry-run
 *   php artisan ads:create-campaign --profile=bedrijfswebsite
 */
class AdsCreateCampaign extends Command
{
    protected $signature = 'ads:create-campaign
        {--profile=bedrijfswebsite : campagne-profiel (config/ads_campaigns.php)}
        {--budget= : dagbudget in euro (leeg = profiel-standaard)}
        {--max-cpc= : max. CPC-plafond in euro (leeg = profiel-standaard)}
        {--dry-run : toon de campagne zonder iets naar Google te sturen}
        {--validate : toets de campagne bij Google (validateOnly) zonder aan te maken}';

    protected $description = 'Bouwt een gepauzeerde Search-campagne uit een profiel; --dry-run stuurt niets.';

    public function handle(GoogleAdsManager $mgr): int
    {
        if ($mgr->customerId() === '') {
            $this->error('GOOGLE_ADS_CUSTOMER_ID ontbreekt. Zie ads:status.');

            return self::FAILURE;
        }

        $profileKey = (string) $this->option('profile');
        $p = $mgr->profile($profileKey);
        if (! $p) {
            $this->error("Onbekend profiel '{$profileKey}'. Beschikbaar: " . implode(', ', array_keys($mgr->profiles())) . '.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $budget = $this->option('budget') !== null ? (float) $this->option('budget') : (float) ($p['budget'] ?? 25);
        $cpc    = $this->option('max-cpc') !== null ? (float) $this->option('max-cpc') : (float) ($p['max_cpc'] ?? 1.5);
        $name   = ($p['name'] ?? $profileKey) . ' · Search · ' . now()->format('Y-m-d');

        $ops = $mgr->campaignOperations($p, $name, (int) round($budget * 1_000_000), (int) round($cpc * 1_000_000));
        $this->overzicht($p, $name, $budget, $cpc, count($ops));

        if ($dryRun) {
            $path = 'ads/preview-' . now()->format('Ymd-His') . '.json';
            Storage::put($path, json_encode(['mutateOperations' => $ops], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('');
            $this->info('DRY-RUN: er is niets naar Google gestuurd.');
            $this->line('  Volledige payload: ' . Storage::path($path));

            return self::SUCCESS;
        }

        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Draai eerst ads:connect (zie ads:status).');

            return self::FAILURE;
        }

        if ((bool) $this->option('validate')) {
            $this->line('');
            $this->line('Toetsen bij Google (validateOnly, er wordt niets aangemaakt) …');
            $res = $mgr->validateSearchCampaign($profileKey, $name, $budget, $cpc);

            if (! $res['ok']) {
                $this->error('Afgekeurd: ' . $res['error']);

                return self::FAILURE;
            }

            $this->info('Geldig: Google keurt deze campagne goed. Er is niets aangemaakt.');

            return self::SUCCESS;
        }

        $this->line('');
        $this->line('Campagne aanmaken (GEPAUZEERD) …');
        $res = $mgr->createSearchCampaign($profileKey, $name, $budget, $cpc);

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info('Aangemaakt (PAUSED): ' . ($res['campaign'] ?: 'zie account'));
        $this->line('Zet de campagne pas op ACTIEF als je alles gecontroleerd hebt.');

        return self::SUCCESS;
    }

    /** @param array<string,mixed> $p */
    private function overzicht(array $p, string $name, float $budget, float $cpc, int $opCount): void
    {
        $kwCount = array_sum(array_map('count', (array) $p['ad_groups']));
        $ext     = count($p['sitelinks'] ?? []) . ' sitelinks, ' . count($p['callouts'] ?? []) . ' highlights'
            . (! empty($p['snippet']['values']) ? ', 1 fragment' : '') . (! empty($p['call_phone']) ? ', 1 bel-asset' : '');

        $this->line('');
        $this->line('  <fg=cyan>Nieuwe Search-campagne (concept)</>');
        $this->line('  ────────────────────────────────────────────');
        $this->line("  Naam            : {$name}");
        $this->line('  Eind-URL        : ' . $p['final_url']);
        $this->line('  Dagbudget       : € ' . number_format($budget, 2, ',', '.'));
        $this->line('  Biedstrategie   : Klikken maximaliseren, max. CPC € ' . number_format($cpc, 2, ',', '.'));
        $this->line('  Doelgebied/taal : Nederland / Nederlands');
        $this->line('  Advertentiegroepen : ' . count((array) $p['ad_groups']) . ' (' . implode(', ', array_keys((array) $p['ad_groups'])) . ')');
        $this->line("  Zoekwoorden     : {$kwCount}   ·   Uitsluitingen: " . count($p['negatives'] ?? []));
        $this->line('  Advertentie     : 1 RSA per groep — ' . count((array) $p['headlines']) . ' koppen, ' . count((array) $p['descriptions']) . ' beschrijvingen');
        $this->line("  Extensies       : {$ext}");
        $this->line("  API-operaties   : {$opCount}");
    }
}
