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

    /* ─────────────────────── Kosten-/quota-plafond ──────────────────────── */

    /** Feature aan én key aanwezig? */
    public function enabled(): bool
    {
        return (bool) config('services.google_places.enabled', true) && (bool) config('services.google_places.key');
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

        $key        = config('services.google_places.key');
        $query      = str_replace([':city', ':region'], [$city, $region], (string) ($search['query'] ?? ':city'));
        $limit      = (int) ($search['limit'] ?? 8);
        $minReviews = (int) ($search['min_reviews'] ?? 0);

        $this->countCall();   // tel de poging (ook bij fout — beschermt tegen error-loops)

        try {
            $resp = Http::timeout(12)
                ->withOptions(['verify' => CaBundle::getSystemCaRootBundlePath()])
                ->withHeaders([
                    'Content-Type'     => 'application/json',
                    'X-Goog-Api-Key'   => $key,
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
