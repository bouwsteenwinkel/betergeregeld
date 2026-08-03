<?php

namespace App\Services\ChannelSites;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Levert echte lokale bedrijven (Google Places, new v1) voor de branche-gerichte
 * "bedrijven in de regio"-sectie op de /plaatsen-pagina's. Cache-first (DB-tabel
 * channel_place_listings) om API-kosten/quota te sparen; faalt altijd zacht.
 */
class PlaceBusinessFinder
{
    /** Cache-levensduur in dagen (bedrijven verplaatsen zelden). */
    private int $ttlDays = 180;

    /**
     * @param array<string,mixed> $search branche-config: query-template + limit + min_reviews
     * @return array<int,array<string,mixed>>
     */
    public function forPlace(string $brancheKey, string $placeSlug, string $city, string $region, array $search): array
    {
        $row = DB::table('channel_place_listings')
            ->where('branche_key', $brancheKey)
            ->where('place_slug', $placeSlug)
            ->first();

        if ($row && $row->fetched_at && Carbon::parse($row->fetched_at)->diffInDays(now()) < $this->ttlDays) {
            return (array) json_decode($row->listings ?? '[]', true);
        }

        $fresh = $this->fetch($city, $region, $search);

        if ($fresh !== null) {
            DB::table('channel_place_listings')->updateOrInsert(
                ['branche_key' => $brancheKey, 'place_slug' => $placeSlug],
                [
                    'listings'   => json_encode($fresh, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'fetched_at' => now(),
                    'updated_at' => now(),
                    'created_at' => $row->created_at ?? now(),
                ]
            );
            return $fresh;
        }

        // Fout/geen key → val terug op (evt. oude) cache i.p.v. de pagina te breken.
        return $row ? (array) json_decode($row->listings ?? '[]', true) : [];
    }

    /** Heeft deze (branche, plaats) al een cache-rij? (voor de warm-command). */
    public function isCached(string $brancheKey, string $placeSlug): bool
    {
        return DB::table('channel_place_listings')
            ->where('branche_key', $brancheKey)
            ->where('place_slug', $placeSlug)
            ->whereNotNull('fetched_at')
            ->exists();
    }

    /**
     * Slugs van plaatsen met minstens $min echte bedrijven in cache (één query,
     * géén API). Voor SEO-gating: alleen "sterke" plaatsen indexeren + in sitemap,
     * dunne (0-2 bedrijven) op noindex zodat er geen doorway-pagina's ontstaan.
     *
     * @return array<int,string>
     */
    public function indexableSlugs(string $brancheKey, int $min): array
    {
        $out = [];
        foreach (DB::table('channel_place_listings')->where('branche_key', $brancheKey)->get(['place_slug', 'listings']) as $row) {
            if (count((array) json_decode($row->listings ?? '[]', true)) >= $min) {
                $out[] = $row->place_slug;
            }
        }

        return array_values(array_filter($out, fn (string $slug) => $this->groteGenoegPlaats($slug)));
    }

    /**
     * Is de woonplaats groot genoeg om een eigen indexeerbare pagina te verdienen?
     *
     * Dit is de scherpe grens; `index_min_businesses` is dat niet. Google Places geeft
     * nooit meer dan negen resultaten terug, dus op 03-08-2026 zaten alle 984 "sterke"
     * plaatsen van bedrijfswebsite in de bak 5-9 en viel er niets te selecteren. Het
     * gevolg was 684 pagina's in "Gevonden — momenteel niet geïndexeerd": Google kende
     * ze, keek ernaar en vond ze de moeite niet.
     *
     * We meten daarom het aantal adressen in de woonplaats (BAG via PDOK,
     * channel_place_facts.adressen). Onbekend (NULL) laten we DOOR: een nog niet
     * verrijkte plaats mag niet stil uit de sitemap vallen — dat is hetzelfde vangnet
     * als bij de bedrijvencache. Zet `index_min_addresses` op 0 om de grens uit te
     * zetten. Eén query per verzoek, in het geheugen gehouden: dit wordt per plaats
     * aangeroepen tijdens het opbouwen van een sitemap van duizend URL's.
     */
    public function groteGenoegPlaats(string $slug): bool
    {
        $min = (int) config('channel_places.index_min_addresses', 0);
        if ($min <= 0) {
            return true;
        }

        if ($this->adressenPerPlaats === null) {
            $this->adressenPerPlaats = DB::table('channel_place_facts')
                ->pluck('adressen', 'slug')
                ->all();
        }

        // Niet in de tabel of nog niet gemeten → doorlaten (zie toelichting hierboven).
        if (! array_key_exists($slug, $this->adressenPerPlaats) || $this->adressenPerPlaats[$slug] === null) {
            return true;
        }

        return (int) $this->adressenPerPlaats[$slug] >= $min;
    }

    /** @var array<string,int|null>|null Adressen per plaats-slug; per request één query. */
    private ?array $adressenPerPlaats = null;

    /* ─────────────────────── Kosten-/quota-plafond ──────────────────────── */

    /** Feature aan én een credential aanwezig (service-account of API-key)? */
    public function enabled(): bool
    {
        return (bool) config('services.google_places.enabled', true) && $this->hasCredentials();
    }

    private function hasCredentials(): bool
    {
        return is_file(storage_path('app/google-api.json')) || (bool) config('services.google_places.key');
    }

    /**
     * Auth-headers voor Places (New): bij voorkeur OAuth via het service-account
     * (google-api.json), anders een API-key. Null = geen credential.
     *
     * @return array<string,string>|null
     */
    private function authHeaders(): ?array
    {
        $saPath = storage_path('app/google-api.json');
        if (is_file($saPath) && ($token = $this->serviceAccountToken($saPath))) {
            $sa = (array) json_decode((string) file_get_contents($saPath), true);
            return ['Authorization' => 'Bearer ' . $token, 'X-Goog-User-Project' => (string) ($sa['project_id'] ?? '')];
        }
        if ($key = config('services.google_places.key')) {
            return ['X-Goog-Api-Key' => (string) $key];
        }
        return null;
    }

    /** OAuth-token uit het service-account (JWT-grant), ~50 min gecachet. */
    private function serviceAccountToken(string $path): ?string
    {
        if ($cached = Cache::get('google_places_sa_token')) {
            return $cached;
        }
        $sa = (array) json_decode((string) file_get_contents($path), true);
        if (empty($sa['client_email']) || empty($sa['private_key']) || empty($sa['token_uri'])) {
            return null;
        }
        $b64 = fn ($d) => rtrim(strtr(base64_encode($d), '+/', '-_'), '=');
        $now = time();
        $jwt = $b64(json_encode(['alg' => 'RS256', 'typ' => 'JWT']))
            . '.' . $b64(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud'   => $sa['token_uri'],
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));
        if (! openssl_sign($jwt, $sig, $sa['private_key'], 'sha256WithRSAEncryption')) {
            return null;
        }
        $jwt .= '.' . $b64($sig);

        try {
            $resp = Http::asForm()->timeout(12)
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->post($sa['token_uri'], [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);
            $token = $resp->json('access_token');
        } catch (\Throwable $e) {
            Log::warning('Places service-account-token fout: ' . $e->getMessage());
            return null;
        }
        if ($token) {
            Cache::put('google_places_sa_token', $token, now()->addMinutes(50));
        }
        return $token ?: null;
    }

    /** Max. live calls per dag (0 = ongelimiteerd). */
    public function dailyCap(): int
    {
        return (int) config('services.google_places.daily_cap', 0);
    }

    /** Live calls die vandaag al gedaan zijn. */
    public function todayCalls(): int
    {
        return (int) Cache::get($this->counterKey(), 0);
    }

    /** Is het dagplafond bereikt? */
    public function capReached(): bool
    {
        $cap = $this->dailyCap();
        return $cap > 0 && $this->todayCalls() >= $cap;
    }

    private function counterKey(): string
    {
        return 'places_api_calls:' . now()->format('Y-m-d');
    }

    /** Telt één call bij de dag-teller (reset automatisch morgen). */
    private function countCall(): void
    {
        $key = $this->counterKey();
        Cache::put($key, $this->todayCalls() + 1, now()->endOfDay());
    }

    /**
     * Live Places-call. null = fout (cache niet overschrijven); array = resultaat
     * (mag leeg zijn = "geen bedrijven gevonden", wél cachen).
     *
     * @return array<int,array<string,mixed>>|null
     */
    private function fetch(string $city, string $region, array $search): ?array
    {
        if (! $this->enabled()) {
            return null;
        }
        // Kosten-/quota-plafond: niet meer live ophalen als de dag-cap bereikt is.
        if ($this->capReached()) {
            Log::info('Places dagplafond bereikt — live fetch overgeslagen', ['calls' => $this->todayCalls(), 'cap' => $this->dailyCap()]);
            return null;
        }

        $auth = $this->authHeaders();
        if ($auth === null) {
            return null;
        }
        $query      = str_replace([':city', ':region'], [$city, $region], (string) ($search['query'] ?? ':city'));
        $limit      = (int) ($search['limit'] ?? 8);
        $minReviews = (int) ($search['min_reviews'] ?? 0);

        $this->countCall();   // tel de poging (ook bij fout — beschermt tegen error-loops)

        try {
            $resp = Http::timeout(12)
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->withHeaders($auth + [
                    'Content-Type'     => 'application/json',
                    'X-Goog-FieldMask' => 'places.displayName,places.formattedAddress,places.rating,places.userRatingCount,places.websiteUri,places.googleMapsUri,places.primaryTypeDisplayName',
                ])
                ->post('https://places.googleapis.com/v1/places:searchText', [
                    'textQuery'      => $query,
                    'languageCode'   => 'nl',
                    'regionCode'     => 'NL',
                    'maxResultCount' => min(20, max($limit, 10)),
                ]);

            if (! $resp->successful()) {
                Log::warning('Places API niet ok', ['status' => $resp->status(), 'q' => $query]);
                return null;
            }
            $places = (array) $resp->json('places', []);
        } catch (\Throwable $e) {
            Log::warning('Places API fout: ' . $e->getMessage(), ['q' => $query]);
            return null;
        }

        $out = [];
        foreach ($places as $p) {
            $reviews = (int) ($p['userRatingCount'] ?? 0);
            if ($minReviews > 0 && $reviews < $minReviews) {
                continue;
            }
            $name = trim((string) ($p['displayName']['text'] ?? ''));
            if ($name === '') {
                continue;
            }
            $out[] = [
                'name'    => $name,
                'address' => (string) ($p['formattedAddress'] ?? ''),
                'rating'  => isset($p['rating']) ? (float) $p['rating'] : null,
                'reviews' => $reviews,
                'website' => $p['websiteUri'] ?? null,
                'maps'    => $p['googleMapsUri'] ?? null,
                'type'    => $p['primaryTypeDisplayName']['text'] ?? null,
            ];
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }
}
