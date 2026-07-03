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
     * @return array<string,mixed>  ingevulde blokken (meta_title, h1, hero_lead, intro, wie, trust, cta, cta_title, faq)
     */
    public function assemble(array $tokens, array $place): array
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

        // Volgorde: langste tokens eerst (:trades vóór :trade) om deel-vervanging te voorkomen.
        $map = [
            ':trades'  => (string) ($t['trades'] ?? 'bedrijven'),
            ':trade'   => (string) ($t['trade'] ?? 'bedrijf'),
            ':niches'  => (string) ($t['niches'] ?? 'diensten'),
            ':niche'   => (string) ($t['niche'] ?? 'vak'),
            ':service' => (string) ($t['service'] ?? 'website'),
            ':region'  => $region,
            ':full'    => $full,
            ':city'    => $city,
        ];
        $repl = fn ($str) => strtr((string) $str, $map);

        $out    = ['_city' => $city, '_region' => $region, '_full' => $full];
        $offset = 0;
        foreach ($variants as $slot => $set) {
            $offset++;
            if ($slot === 'faq') {
                $out['faq'] = $this->faq((array) $set, $repl);
                continue;
            }
            $out[$slot] = $repl($this->pick((array) $set, $slug, $offset));
        }
        return $out;
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
