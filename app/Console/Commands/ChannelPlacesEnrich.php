<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Haalt per plaats echte gegevens op bij de PDOK-locatieserver en berekent
 * daaruit afstand en buurplaatsen.
 *
 *   php artisan channel:places-enrich              (alleen wat nog ontbreekt)
 *   php artisan channel:places-enrich --limit=50   (in brokken, voor een trage host)
 *   php artisan channel:places-enrich --refresh    (ook al opgehaalde plaatsen opnieuw)
 *
 * PDOK is open data zonder sleutel of dagquota, maar we gaan er netjes mee om:
 * één verzoek per plaats, met een korte pauze ertussen, en het resultaat blijft
 * staan. Een plaats die PDOK niet kent wordt gemarkeerd (bron='onbekend') zodat
 * een volgende run 'm niet eindeloos opnieuw probeert.
 */
class ChannelPlacesEnrich extends Command
{
    protected $signature = 'channel:places-enrich
        {--limit=0 : Maximaal aantal plaatsen deze run (0 = alles)}
        {--refresh : Ook plaatsen die al opgehaald zijn opnieuw doen}
        {--afstand-only : Alleen afstand en buurplaatsen herberekenen}';

    protected $description = 'Vult channel_place_facts met gemeente, coördinaten, afstand en buurplaatsen';

    /** Vestigingsadres: Bussum. Afstanden zijn hemelsbreed vanaf dit punt. */
    private const BASIS_LAT = 52.2795;
    private const BASIS_LON = 5.1625;

    public function handle(): int
    {
        $plaatsen = (array) config('nl_places', []);
        if (! $plaatsen) {
            $this->error('config/nl_places.php is leeg.');

            return self::FAILURE;
        }

        if (! $this->option('afstand-only')) {
            $this->ophalen($plaatsen);
        }
        $this->afstandEnBuren();
        $this->inwoners();

        return self::SUCCESS;
    }

    /**
     * Inwonertal per gemeente uit CBS-opendata (StatLine 85984NED, Kerncijfers
     * wijken en buurten). Eén verzoek voor alle 342 gemeenten, dus geen reden om
     * dit per plaats te doen. Plaatsen binnen dezelfde gemeente krijgen hetzelfde
     * getal — dat is ook wat het is: een gemeentecijfer, en zo staat het op de
     * pagina.
     */
    private function inwoners(): void
    {
        $opties = [];
        if (class_exists(\Composer\CaBundle\CaBundle::class)) {
            $opties['verify'] = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
        }

        try {
            $res = Http::withOptions($opties)->timeout(60)->get('https://opendata.cbs.nl/ODataApi/odata/85984NED/TypedDataSet', [
                '$select' => 'WijkenEnBuurten,Gemeentenaam_1,AantalInwoners_5',
                '$filter' => "startswith(WijkenEnBuurten,'GM')",
            ]);
            if (! $res->ok()) {
                $this->warn('CBS gaf HTTP ' . $res->status() . ' — inwonertal overgeslagen.');

                return;
            }
        } catch (\Throwable $e) {
            $this->warn('CBS niet bereikbaar (' . $e->getMessage() . ') — inwonertal overgeslagen.');

            return;
        }

        // CBS levert de namen met spaties opgevuld; trimmen en op kleine letters
        // vergelijken, anders matcht "Bergen (NH.)  " nooit met onze gemeentenaam.
        $perGemeente = [];
        foreach ((array) data_get($res->json(), 'value', []) as $rij) {
            $naam = mb_strtolower(trim((string) ($rij['Gemeentenaam_1'] ?? '')));
            $aantal = $rij['AantalInwoners_5'] ?? null;
            if ($naam !== '' && $aantal !== null) $perGemeente[$naam] = (int) $aantal;
        }
        if (! $perGemeente) {
            $this->warn('CBS leverde geen gemeenten op — inwonertal overgeslagen.');

            return;
        }

        // CBS schrijft provincie-achtervoegsels mét punten ("Bergen (NH.)") waar
        // PDOK ze zonder punt geeft ("Bergen (NH)"), en soms laat PDOK het
        // achtervoegsel helemaal weg ("Beek" tegenover "Beek (L.)"). Daarom een
        // genormaliseerde sleutel plus, als die niets oplevert, een zoektocht op
        // naam + achtervoegsel — maar alleen als er precies één kandidaat is.
        $normaliseer = fn (string $s) => preg_replace('/[^a-z0-9()]/', '', mb_strtolower($s));
        $genormaliseerd = [];
        foreach ($perGemeente as $naam => $aantal) $genormaliseerd[$normaliseer($naam)] = $aantal;

        $gezet = 0; $onbekend = [];
        foreach (DB::table('channel_place_facts')->whereNotNull('gemeente')->get(['slug', 'gemeente']) as $r) {
            $ruw     = trim((string) $r->gemeente);
            $sleutel = $normaliseer($ruw);
            $aantal  = $genormaliseerd[$sleutel] ?? null;

            if ($aantal === null) {
                $kandidaten = [];
                foreach ($genormaliseerd as $cbs => $n) {
                    if (str_starts_with($cbs, $sleutel . '(')) $kandidaten[$cbs] = $n;
                }
                if (count($kandidaten) === 1) $aantal = reset($kandidaten);
            }

            if ($aantal === null) { $onbekend[mb_strtolower($ruw)] = true; continue; }
            DB::table('channel_place_facts')->where('slug', $r->slug)->update(['inwoners' => $aantal]);
            $gezet++;
        }

        $this->info('Inwonertal gezet voor ' . $gezet . ' plaatsen (' . count($perGemeente) . ' gemeenten uit CBS'
            . ($onbekend ? ', ' . count($onbekend) . ' gemeentenamen niet herkend' : '') . ').');
        if ($onbekend) {
            $this->line('  niet herkend: ' . implode(', ', array_slice(array_keys($onbekend), 0, 8)));
        }
    }

    /** Stap 1: gemeente + coördinaten per plaats bij PDOK ophalen. */
    private function ophalen(array $plaatsen): void
    {
        $bekend = DB::table('channel_place_facts')->pluck('bron', 'slug')->all();
        $limit  = (int) $this->option('limit');
        $gedaan = 0; $gevonden = 0; $gemist = 0;

        foreach ($plaatsen as $slug => $rij) {
            if (! $this->option('refresh') && isset($bekend[$slug])) continue;
            if ($limit > 0 && $gedaan >= $limit) break;

            $naam      = (string) ($rij['naam'] ?? $slug);
            $provincie = (string) ($rij['provincie'] ?? '');
            $doc       = $this->pdok($naam, $provincie);
            $gedaan++;

            $waarden = [
                'naam'         => $naam,
                'provincie'    => $doc['provincienaam'] ?? ($provincie ?: null),
                'gemeente'     => $doc['gemeentenaam'] ?? null,
                'lat'          => null,
                'lon'          => null,
                'bron'         => $doc ? 'pdok' : 'onbekend',
                'opgehaald_op' => now(),
            ];
            if ($doc && preg_match('/POINT\(([\d.\-]+)\s+([\d.\-]+)\)/', (string) ($doc['centroide_ll'] ?? ''), $m)) {
                $waarden['lon'] = round((float) $m[1], 6);
                $waarden['lat'] = round((float) $m[2], 6);
            }

            DB::table('channel_place_facts')->updateOrInsert(['slug' => $slug], $waarden);
            $doc ? $gevonden++ : $gemist++;

            if ($gedaan % 25 === 0) $this->line("  {$gedaan} verwerkt…");
            usleep(120000);   // ~8 verzoeken per seconde: vriendelijk voor een open dienst
        }

        $this->info("Opgehaald: {$gedaan} plaatsen ({$gevonden} gevonden, {$gemist} onbekend bij PDOK).");
    }

    /** Eén plaats opzoeken; provincie erbij zodat dubbele namen goed vallen. */
    private function pdok(string $naam, string $provincie): ?array
    {
        try {
            // Windows-PHP heeft vaak geen bruikbare CA-lijst; zonder deze regel
            // faalt elke aanroep met "cURL error 60: self-signed certificate in
            // certificate chain". Op een server met een goede CA-store verandert
            // dit niets.
            $opties = [];
            if (class_exists(\Composer\CaBundle\CaBundle::class)) {
                $opties['verify'] = \Composer\CaBundle\CaBundle::getSystemCaRootBundlePath();
            }

            $res = Http::withOptions($opties)->timeout(15)->get('https://api.pdok.nl/bzk/locatieserver/search/v3_1/free', [
                'q'    => trim($naam . ' ' . $provincie),
                'fq'   => 'type:woonplaats',
                'rows' => 1,
                'fl'   => 'weergavenaam,centroide_ll,gemeentenaam,provincienaam',
            ]);
            if (! $res->ok()) return null;
            $docs = (array) data_get($res->json(), 'response.docs', []);

            return $docs ? (array) $docs[0] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Stap 2: afstand tot de vestiging + de vijf dichtstbijzijnde plaatsen. */
    private function afstandEnBuren(): void
    {
        $rijen = DB::table('channel_place_facts')
            ->whereNotNull('lat')->whereNotNull('lon')
            ->get(['slug', 'naam', 'lat', 'lon'])->all();

        if (! $rijen) {
            $this->warn('Geen plaatsen met coördinaten — eerst ophalen.');

            return;
        }

        foreach ($rijen as $r) {
            $afstand = $this->km((float) $r->lat, (float) $r->lon, self::BASIS_LAT, self::BASIS_LON);

            $buren = [];
            foreach ($rijen as $ander) {
                if ($ander->slug === $r->slug) continue;
                $buren[$ander->slug] = $this->km((float) $r->lat, (float) $r->lon, (float) $ander->lat, (float) $ander->lon);
            }
            asort($buren);

            DB::table('channel_place_facts')->where('slug', $r->slug)->update([
                'afstand_km' => (int) round($afstand),
                'buren'      => implode(',', array_slice(array_keys($buren), 0, 5)),
            ]);
        }

        $this->info('Afstand en buurplaatsen bijgewerkt voor ' . count($rijen) . ' plaatsen.');
    }

    /** Hemelsbrede afstand in kilometers (haversine). */
    private function km(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
