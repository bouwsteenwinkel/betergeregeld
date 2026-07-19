<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Bouwt één complete Search-campagne voor een channel-landingspagina en zet 'm
 * (gepauzeerd) in het gekoppelde Google Ads-account. Het template zelf staat in
 * GoogleAdsManager, gedeeld met het admin-paneel.
 *
 *   php artisan ads:create-campaign --dry-run      (toont alles, stuurt niets)
 *   php artisan ads:create-campaign                (maakt de campagne, GEPAUZEERD)
 */
class AdsCreateCampaign extends Command
{
    protected $signature = 'ads:create-campaign
        {--channel=bedrijfswebsite : channel-key, voor de naamgeving}
        {--url=https://jouw-bedrijfswebsite.nl : eind-URL van de advertenties}
        {--budget=20 : dagbudget in euro}
        {--max-cpc=1.5 : max. CPC-plafond in euro (Klikken maximaliseren)}
        {--dry-run : toon de campagne zonder iets naar Google te sturen}';

    protected $description = 'Bouwt een gepauzeerde Search-campagne (template) voor een channel; --dry-run stuurt niets.';

    public function handle(GoogleAdsManager $mgr): int
    {
        if ($mgr->customerId() === '') {
            $this->error('GOOGLE_ADS_CUSTOMER_ID ontbreekt. Zie ads:status.');

            return self::FAILURE;
        }

        $dryRun     = (bool) $this->option('dry-run');
        $channel    = (string) $this->option('channel');
        $url        = (string) $this->option('url');
        $budgetEuro = (float) $this->option('budget');
        $cpcEuro    = (float) $this->option('max-cpc');
        $name       = "{$channel} · Search · " . now()->format('Y-m-d');

        $ops = $mgr->campaignOperations($name, $url, (int) round($budgetEuro * 1_000_000), (int) round($cpcEuro * 1_000_000));
        $this->overzicht($name, $url, $budgetEuro, $cpcEuro, count($ops));

        if ($dryRun) {
            $path = 'ads/preview-' . now()->format('Ymd-His') . '.json';
            Storage::put($path, json_encode(['mutateOperations' => $ops], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('');
            $this->info('DRY-RUN: er is niets naar Google gestuurd.');
            $this->line('  Volledige payload: ' . Storage::path($path));
            $this->line('  Live zetten (gepauzeerd): dezelfde opdracht zónder --dry-run.');

            return self::SUCCESS;
        }

        if (! $mgr->connected()) {
            $this->error('Niet gekoppeld. Draai eerst ads:connect (zie ads:status).');

            return self::FAILURE;
        }

        $this->line('');
        $this->line('Campagne aanmaken (GEPAUZEERD) …');
        $res = $mgr->createSearchCampaign($name, $url, $budgetEuro, $cpcEuro);

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);

            return self::FAILURE;
        }

        $this->info('Aangemaakt (PAUSED): ' . ($res['campaign'] ?: 'zie account'));
        $this->line('Zet de campagne pas op ACTIEF als je alles gecontroleerd hebt.');

        return self::SUCCESS;
    }

    private function overzicht(string $name, string $url, float $budgetEuro, float $cpcEuro, int $opCount): void
    {
        $kwCount = array_sum(array_map('count', GoogleAdsManager::AD_GROUPS));
        $this->line('');
        $this->line('  <fg=cyan>Nieuwe Search-campagne (concept)</>');
        $this->line('  ────────────────────────────────────────────');
        $this->line("  Naam            : {$name}");
        $this->line("  Eind-URL        : {$url}");
        $this->line('  Dagbudget       : € ' . number_format($budgetEuro, 2, ',', '.'));
        $this->line('  Biedstrategie   : Klikken maximaliseren, max. CPC € ' . number_format($cpcEuro, 2, ',', '.'));
        $this->line('  Doelgebied/taal : Nederland / Nederlands');
        $this->line('  Netwerk         : alleen Google-zoeknetwerk (geen partners/display)');
        $this->line('  Status          : PAUSED');
        $this->line('  Advertentiegroepen : ' . count(GoogleAdsManager::AD_GROUPS) . ' (' . implode(', ', array_keys(GoogleAdsManager::AD_GROUPS)) . ')');
        $this->line("  Zoekwoorden     : {$kwCount}   ·   Uitsluitingen: " . count(GoogleAdsManager::NEGATIVES));
        $this->line('  Advertentie     : 1 RSA per groep — ' . count(GoogleAdsManager::HEADLINES) . ' koppen, ' . count(GoogleAdsManager::DESCRIPTIONS) . ' beschrijvingen');
        $this->line("  API-operaties   : {$opCount}");
    }
}
