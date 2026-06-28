<?php

namespace App\Support\Geo;

use Composer\CaBundle\CaBundle;
use Illuminate\Support\Facades\Http;

/**
 * Afstand (hemelsbreed) van een NL-postcode tot Bussum, voor de afspraak-keuze:
 * <= 50 km → bezoek mogelijk, anders Google Meet.
 *
 * Geocodet via de gratis PDOK Locatieserver (geen key). Best-effort: faalt 'ie,
 * dan null → de flow valt veilig terug op Google Meet.
 */
class GeoBussum
{
    // Bussum (centrum) — referentiepunt.
    private const LAT = 52.2766;
    private const LNG = 5.1623;

    public const RADIUS_KM = 50;

    private const ENDPOINT = 'https://api.pdok.nl/bzk/locatieserver/search/v3_1/free';

    /** Afstand in km tot Bussum, of null als onbekend/ongeldig. */
    public static function distanceKm(?string $postcode): ?int
    {
        $pc = strtoupper(preg_replace('/\s+/', '', (string) $postcode));
        if (! preg_match('/^[1-9][0-9]{3}[A-Z]{0,2}$/', $pc)) {
            return null;
        }

        $coords = self::geocode($pc) ?? self::geocode(substr($pc, 0, 4));
        if (! $coords) {
            return null;
        }

        return (int) round(self::haversine(self::LAT, self::LNG, $coords[0], $coords[1]));
    }

    /** Binnen de bezoekstraal? null = onbekend. */
    public static function withinRadius(?int $km): ?bool
    {
        return $km === null ? null : ($km <= self::RADIUS_KM);
    }

    /** @return array{0:float,1:float}|null [lat,lng] */
    private static function geocode(string $q): ?array
    {
        try {
            $opts = [];
            if (class_exists(CaBundle::class)) {
                $opts['verify'] = CaBundle::getSystemCaRootBundlePath(); // Windows-dev CA-fix
            }
            $resp = Http::withOptions($opts)->timeout(6)->get(self::ENDPOINT, [
                'q'    => $q,
                'fq'   => 'type:postcode',
                'rows' => 1,
            ]);
            $ll = (string) $resp->json('response.docs.0.centroide_ll', '');
            if (preg_match('/POINT\(([-0-9.]+)\s+([-0-9.]+)\)/', $ll, $m)) {
                return [(float) $m[2], (float) $m[1]]; // POINT(lng lat) → [lat,lng]
            }
        } catch (\Throwable $e) {
            // best-effort
        }
        return null;
    }

    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
