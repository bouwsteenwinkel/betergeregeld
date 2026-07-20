<?php

namespace App\Services\Ads;

/**
 * Hoog-niveau campagnebeheer bovenop GoogleAdsClient. Campagnes worden gebouwd uit
 * een profiel (config/ads_campaigns.php) — één per bedrijf/aanbod — zodat de admin
 * én de CLI met hetzelfde recept een complete Search-campagne aanmaken. Daarnaast:
 * campagnes uitlezen (met prestaties + vertoningsaandeel), pauzeren/activeren, en
 * dagbudget/CPC-plafond wijzigen. Eén bron van waarheid voor CLI + admin.
 */
class GoogleAdsManager
{
    public function __construct(private GoogleAdsClient $client) {}

    /** Nederland + Nederlands (vaste Google-constanten). */
    private const GEO_NL  = 'geoTargetConstants/2528';
    private const LANG_NL = 'languageConstants/1010';

    public function customerId(): string
    {
        return (string) config('google_ads.customer_id');
    }

    public function connected(): bool
    {
        return $this->client->connected();
    }

    /* ─────────────────────────── Profielen ─────────────────────────── */

    /** @return array<string,array<string,mixed>> */
    public function profiles(): array
    {
        return (array) config('ads_campaigns', []);
    }

    /** @return array<string,mixed>|null */
    public function profile(string $key): ?array
    {
        return config('ads_campaigns.' . $key);
    }

    /* ─────────────────────────── Aanmaken ─────────────────────────── */

    /**
     * Bouwt de atomische operatielijst voor een complete Search-campagne uit een
     * profiel: budget → campagne → geo/taal/uitsluitingen → advertentiegroepen →
     * zoekwoorden → RSA → extensies (sitelinks/callouts/fragment/telefoon).
     *
     * @param array<string,mixed> $p
     */
    public function campaignOperations(array $p, string $name, int $budgetMic, int $ceilMic): array
    {
        $cid    = $this->customerId();
        $url    = (string) $p['final_url'];
        $root   = $this->domainRoot($url);
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

        foreach ($p['negatives'] ?? [] as $neg) {
            $ops[] = ['campaignCriterionOperation' => ['create' => ['campaign' => $camp, 'negative' => true, 'keyword' => ['text' => $neg, 'matchType' => 'BROAD']]]];
        }

        $paths   = array_values(array_filter((array) ($p['paths'] ?? []), fn ($x) => $x !== ''));
        $agIndex = -3;
        foreach ($p['ad_groups'] as $agName => $keywords) {
            $ag = "customers/{$cid}/adGroups/{$agIndex}";
            $agIndex--;

            $ops[] = ['adGroupOperation' => ['create' => ['resourceName' => $ag, 'campaign' => $camp, 'name' => $agName, 'type' => 'SEARCH_STANDARD', 'status' => 'ENABLED']]];

            foreach ($keywords as [$text, $match]) {
                $ops[] = ['adGroupCriterionOperation' => ['create' => ['adGroup' => $ag, 'status' => 'ENABLED', 'keyword' => ['text' => $text, 'matchType' => $match]]]];
            }

            // Koppen/descriptions kunnen per advertentiegroep worden overschreven
            // (ad_group_headlines/ad_group_descriptions op groepsnaam). Zonder
            // override valt de groep terug op de gedeelde profiel-set. Zo matchen de
            // koppen de zoekwoorden van die groep — cruciaal voor de Advertentiekwaliteit.
            $agHeadlines    = (array) ($p['ad_group_headlines'][$agName] ?? $p['headlines']);
            $agDescriptions = (array) ($p['ad_group_descriptions'][$agName] ?? $p['descriptions']);

            $rsa = ['finalUrls' => [$url], 'responsiveSearchAd' => [
                'headlines'    => $this->headlines($agHeadlines, (int) ($p['pin_h1'] ?? 0)),
                'descriptions' => array_map(fn ($d) => ['text' => $d], $agDescriptions),
            ]];
            if (isset($paths[0])) {
                $rsa['responsiveSearchAd']['path1'] = $paths[0];
            }
            if (isset($paths[1])) {
                $rsa['responsiveSearchAd']['path2'] = $paths[1];
            }
            $ops[] = ['adGroupAdOperation' => ['create' => ['adGroup' => $ag, 'status' => 'ENABLED', 'ad' => $rsa]]];
        }

        // Extensies (campagne-breed). Sitelink-URL's gaan t.o.v. het domein-root,
        // niet achter de landingspagina-URL (final_url kan zelf een pad hebben).
        $aid = -101;
        foreach ($p['sitelinks'] ?? [] as [$text, $spath, $d1, $d2]) {
            $asset = "customers/{$cid}/assets/{$aid}";
            $aid--;
            $ops[] = ['assetOperation' => ['create' => [
                'resourceName'  => $asset,
                'finalUrls'     => [$root . $spath],
                'sitelinkAsset' => ['linkText' => $text, 'description1' => $d1, 'description2' => $d2],
            ]]];
            $ops[] = ['campaignAssetOperation' => ['create' => ['campaign' => $camp, 'asset' => $asset, 'fieldType' => 'SITELINK']]];
        }

        foreach ($p['callouts'] ?? [] as $text) {
            $asset = "customers/{$cid}/assets/{$aid}";
            $aid--;
            $ops[] = ['assetOperation' => ['create' => ['resourceName' => $asset, 'calloutAsset' => ['calloutText' => $text]]]];
            $ops[] = ['campaignAssetOperation' => ['create' => ['campaign' => $camp, 'asset' => $asset, 'fieldType' => 'CALLOUT']]];
        }

        if (! empty($p['snippet']['values'])) {
            $asset = "customers/{$cid}/assets/{$aid}";
            $aid--;
            $ops[] = ['assetOperation' => ['create' => ['resourceName' => $asset, 'structuredSnippetAsset' => ['header' => $p['snippet']['header'], 'values' => $p['snippet']['values']]]]];
            $ops[] = ['campaignAssetOperation' => ['create' => ['campaign' => $camp, 'asset' => $asset, 'fieldType' => 'STRUCTURED_SNIPPET']]];
        }

        if (! empty($p['call_phone'])) {
            $asset = "customers/{$cid}/assets/{$aid}";
            $ops[] = ['assetOperation' => ['create' => ['resourceName' => $asset, 'callAsset' => ['countryCode' => 'NL', 'phoneNumber' => $p['call_phone']]]]];
            $ops[] = ['campaignAssetOperation' => ['create' => ['campaign' => $camp, 'asset' => $asset, 'fieldType' => 'CALL']]];
        }

        return $ops;
    }

    /**
     * Maakt een Search-campagne aan uit een profiel (gepauzeerd). Naam/budget/CPC
     * vallen terug op de profiel-defaults als ze niet meegegeven zijn.
     *
     * @return array{ok:bool,error:?string,campaign:?string}
     */
    public function createSearchCampaign(string $profileKey, ?string $name = null, ?float $budgetEuro = null, ?float $maxCpcEuro = null): array
    {
        return $this->sendCampaign($profileKey, $name, $budgetEuro, $maxCpcEuro, false);
    }

    /**
     * Toetst het profiel bij Google (validateOnly) zonder iets aan te maken —
     * zo verifieer je vooraf dat alle koppen/extensies/telefoon geldig zijn.
     *
     * @return array{ok:bool,error:?string,campaign:?string}
     */
    public function validateSearchCampaign(string $profileKey, ?string $name = null, ?float $budgetEuro = null, ?float $maxCpcEuro = null): array
    {
        return $this->sendCampaign($profileKey, $name, $budgetEuro, $maxCpcEuro, true);
    }

    /** @return array{ok:bool,error:?string,campaign:?string} */
    private function sendCampaign(string $profileKey, ?string $name, ?float $budgetEuro, ?float $maxCpcEuro, bool $validateOnly): array
    {
        $p = $this->profile($profileKey);
        if (! $p) {
            return ['ok' => false, 'error' => "Onbekend campagne-profiel: {$profileKey}", 'campaign' => null];
        }

        $name   = $name ?: (($p['name'] ?? $profileKey) . ' · Search');
        $budget = $budgetEuro ?? (float) ($p['budget'] ?? 25);
        $cpc    = $maxCpcEuro ?? (float) ($p['max_cpc'] ?? 1.5);

        $ops = $this->campaignOperations($p, $name, $this->micros($budget), $this->micros($cpc));
        $res = $this->client->mutate($ops, null, $validateOnly);

        return [
            'ok'       => $res['ok'],
            'error'    => $res['error'],
            'campaign' => collect($res['results'])->pluck('campaignResult.resourceName')->filter()->first(),
        ];
    }

    /**
     * Zet de RSA-koppen om naar API-vorm. Standaard $pinH1 = 0 = NIET pinnen:
     * dat geeft de beste Advertentiekwaliteit en laat Google alle koppen-
     * combinaties testen. Alleen bij een expliciete pin_h1 in het profiel worden
     * de eerste $pinH1 koppen op positie 1 gepind (zelden nodig).
     */
    private function headlines(array $texts, int $pinH1 = 0): array
    {
        $out = [];
        foreach (array_values($texts) as $i => $text) {
            $h = ['text' => $text];
            if ($i < $pinH1) {
                $h['pinnedField'] = 'HEADLINE_1';
            }
            $out[] = $h;
        }

        return $out;
    }

    private function domainRoot(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host   = parse_url($url, PHP_URL_HOST) ?: '';

        return $scheme . '://' . $host;
    }

    /* ─────────────────────────── Uitlezen ─────────────────────────── */

    /**
     * Alle campagnes met status, dagbudget, biedstrategie, CPC-plafond en (all-time)
     * prestaties + vertoningsaandeel-verlies (budget vs rang).
     *
     * @return array<int,array<string,mixed>>
     */
    public function listCampaigns(): array
    {
        $res = $this->client->search(
            'SELECT campaign.id, campaign.name, campaign.status, campaign_budget.amount_micros, '
            . 'campaign.bidding_strategy_type, campaign.target_spend.cpc_bid_ceiling_micros, '
            . 'metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, '
            . 'metrics.search_impression_share, metrics.search_budget_lost_impression_share, '
            . 'metrics.search_rank_lost_impression_share '
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
                'imprShare'   => (float) ($m['searchImpressionShare'] ?? 0),
                'lostBudget'  => (float) ($m['searchBudgetLostImpressionShare'] ?? 0),
                'lostRank'    => (float) ($m['searchRankLostImpressionShare'] ?? 0),
                'biddingType' => (string) data_get($r, 'campaign.biddingStrategyType', ''),
                'maxCpc'      => ((int) data_get($r, 'campaign.targetSpend.cpcBidCeilingMicros', 0)) / 1_000_000,
            ];
        }, $res['results']);
    }

    /* ─────────────────────────── Beheren ─────────────────────────── */

    /**
     * Zet de campagne op ENABLED of PAUSED. REMOVED kan NIET via een status-update
     * (Google: INVALID_ENUM_VALUE) — daarvoor is removeCampaign(); daar routen we
     * het naartoe zodat een verkeerde aanroep niet stilletjes faalt.
     *
     * @return array{ok:bool,error:?string}
     */
    public function setStatus(string $id, string $status): array
    {
        if ($status === 'REMOVED') {
            return $this->removeCampaign($id);
        }

        $camp = 'customers/' . $this->customerId() . '/campaigns/' . $id;
        $res = $this->client->mutate([
            ['campaignOperation' => ['update' => ['resourceName' => $camp, 'status' => $status], 'updateMask' => 'status']],
        ]);

        return ['ok' => $res['ok'], 'error' => $res['error']];
    }

    /**
     * Verwijdert een campagne definitief via de aparte remove-operatie
     * (status → REMOVED kan niet als update).
     *
     * @return array{ok:bool,error:?string}
     */
    public function removeCampaign(string $id): array
    {
        $camp = 'customers/' . $this->customerId() . '/campaigns/' . $id;
        $res = $this->client->mutate([
            ['campaignOperation' => ['remove' => $camp]],
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

    /**
     * Zet het CPC-plafond van een "Klikken maximaliseren"-campagne (TARGET_SPEND).
     *
     * @return array{ok:bool,error:?string}
     */
    public function setMaxCpc(string $id, float $euro): array
    {
        $camp = 'customers/' . $this->customerId() . '/campaigns/' . $id;
        $res = $this->client->mutate([
            ['campaignOperation' => [
                'update'     => ['resourceName' => $camp, 'targetSpend' => ['cpcBidCeilingMicros' => (string) $this->micros($euro)]],
                'updateMask' => 'target_spend.cpc_bid_ceiling_micros',
            ]],
        ]);

        return ['ok' => $res['ok'], 'error' => $res['error']];
    }

    private function micros(float $euro): int
    {
        return (int) round($euro * 1_000_000);
    }

    /* ─────────────────────── Fragmenten (structured snippets) ─────────────────────── */

    /**
     * Vervangt het structured-snippet-fragment van een bestaande campagne.
     * Snippet-assets zijn bij Google immutable, dus we ontkoppelen elk bestaand
     * STRUCTURED_SNIPPET-fragment en maken één nieuwe asset aan die we koppelen —
     * atomair in één mutate. Zo herstel je een afgekeurd fragment (bv. een
     * false-positive) zonder de hele campagne opnieuw op te bouwen.
     *
     * @param array<int,string> $values 3–10 waarden (Google-grens), elk ≤ 25 tekens
     * @return array{ok:bool,error:?string,removed:int}
     */
    public function syncStructuredSnippet(string $campaignId, string $header, array $values): array
    {
        $header = trim($header);
        $values = array_values(array_filter(array_map(fn ($v) => trim((string) $v), $values), fn ($v) => $v !== ''));

        if ($header === '') {
            return ['ok' => false, 'error' => 'Fragment-kop is verplicht.', 'removed' => 0];
        }
        if (count($values) < 3) {
            return ['ok' => false, 'error' => 'Een fragment vereist minstens 3 waarden.', 'removed' => 0];
        }

        $cid  = $this->customerId();
        $camp = "customers/{$cid}/campaigns/{$campaignId}";

        // Bestaande fragment-koppelingen opzoeken. Filteren op campaign_asset.campaign
        // (WHERE campaign.id mag niet op deze resource). Het field_type filteren we in
        // PHP i.p.v. in GAQL — dat scheelt gedoe met enum-quoting in de query.
        $q = $this->client->search(
            'SELECT campaign_asset.resource_name, campaign_asset.field_type '
            . "FROM campaign_asset WHERE campaign_asset.campaign = '{$camp}'"
        );
        if (! $q['ok']) {
            return ['ok' => false, 'error' => $q['error'], 'removed' => 0];
        }

        $ops     = [];
        $removed = 0;
        foreach ($q['results'] as $r) {
            if ((string) data_get($r, 'campaignAsset.fieldType') !== 'STRUCTURED_SNIPPET') {
                continue;
            }
            if ($link = data_get($r, 'campaignAsset.resourceName')) {
                $ops[] = ['campaignAssetOperation' => ['remove' => $link]];
                $removed++;
            }
        }

        // Nieuwe asset (temp resource -1) aanmaken en aan de campagne koppelen. De
        // create moet ná de removes staan zodat de temp-naam eerst gedefinieerd is.
        $asset = "customers/{$cid}/assets/-1";
        $ops[] = ['assetOperation' => ['create' => ['resourceName' => $asset, 'structuredSnippetAsset' => ['header' => $header, 'values' => array_slice($values, 0, 10)]]]];
        $ops[] = ['campaignAssetOperation' => ['create' => ['campaign' => $camp, 'asset' => $asset, 'fieldType' => 'STRUCTURED_SNIPPET']]];

        $res = $this->client->mutate($ops);

        return ['ok' => $res['ok'], 'error' => $res['error'], 'removed' => $removed];
    }

    /**
     * Als syncStructuredSnippet, maar leest kop + waarden uit een campagne-profiel
     * (config/ads_campaigns.php). Zo herstel je een afgekeurd fragment naar de
     * goedgekeurde config-versie — één bron van waarheid.
     *
     * @return array{ok:bool,error:?string,removed:int}
     */
    public function syncSnippetFromProfile(string $campaignId, string $profileKey): array
    {
        $p = $this->profile($profileKey);
        if (! $p || empty($p['snippet']['values'])) {
            return ['ok' => false, 'error' => "Profiel '{$profileKey}' heeft geen fragment.", 'removed' => 0];
        }

        return $this->syncStructuredSnippet(
            $campaignId,
            (string) ($p['snippet']['header'] ?? 'Types'),
            (array) $p['snippet']['values'],
        );
    }

    /* ─────────────────────── Conversies (offline import) ─────────────────────── */

    /** Resource-naam van een conversie-actie op naam, of null. */
    public function findConversionAction(string $name): ?string
    {
        $r = $this->client->search(
            "SELECT conversion_action.resource_name FROM conversion_action WHERE conversion_action.name = '" . addslashes($name) . "'"
        );

        return $r['ok'] ? data_get($r, 'results.0.conversionAction.resourceName') : null;
    }

    /**
     * Maakt (idempotent) een UPLOAD_CLICKS-conversie-actie voor server-side import,
     * bv. "Nieuw abonnement". Bestaat 'ie al op naam, dan geven we die terug.
     *
     * @return array{ok:bool,error:?string,resource:?string,created:bool}
     */
    public function ensureConversionAction(string $name, float $defaultValue = 10.0, string $category = 'SIGNUP'): array
    {
        if ($existing = $this->findConversionAction($name)) {
            return ['ok' => true, 'error' => null, 'resource' => $existing, 'created' => false];
        }

        $res = $this->client->mutate([
            ['conversionActionOperation' => ['create' => [
                'name'                           => $name,
                'type'                           => 'UPLOAD_CLICKS',
                'category'                       => $category,
                'status'                         => 'ENABLED',
                'primaryForGoal'                 => true,
                'countingType'                   => 'ONE_PER_CLICK',
                'clickThroughLookbackWindowDays' => 90,
                'valueSettings'                  => [
                    'defaultValue'          => $defaultValue,
                    'defaultCurrencyCode'   => 'EUR',
                    'alwaysUseDefaultValue' => false,
                ],
            ]]],
        ]);

        return [
            'ok'       => $res['ok'],
            'error'    => $res['error'],
            'resource' => collect($res['results'])->pluck('conversionActionResult.resourceName')->filter()->first(),
            'created'  => $res['ok'],
        ];
    }

    /**
     * Meldt één nieuw abonnement als conversie via de Data Manager API: koppelt de
     * gclid aan de "Nieuw abonnement"-conversie-actie met €-waarde. $rfc3339Time in
     * UTC, bv. 2026-07-19T12:03:00Z. $transactionId (bv. "membership-123") maakt de
     * import idempotent: dubbel insturen telt niet dubbel.
     *
     * @return array{ok:bool,error:?string,results:mixed}
     */
    public function ingestSubscriptionConversion(string $gclid, string $rfc3339Time, float $value = 10.0, ?string $transactionId = null, bool $consentGranted = true): array
    {
        $login = preg_replace('/\D/', '', (string) config('google_ads.login_customer_id'));
        $op    = preg_replace('/\D/', '', (string) config('google_ads.customer_id'));
        $caId  = preg_replace('/\D/', '', (string) config('google_ads.conversion_abo'));

        if ($caId === '') {
            return ['ok' => false, 'error' => 'google_ads.conversion_abo (conversie-actie-ID) ontbreekt.', 'results' => null];
        }

        $destinations = [[
            'reference'            => 'gads_abo',
            'loginAccount'         => ['accountId' => $login, 'accountType' => 'GOOGLE_ADS'],
            'operatingAccount'     => ['accountId' => $op, 'accountType' => 'GOOGLE_ADS'],
            'productDestinationId' => $caId,
        ]];

        $event = [
            'destinationReferences' => ['gads_abo'],
            'adIdentifiers'         => ['gclid' => $gclid],
            'eventTimestamp'        => $rfc3339Time,
            'currency'              => 'EUR',
            'conversionValue'       => $value,
            'eventSource'           => 'WEB',
        ];
        if ($transactionId !== null && $transactionId !== '') {
            $event['transactionId'] = $transactionId;
        }

        $consent = [
            'adUserData'        => $consentGranted ? 'CONSENT_GRANTED' : 'CONSENT_DENIED',
            'adPersonalization' => $consentGranted ? 'CONSENT_GRANTED' : 'CONSENT_DENIED',
        ];

        return $this->client->dataManagerIngest($destinations, [$event], $consent);
    }
}
