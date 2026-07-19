<?php

namespace App\Console\Commands;

use App\Services\Ads\GoogleAdsClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Bouwt één complete Search-campagne voor een channel-landingspagina en zet 'm
 * (gepauzeerd) in het gekoppelde Google Ads-account via één atomische mutate:
 * budget → campagne → geo/taal/uitsluitingen → advertentiegroepen → zoekwoorden
 * → responsive search ad.
 *
 *   php artisan ads:create-campaign --dry-run      (toont alles, stuurt niets)
 *   php artisan ads:create-campaign                (maakt de campagne, GEPAUZEERD)
 *
 * Veiligheid: de campagne start ALTIJD op PAUSED; niets gaat live tot je 'm in de
 * Ads-UI (of straks het admin) inschakelt. --dry-run doet geen enkele API-call.
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

    /** Nederland + Nederlands (vaste Google-constanten). */
    private const GEO_NL  = 'geoTargetConstants/2528';
    private const LANG_NL = 'languageConstants/1010';

    /** Advertentiegroepen met hun phrase/exact-zoekwoorden. */
    private const AD_GROUPS = [
        'Website laten maken' => [
            ['website laten maken', 'PHRASE'],
            ['bedrijfswebsite laten maken', 'PHRASE'],
            ['website voor mijn bedrijf', 'PHRASE'],
            ['zzp website laten maken', 'PHRASE'],
            ['website laten maken', 'EXACT'],
        ],
        'Betaalbare website' => [
            ['goedkope website laten maken', 'PHRASE'],
            ['betaalbare website', 'PHRASE'],
            ['simpele website laten maken', 'PHRASE'],
            ['website voor ondernemers', 'PHRASE'],
        ],
    ];

    /** Campagne-brede uitsluitingen (BROAD), sparen budget op slechte zoekers. */
    private const NEGATIVES = [
        'gratis website maken', 'zelf website maken', 'wordpress', 'wix', 'squarespace',
        'shopify', 'template', 'cursus', 'opleiding', 'vacature', 'baan', 'stage',
        'betekenis', 'download',
    ];

    /** RSA: max 15 koppen (≤30 tekens); kop 1 vast op positie 1. */
    private const HEADLINES = [
        'Gratis voorbeeld in 1 minuut', 'Zie nu jouw nieuwe website', 'Website laten maken?',
        'Direct een gratis voorbeeld', 'Jouw bedrijfswebsite online', 'Professioneel & betaalbaar',
        'Eerst zien, dan beslissen', 'Klaar terwijl je kijkt', 'Voor ondernemers & zzp',
        'Website in jouw eigen stijl', 'Gratis & vrijblijvend', 'Bekijk je voorbeeld gratis',
        'Geen technische kennis nodig', 'Meer klanten via je website', 'Start nu, gratis',
    ];

    /** RSA: max 4 beschrijvingen (≤90 tekens). */
    private const DESCRIPTIONS = [
        'Vul kort je gegevens in en zie in 1 minuut een gratis voorbeeld van jouw website.',
        'Professionele site voor ondernemers. Gratis voorbeeld, daarna pas beslissen.',
        "Geen gedoe: we maken 'm samen af in een korte, vrijblijvende videoafspraak.",
        'Bekijk vrijblijvend hoe jouw bedrijfswebsite eruit kan zien. Start nu gratis.',
    ];

    public function handle(GoogleAdsClient $ads): int
    {
        $cid       = (string) config('google_ads.customer_id');
        $dryRun    = (bool) $this->option('dry-run');
        $channel   = (string) $this->option('channel');
        $url       = (string) $this->option('url');
        $budgetMic = (int) round(((float) $this->option('budget')) * 1_000_000);
        $ceilMic   = (int) round(((float) $this->option('max-cpc')) * 1_000_000);

        if ($cid === '') {
            $this->error('GOOGLE_ADS_CUSTOMER_ID ontbreekt. Zie ads:status.');

            return self::FAILURE;
        }

        $name = "{$channel} · Search · " . now()->format('Y-m-d');
        $ops  = $this->buildOperations($cid, $name, $url, $budgetMic, $ceilMic);

        $this->overzicht($name, $url, $budgetMic, $ceilMic, count($ops));

        if ($dryRun) {
            $path = 'ads/preview-' . now()->format('Ymd-His') . '.json';
            Storage::put($path, json_encode(['mutateOperations' => $ops], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            $this->line('');
            $this->info('DRY-RUN: er is niets naar Google gestuurd.');
            $this->line('  Volledige payload: ' . Storage::path($path));
            $this->line('  Live zetten (gepauzeerd): dezelfde opdracht zónder --dry-run.');

            return self::SUCCESS;
        }

        if (! $ads->connected()) {
            $this->error('Niet gekoppeld. Draai eerst ads:connect (zie ads:status).');

            return self::FAILURE;
        }

        $this->line('');
        $this->line('Campagne aanmaken (GEPAUZEERD) …');
        $res = $ads->mutate($ops);

        if (! $res['ok']) {
            $this->error('Mislukt: ' . $res['error']);
            $this->line('Tip: bij een developer-token op "Verkenner"-niveau kan aanmaken nog geweigerd worden; dan is Basic access nodig.');

            return self::FAILURE;
        }

        $campaignRes = collect($res['results'])->pluck('campaignResult.resourceName')->filter()->first();
        $this->info('Aangemaakt (PAUSED): ' . ($campaignRes ?: 'zie account'));
        $this->line('Zet de campagne pas op ACTIEF als je alles gecontroleerd hebt.');

        return self::SUCCESS;
    }

    /** Bouwt de atomische operatielijst met tijdelijke resource-namen (negatieve id's). */
    private function buildOperations(string $cid, string $name, string $url, int $budgetMic, int $ceilMic): array
    {
        $budget = "customers/{$cid}/campaignBudgets/-1";
        $camp   = "customers/{$cid}/campaigns/-2";
        $ops    = [];

        // 1. Budget
        $ops[] = ['campaignBudgetOperation' => ['create' => [
            'resourceName'    => $budget,
            'name'            => $name . ' · budget',
            'amountMicros'    => (string) $budgetMic,
            'deliveryMethod'  => 'STANDARD',
            'explicitlyShared' => false,
        ]]];

        // 2. Campagne: Search, gepauzeerd, alleen Google-zoeknetwerk (geen partners/display),
        //    Klikken maximaliseren met CPC-plafond.
        $ops[] = ['campaignOperation' => ['create' => [
            'resourceName'          => $camp,
            'name'                  => $name,
            'status'                => 'PAUSED',
            'advertisingChannelType' => 'SEARCH',
            'campaignBudget'        => $budget,
            'targetSpend'           => ['cpcBidCeilingMicros' => (string) $ceilMic],
            // Verplicht sinds de EU-regel rond politieke advertenties; een
            // bedrijfswebsite-campagne bevat dat niet.
            'containsEuPoliticalAdvertising' => 'DOES_NOT_CONTAIN_EU_POLITICAL_ADVERTISING',
            'networkSettings'       => [
                'targetGoogleSearch'         => true,
                'targetSearchNetwork'        => false,
                'targetContentNetwork'       => false,
                'targetPartnerSearchNetwork' => false,
            ],
        ]]];

        // 3. Geo (Nederland) + taal (Nederlands)
        $ops[] = ['campaignCriterionOperation' => ['create' => [
            'campaign' => $camp,
            'location' => ['geoTargetConstant' => self::GEO_NL],
        ]]];
        $ops[] = ['campaignCriterionOperation' => ['create' => [
            'campaign' => $camp,
            'language' => ['languageConstant' => self::LANG_NL],
        ]]];

        // 4. Campagne-brede uitsluitingen
        foreach (self::NEGATIVES as $neg) {
            $ops[] = ['campaignCriterionOperation' => ['create' => [
                'campaign' => $camp,
                'negative' => true,
                'keyword'  => ['text' => $neg, 'matchType' => 'BROAD'],
            ]]];
        }

        // 5. Advertentiegroepen + zoekwoorden + RSA
        $agIndex = -3;
        foreach (self::AD_GROUPS as $agName => $keywords) {
            $ag = "customers/{$cid}/adGroups/{$agIndex}";
            $agIndex--;

            $ops[] = ['adGroupOperation' => ['create' => [
                'resourceName' => $ag,
                'campaign'     => $camp,
                'name'         => $agName,
                'type'         => 'SEARCH_STANDARD',
                'status'       => 'ENABLED',
            ]]];

            foreach ($keywords as [$text, $match]) {
                $ops[] = ['adGroupCriterionOperation' => ['create' => [
                    'adGroup' => $ag,
                    'status'  => 'ENABLED',
                    'keyword' => ['text' => $text, 'matchType' => $match],
                ]]];
            }

            $ops[] = ['adGroupAdOperation' => ['create' => [
                'adGroup' => $ag,
                'status'  => 'ENABLED',
                'ad'      => [
                    'finalUrls'          => [$url],
                    'responsiveSearchAd' => [
                        'headlines'    => $this->headlines(),
                        'descriptions' => array_map(fn ($d) => ['text' => $d], self::DESCRIPTIONS),
                        'path1'        => 'voorbeeld',
                        'path2'        => 'gratis',
                    ],
                ],
            ]]];
        }

        return $ops;
    }

    /** Koppen; de eerste vast op positie 1 zodat de haak altijd zichtbaar is. */
    private function headlines(): array
    {
        $out = [];
        foreach (self::HEADLINES as $i => $text) {
            $h = ['text' => $text];
            if ($i === 0) {
                $h['pinnedField'] = 'HEADLINE_1';
            }
            $out[] = $h;
        }

        return $out;
    }

    private function overzicht(string $name, string $url, int $budgetMic, int $ceilMic, int $opCount): void
    {
        $kwCount = array_sum(array_map('count', self::AD_GROUPS));
        $this->line('');
        $this->line('  <fg=cyan>Nieuwe Search-campagne (concept)</>');
        $this->line('  ────────────────────────────────────────────');
        $this->line("  Naam            : {$name}");
        $this->line("  Eind-URL        : {$url}");
        $this->line('  Dagbudget       : € ' . number_format($budgetMic / 1_000_000, 2, ',', '.'));
        $this->line('  Biedstrategie   : Klikken maximaliseren, max. CPC € ' . number_format($ceilMic / 1_000_000, 2, ',', '.'));
        $this->line('  Doelgebied/taal : Nederland / Nederlands');
        $this->line('  Netwerk         : alleen Google-zoeknetwerk (geen partners/display)');
        $this->line('  Status          : PAUSED');
        $this->line('  Advertentiegroepen : ' . count(self::AD_GROUPS) . ' (' . implode(', ', array_keys(self::AD_GROUPS)) . ')');
        $this->line("  Zoekwoorden     : {$kwCount}   ·   Uitsluitingen: " . count(self::NEGATIVES));
        $this->line('  Advertentie     : 1 RSA per groep — ' . count(self::HEADLINES) . ' koppen, ' . count(self::DESCRIPTIONS) . ' beschrijvingen');
        $this->line("  API-operaties   : {$opCount}");
    }
}
