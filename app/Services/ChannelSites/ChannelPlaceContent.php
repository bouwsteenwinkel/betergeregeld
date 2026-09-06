<?php

namespace App\Services\ChannelSites;

/**
 * Content-variatie-engine voor de /plaatsen-pagina's van ÁLLE niche-sites.
 *
 * De variantsets staan generiek in config/channel_places.php (trade-aware via
 * tokens). Per branche komen alleen kleine tokens uit branche.places. Per plaats
 * wordt deterministisch (slug-hash + slot-offset) één variant per blok gekozen en
 * verweven met branche- + plaats-tokens → unieke tekst per pagina, geen duplicate.
 */
class ChannelPlaceContent
{
    /**
     * @param array<string,mixed> $tokens branche.places (trade/trades/niche/niches/service, ...)
     * @param array<string,mixed> $place  ['slug','naam','provincie','context']
     * De $seed onderscheidt sites onderling: zonder seed hasht de variantkeuze
     * alleen op de plaatsslug, waardoor álle channel-sites voor dezelfde plaats
     * exact dezelfde variant kozen. De pagina's varieerden dus wél per plaats,
     * maar niet per site — 98% identieke tekst tussen de kanalen (23-08-2026).
     *
     * @return array<string,mixed>  ingevulde blokken (meta_title, h1, hero_lead, intro, wie, trust, cta, cta_title, faq)
     */
    public function assemble(array $tokens, array $place, string $seed = '', ?string $brancheKey = null): array
    {
        $cfg      = (array) config('channel_places', []);
        $defaults = (array) ($cfg['defaults'] ?? []);
        $variants = (array) ($cfg['variants'] ?? []);
        $t        = array_merge($defaults, array_filter($tokens, fn ($v) => is_scalar($v) && $v !== ''));

        $city    = (string) ($place['naam'] ?? '');
        $region  = (string) ($place['provincie'] ?? 'Nederland');
        $context = $place['context'] ?? null;
        $full    = $context ? "{$city} ({$context})" : $city;
        $slug    = (string) ($place['slug'] ?? '');

        // Branche-tokens uit één bron (App\Support\ChannelTokens), plaats-tokens
        // erbij. Volgorde: langste eerst (:trades vóór :trade), zie die klasse.
        $map = \App\Support\ChannelTokens::map($t, $brancheKey) + [
            ':aanspreek' => $this->aanspreek($t, $brancheKey, $city),
            ':region'    => $region,
            ':full'      => $full,
            ':city'      => $city,
        ];
        $repl = fn ($str) => strtr((string) $str, $map);

        $out    = ['_city' => $city, '_region' => $region, '_full' => $full];
        $offset = 0;
        foreach ($variants as $slot => $set) {
            $offset++;
            if ($slot === 'faq') {
                $out['faq'] = $this->faq($this->faqSet((array) $set, $seed . $slug, $offset), $repl);
                continue;
            }
            $out[$slot] = $this->zinBeginKapitaal($repl($this->pick((array) $set, $seed . $slug, $offset)));
        }
        return $out;
    }

    /**
     * De openingszin die de ONDERNEMER aanspreekt, niet zijn klant.
     *
     * Aanleiding (06-09-2026): de plaatspagina's trokken 93% verkeerd publiek.
     * De titels begonnen met branche + plaats ("Meer aanvragen voor loodgieters
     * in Amstelveen"), en dat is woordelijk waar iemand met een lekkage op
     * zoekt. Een pagina die opent met "Ben jij de loodgieter uit Amstelveen
     * die..." kiest meteen partij: de consument leest die zin en klikt weg.
     *
     * Twee vormen, want het Nederlands laat hier geen enkele toe. Is `trade`
     * een beroep, dan kan het rechtstreeks ("Ben jij de loodgieter uit
     * Baarn"); is het een zaak, dan moet de eigenaar ertussen ("Ben jij de
     * eigenaar van een rijschool in Baarn"). Beide lopen door in dezelfde
     * bijzin, zodat één variant-tekst voor alle 204 branches volstaat.
     *
     * De scheidslijn is `channel_places.beroep_branches`, via
     * ChannelTokens::isBeroep(). Zo hangen de aanspreekvorm en het zaakwoord
     * aan dezelfde lijst en kunnen ze niet uit elkaar lopen.
     *
     * @param array<string,mixed> $t
     */
    private function aanspreek(array $t, ?string $brancheKey, string $city): string
    {
        if (\App\Support\ChannelTokens::isBeroep($brancheKey)) {
            return 'Ben jij de ' . (string) ($t['trade'] ?? 'ondernemer') . " uit {$city}";
        }

        // Zaak-vorm bewust op :zaak en niet op :trade, zodat een dienstwoord met
        // een eigen zaakwoord ("asbestverwijdering" -> "bedrijf") hier ook goed
        // uitkomt in plaats van "de eigenaar van een asbestverwijdering".
        $zaak = \App\Support\ChannelTokens::map($t, $brancheKey)[':zaak'] ?? 'bedrijf';

        return "Ben jij de eigenaar van een {$zaak} in {$city}";
    }

    /**
     * Hoofdletter aan het begin van ELKE zin. De varianten beginnen vaak met een
     * token (:trade, :service) en die zijn per definitie kleingeschreven, dus
     * zonder dit levert de engine koppen als "badkamerbedrijf in Utrecht?" op.
     *
     * Ook ná een punt, vraagteken of uitroepteken: sinds de titels met een vraag
     * openen ("Zelf een website bouwen of laten maken? :trades in :city") staat
     * er anders "... laten maken? acupuncturisten in Middelburg".
     *
     * mb_-functies: branche-tokens kunnen accenten bevatten (diëtist).
     */
    private function zinBeginKapitaal(string $tekst): string
    {
        if ($tekst === '') {
            return $tekst;
        }

        $tekst = mb_strtoupper(mb_substr($tekst, 0, 1)) . mb_substr($tekst, 1);

        return (string) preg_replace_callback(
            '/([.?!])(\s+)(\p{Ll})/u',
            fn ($m) => $m[1] . $m[2] . mb_strtoupper($m[3]),
            $tekst
        );
    }

    /** Deterministische keuze uit een variantset o.b.v. slug + slot-offset. */
    private function pick(array $arr, string $slug, int $offset): string
    {
        $arr = array_values($arr);
        if (! $arr) {
            return '';
        }
        $hash = abs(crc32($slug . ':' . $offset));
        return (string) $arr[$hash % count($arr)];
    }

    /** FAQ: alle {q,a}-templates ingevuld (niet gekozen — vaste set). */
    /**
     * Kiest één FAQ-set voor deze plaats, of geeft de losse lijst ongewijzigd terug.
     *
     * WAAROM. Elk ander tekstblok koos al een variant per plaats, maar de FAQ niet: die
     * stond op álle plaatspagina's letterlijk gelijk. Gemeten 03-08-2026 hadden vier
     * plaatspagina's van bedrijfswebsite gemiddeld 31% identieke tekst (Assen en Beilen
     * zelfs 42%, over 6-woord-reeksen), en Google liet 684 van de 1.026 URL's staan op
     * "Gevonden — momenteel niet geïndexeerd". Een blok dat op honderden pagina's woord
     * voor woord hetzelfde is, helpt daar niet bij.
     *
     * TWEE VORMEN, want de andere zeventien kanalen delen deze config:
     *   - lijst van {q,a}      → oude gedrag, ongewijzigd (alle vragen, overal gelijk)
     *   - lijst van LIJSTEN    → per plaats één set, met dezelfde deterministische keuze
     *                            als de overige blokken (crc32 over de slug)
     *
     * Deterministisch en niet willekeurig: dezelfde plaats moet bij elke render dezelfde
     * tekst geven, anders ziet een crawler bij elk bezoek iets anders.
     *
     * @param  array<int,mixed>  $set
     * @return array<int,mixed>
     */
    private function faqSet(array $set, string $slug, int $offset): array
    {
        $eerste = reset($set);

        // Losse {q,a}-lijst (of iets onverwachts) → oude gedrag, ongewijzigd. Een set van
        // sets herkennen we eraan dat het eerste element zelf een lijst is zonder q/a.
        if (! is_array($eerste) || isset($eerste['q']) || isset($eerste['a'])) {
            return $set;
        }

        $sets = array_values(array_filter($set, 'is_array'));
        if (! $sets) {
            return [];
        }

        $hash = abs(crc32($slug . ':faq:' . $offset));

        return (array) $sets[$hash % count($sets)];
    }

    private function faq(array $set, callable $repl): array
    {
        $out = [];
        foreach ($set as $qa) {
            $q = $repl($qa['q'] ?? $qa[0] ?? '');
            $a = $repl($qa['a'] ?? $qa[1] ?? '');
            if ($q !== '' && $a !== '') {
                $out[] = ['q' => $q, 'a' => $a];
            }
        }
        return $out;
    }
}
