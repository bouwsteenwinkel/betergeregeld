<?php

namespace App\Services\ChannelSites;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Echte marktcijfers per branche, uit `channel_place_listings`.
 *
 * Aanleiding (05-09-2026): over 16 channel-sites en 10 paginasoorten was maar
 * 15% van de tekst branche-specifiek; op /prijzen zelfs 5%. De verkooppagina's
 * vertellen op elke site woordelijk hetzelfde verhaal, terwijl de plaatspagina's
 * -- die de lokale data wél gebruiken -- op 62% eigen tekst zitten en als enige
 * klikken opleveren. Zie docs/SEO-channels-eigenheid-2026-09-05.md.
 *
 * Het materiaal lag er al: 18.885 rijen met per branche en plaats de echte
 * aanbieders, hun waardering, hun aantal beoordelingen en of ze een site hebben.
 * Deze klasse dicht die per branche samen, zodat ook de verkooppagina's iets
 * kunnen zeggen dat alleen voor dát vak waar is.
 *
 * Twee dingen bewust:
 *  - **Alleen geaggregeerde cijfers, geen bedrijfsnamen.** De ruwe data bevat
 *    ruis (zie `PlaceBusinessFinder::schoon()`); een verkeerd getal in een
 *    gemiddelde over duizenden rijen valt weg, een verkeerde naam op de pagina
 *    niet.
 *  - **Faalt zacht.** Ontbreekt de tabel of de branche, dan komt er null uit en
 *    slaat de pagina het blok over. Nooit een verkooppagina laten vallen over
 *    een sierlijk cijfer.
 */
class BrancheMarktcijfers
{
    /** Cijfers veranderen alleen bij `channel:places-warm`, dus lang vasthouden. */
    private const CACHE_UREN = 24;

    private const CACHE_SLEUTEL = 'channel:marktcijfers:';

    /**
     * @return array<string,mixed>|null  null = geen bruikbare data voor deze branche
     */
    public function voor(string $brancheKey): ?array
    {
        if ($brancheKey === '') {
            return null;
        }

        /*
         * Let op de lege array als "niets gevonden", niet null: Laravel kan een
         * gecachte null niet onderscheiden van een cache-misser, waardoor een
         * branche zonder listings de hele berekening bij ELK paginaverzoek
         * opnieuw zou doen. Dat is precies het geval bij een net toegevoegde
         * site, en dat zijn er binnenkort ~200.
         */
        $cijfers = Cache::remember(
            self::CACHE_SLEUTEL . $brancheKey,
            now()->addHours(self::CACHE_UREN),
            fn () => $this->bereken($brancheKey) ?? []
        );

        return $cijfers ?: null;
    }

    /** Vult de cache vooruit, zodat geen bezoeker de ~1,3 s berekening betaalt. */
    public function warm(string $brancheKey): ?array
    {
        Cache::forget(self::CACHE_SLEUTEL . $brancheKey);

        return $this->voor($brancheKey);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function bereken(string $brancheKey): ?array
    {
        try {
            $rijen = DB::table('channel_place_listings')
                ->where('branche_key', $brancheKey)
                ->get(['place_slug', 'listings']);
        } catch (\Throwable $e) {
            return null;   // tabel bestaat niet → pagina werkt gewoon door
        }

        if ($rijen->count() < 50) {
            return null;   // te dun om iets over "de markt" te beweren
        }

        $filter = app(PlaceBusinessFinder::class);

        $aanbieders  = 0;
        $zonderSite  = 0;
        $waarderingen = [];
        $reviews     = [];
        $koplopers   = [];   // beoordelingen van de best gewaardeerde per plaats
        $perPlaats   = [];

        foreach ($rijen as $rij) {
            $listings = $filter->schoon((array) json_decode($rij->listings ?? '[]', true));
            if (! $listings) {
                continue;
            }

            $perPlaats[$rij->place_slug] = count($listings);
            $aanbieders += count($listings);

            $beste = null;
            foreach ($listings as $l) {
                if (empty($l['website'])) {
                    $zonderSite++;
                }
                if (! empty($l['rating'])) {
                    $waarderingen[] = (float) $l['rating'];
                }
                if (isset($l['reviews'])) {
                    $reviews[] = (int) $l['reviews'];
                    if ($beste === null || (int) $l['reviews'] > $beste) {
                        $beste = (int) $l['reviews'];
                    }
                }
            }
            if ($beste !== null) {
                $koplopers[] = $beste;
            }
        }

        if ($aanbieders < 200 || ! $perPlaats) {
            return null;
        }

        /*
         * Bewust GEEN "drukste plaats". De Places-zoekopdracht is afgekapt op
         * acht resultaten (channel_places.business.limit), dus de koploper is
         * altijd een willekeurige plaats die net het plafond raakt -- bij
         * rijschool kwam daar het dorp Aalst uit. Een ondergrens is wel eerlijk:
         * hoeveel plaatsen hebben er minstens vijf.
         */
        $vol = count(array_filter($perPlaats, fn ($n) => $n >= 5));

        return [
            'aanbieders'        => $aanbieders,
            'plaatsen'          => count($perPlaats),
            'per_plaats'        => round($aanbieders / count($perPlaats), 1),
            'plaatsen_vol'      => $vol,
            'plaatsen_vol_pct'  => (int) round($vol / count($perPlaats) * 100),
            'waardering'        => $waarderingen ? round(array_sum($waarderingen) / count($waarderingen), 2) : null,
            'reviews_mediaan'   => $this->mediaan($reviews),
            'koploper_mediaan'  => $this->mediaan($koplopers),
            'zonder_site'       => $zonderSite,
            'zonder_site_pct'   => (int) round($zonderSite / $aanbieders * 100),
        ];
    }

    /** @param array<int,int> $getallen */
    private function mediaan(array $getallen): ?int
    {
        if (! $getallen) {
            return null;
        }
        sort($getallen);
        return (int) $getallen[intdiv(count($getallen), 2)];
    }
}
