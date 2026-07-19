<?php

namespace App\Services\Ads;

/**
 * Hoog-niveau campagnebeheer bovenop GoogleAdsClient: campagnes aanmaken vanuit
 * een vast Search-template, en bestaande campagnes uitlezen, pauzeren/activeren
 * en van budget wijzigen. Eén bron van waarheid voor de CLI-commando's én het
 * admin-paneel.
 */
class GoogleAdsManager
{
    public function __construct(private GoogleAdsClient $client) {}

    /** Nederland + Nederlands (vaste Google-constanten). */
    private const GEO_NL  = 'geoTargetConstants/2528';
    private const LANG_NL = 'languageConstants/1010';

    /** Advertentiegroepen met hun phrase/exact-zoekwoorden. */
    public const AD_GROUPS = [
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

    /** Campagne-brede uitsluitingen (BROAD). */
    public const NEGATIVES = [
        'gratis website maken', 'zelf website maken', 'wordpress', 'wix', 'squarespace',
        'shopify', 'template', 'cursus', 'opleiding', 'vacature', 'baan', 'stage',
        'betekenis', 'download',
    ];

    /** RSA: max 15 koppen (≤30 tekens); kop 1 vast op positie 1. */
    public const HEADLINES = [
        'Gratis voorbeeld in 1 minuut', 'Zie nu jouw nieuwe website', 'Website laten maken?',
        'Direct een gratis voorbeeld', 'Jouw bedrijfswebsite online', 'Professioneel & betaalbaar',
        'Eerst zien, dan beslissen', 'Klaar terwijl je kijkt', 'Voor ondernemers & zzp',
        'Website in jouw eigen stijl', 'Gratis & vrijblijvend', 'Bekijk je voorbeeld gratis',
        'Geen technische kennis nodig', 'Meer klanten via je website', 'Start nu, gratis',
    ];

    /** RSA: max 4 beschrijvingen (≤90 tekens). */
    public const DESCRIPTIONS = [
        'Vul kort je gegevens in en zie in 1 minuut een gratis voorbeeld van jouw website.',
        'Professionele site voor ondernemers. Gratis voorbeeld, daarna pas beslissen.',
        "Geen gedoe: we maken 'm samen af in een korte, vrijblijvende videoafspraak.",
        'Bekijk vrijblijvend hoe jouw bedrijfswebsite eruit kan zien. Start nu gratis.',
    ];

    public function customerId(): string
    {
        return (string) config('google_ads.customer_id');
    }

    public function connected(): bool
    {
        return $this->client->connected();
    }

    /* ─────────────────────────── Aanmaken ─────────────────────────── */

    /**
     * Bouwt de atomische operatielijst voor een complete Search-campagne
     * (budget → campagne → geo/taal/uitsluitingen → advertentiegroepen →
     * zoekwoorden → responsive search ad) met tijdelijke resource-namen.
     */
    public function campaignOperations(string $name, string $url, int $budgetMic, int $ceilMic): array
    {
        $cid    = $this->customerId();
        $budget = "customers/{$cid}/campaignBudgets/-1";
        $camp   = "customers/{$cid}/campaigns/-2";
        $ops    = [];

        $ops[] = ['campaignBudgetOperation' => ['create' => [
            'resourceName'     => $budget,
            'name'             => $name . ' · budget',
            'amountMicros'     => (string) $budgetMic,
            'deliveryMethod'   => 'STANDARD',
            'explicitlyShared' => false,
        ]]];

        $ops[] = ['campaignOperation' => ['create' => [
            'resourceName'                   => $camp,
            'name'                           => $name,
            'status'                         => 'PAUSED',
            'advertisingChannelType'         => 'SEARCH',
            'campaignBudget'                 => $budget,
            'targetSpend'                    => ['cpcBidCeilingMicros' => (string) $ceilMic],
            // Verplicht sinds de EU-regel rond politieke advertenties.
            'containsEuPoliticalAdvertising' => 'DOES_NOT_CONTAIN_EU_POLITICAL_ADVERTISING',
            'networkSettings'                => [
                'targetGoogleSearch'         => true,
                'targetSearchNetwork'        => false,
                'targetContentNetwork'       => false,
                'targetPartnerSearchNetwork' => false,
            ],
        ]]];

        $ops[] = ['campaignCriterionOperation' => ['create' => ['campaign' => $camp, 'location' => ['geoTargetConstant' => self::GEO_NL]]]];
        $ops[] = ['campaignCriterionOperation' => ['create' => ['campaign' => $camp, 'language' => ['languageConstant' => self::LANG_NL]]]];

        foreach (self::NEGATIVES as $neg) {
            $ops[] = ['campaignCriterionOperation' => ['create' => [
                'campaign' => $camp, 'negative' => true, 'keyword' => ['text' => $neg, 'matchType' => 'BROAD'],
            ]]];
        }

        $agIndex = -3;
        foreach (self::AD_GROUPS as $agName => $keywords) {
            $ag = "customers/{$cid}/adGroups/{$agIndex}";
            $agIndex--;

            $ops[] = ['adGroupOperation' => ['create' => [
                'resourceName' => $ag, 'campaign' => $camp, 'name' => $agName, 'type' => 'SEARCH_STANDARD', 'status' => 'ENABLED',
            ]]];

            foreach ($keywords as [$text, $match]) {
                $ops[] = ['adGroupCriterionOperation' => ['create' => [
                    'adGroup' => $ag, 'status' => 'ENABLED', 'keyword' => ['text' => $text, 'matchType' => $match],
                ]]];
            }

            $ops[] = ['adGroupAdOperation' => ['create' => [
                'adGroup' => $ag, 'status' => 'ENABLED', 'ad' => [
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

    /** @return array{ok:bool,error:?string,campaign:?string} */
    public function createSearchCampaign(string $name, string $url, float $budgetEuro, float $maxCpcEuro): array
    {
        $ops = $this->campaignOperations($name, $url, $this->micros($budgetEuro), $this->micros($maxCpcEuro));
        $res = $this->client->mutate($ops);

        return [
            'ok'       => $res['ok'],
            'error'    => $res['error'],
            'campaign' => collect($res['results'])->pluck('campaignResult.resourceName')->filter()->first(),
        ];
    }

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

    /* ─────────────────────────── Uitlezen ─────────────────────────── */

    /**
     * Alle campagnes met status, dagbudget en (all-time) prestaties.
     *
     * @return array<int,array{id:string,name:string,status:string,budget:float,impressions:int,clicks:int,cost:float,conversions:float}>
     */
    public function listCampaigns(): array
    {
        $res = $this->client->search(
            'SELECT campaign.id, campaign.name, campaign.status, campaign_budget.amount_micros, '
            . 'metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions '
            . 'FROM campaign ORDER BY campaign.id'
        );

        if (! $res['ok']) {
            return [];
        }

        return array_map(function ($r) {
            $m = $r['metrics'] ?? [];

            return [
                'id'          => (string) data_get($r, 'campaign.id', ''),
                'name'        => (string) data_get($r, 'campaign.name', '—'),
                'status'      => (string) data_get($r, 'campaign.status', ''),
                'budget'      => ((int) data_get($r, 'campaignBudget.amountMicros', 0)) / 1_000_000,
                'impressions' => (int) ($m['impressions'] ?? 0),
                'clicks'      => (int) ($m['clicks'] ?? 0),
                'cost'        => ((int) ($m['costMicros'] ?? 0)) / 1_000_000,
                'conversions' => (float) ($m['conversions'] ?? 0),
            ];
        }, $res['results']);
    }

    /* ─────────────────────────── Beheren ─────────────────────────── */

    /** @return array{ok:bool,error:?string} */
    public function setStatus(string $id, string $status): array
    {
        $camp = 'customers/' . $this->customerId() . '/campaigns/' . $id;
        $res = $this->client->mutate([
            ['campaignOperation' => ['update' => ['resourceName' => $camp, 'status' => $status], 'updateMask' => 'status']],
        ]);

        return ['ok' => $res['ok'], 'error' => $res['error']];
    }

    /** @return array{ok:bool,error:?string} */
    public function setBudget(string $id, float $euro): array
    {
        $q = $this->client->search("SELECT campaign_budget.resource_name FROM campaign WHERE campaign.id = {$id}");
        $bud = data_get($q, 'results.0.campaignBudget.resourceName');
        if (! $q['ok'] || ! $bud) {
            return ['ok' => false, 'error' => $q['error'] ?: 'budget-resource niet gevonden'];
        }

        $res = $this->client->mutate([
            ['campaignBudgetOperation' => ['update' => ['resourceName' => $bud, 'amountMicros' => (string) $this->micros($euro)], 'updateMask' => 'amount_micros']],
        ]);

        return ['ok' => $res['ok'], 'error' => $res['error']];
    }

    private function micros(float $euro): int
    {
        return (int) round($euro * 1_000_000);
    }
}
