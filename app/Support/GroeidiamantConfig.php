<?php

namespace App\Support;

/**
 * De inhoud van de Groeidiamant-pagina (/groeidiamant) voor een kanaal.
 *
 * Drie lagen, van generiek naar specifiek:
 *   1. config/groeidiamant_basis.php            -- wat voor elk kanaal hetzelfde is
 *   2. config/{key}_landings.php van de site    -- per fase de hero-zin en usps van de bestaande
 *                                                  dienstpagina's (branche-specifiek, automatisch)
 *   3. config/{key}_groeidiamant.php            -- de handgeschreven branche-laag (register:
 *                                                  config/channel_groeidiamant.php)
 * Laag 3 wint van 2, 2 van 1. Welke laag per fase gebruikt is staat in 'bron', zodat het
 * eindrapport (docs/GROEIDIAMANT-CHANGES.md) en de admin kunnen zien wie nog op terugval draait.
 *
 * De AI-telefonie-sectie leest bovendien config/{key}_telefonie.php (voorbeeldvragen, demo-
 * nummer, woorden) als het kanaal een telefoniepagina heeft; de fase-link gaat dan naar
 * /ai-telefonie-{key} in plaats van de generieke /ai-landing.
 *
 * Plaatshouders: :zaak, :trade, :trades (ChannelTokens), :bedrijf ("je kantoor"), :bedrijven
 * ("advocatenkantoren"), :Bedrijf (zinbegin). Woorden uit 'woorden' in de branche-laag, anders
 * uit de telefonie-config, anders de kanaaltokens.
 */
final class GroeidiamantConfig
{
    /** @return array<string,mixed> */
    public static function for(ChannelSite $site): array
    {
        $basis = (array) config('groeidiamant_basis', []);
        $cfgKey = config('channel_groeidiamant.' . $site->key);
        $branche = $cfgKey ? (array) config($cfgKey, []) : [];

        $telKey = config('channel_telefonie.' . $site->key);
        $tel = $telKey ? (array) config($telKey, []) : [];

        $landingsKey = config('channel_landings.' . $site->key);
        $landings = $landingsKey ? (array) config($landingsKey, []) : [];

        // Woorden: branche-laag > telefonie-config > kanaaltokens.
        $tokens = ChannelTokens::map((array) $site->get('places', []), $site->brancheKey());
        $w = (array) ($branche['woorden'] ?? []) + (array) ($tel['woorden'] ?? []);
        $bedrijf = (string) ($w['bedrijf'] ?? ('je ' . $tokens[':zaak']));
        $map = $tokens + [
            ':bedrijven' => (string) ($w['bedrijven'] ?? $tokens[':trades']),
            ':bedrijf'   => $bedrijf,
            ':Bedrijf'   => self::ucfirst($bedrijf),
        ];
        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));

        $c = $basis;
        $bron = [];

        // Fasen: per fase en per sleutel de meest specifieke laag.
        foreach ((array) $basis['fasen'] as $fase => $f) {
            $b = (array) ($branche['fasen'][$fase] ?? []);
            $l = (array) ($landings[$fase] ?? []);
            $uitLandings = [];
            if ($l) {
                if (! empty($l['hero']['sub'])) {
                    $uitLandings['voor'] = (string) $l['hero']['sub'];
                }
                if (! empty($l['hero']['usps']) && is_array($l['hero']['usps'])) {
                    $uitLandings['voorbeelden'] = array_values(array_map('strval', $l['hero']['usps']));
                }
            }
            $c['fasen'][$fase] = array_replace($f, $uitLandings, $b);
            $bron[$fase] = $b ? 'branche' : ($uitLandings ? 'landings' : 'basis');

            // Link: telefoniepagina voor de AI-fase als die bestaat, anders de facet-landing.
            $route = (string) ($c['fasen'][$fase]['route'] ?? $fase);
            if ($fase === 'ai' && $telKey) {
                $route = 'ai-telefonie-' . $site->key;
            }
            $c['fasen'][$fase]['url'] = $site->url($route);
            $c['fasen'][$fase]['key'] = $fase;
            $c['fasen'][$fase]['nr']  = (int) config('groeidiamant.facets.' . $fase . '.nr', 0);
        }

        // Losse blokken: de branche-laag vervangt het blok in zijn geheel (praktijk, telefonie-
        // vragen) of per sleutel (hero, seo, instap, slot).
        foreach (['hero', 'instap', 'slot', 'waarom', 'telefonie', 'formulier'] as $blok) {
            $c[$blok] = array_replace((array) ($basis[$blok] ?? []), (array) ($branche[$blok] ?? []));
        }
        foreach (['seo_titel', 'seo_omschrijving'] as $k) {
            if (! empty($branche[$k])) {
                $c[$k] = $branche[$k];
            }
        }
        if (! empty($branche['situaties'])) {
            foreach ((array) $branche['situaties'] as $fase => $s) {
                $c['situaties'][$fase] = array_replace((array) ($c['situaties'][$fase] ?? []), (array) $s);
            }
        }
        if (! empty($branche['praktijk'])) {
            $c['praktijk'] = (array) $branche['praktijk'];
            $bron['praktijk'] = 'branche';
        } else {
            $bron['praktijk'] = 'basis';
        }

        // AI-telefonie-sectie: vragen uit de branche-laag, anders uit de gesprekken van de
        // telefoniepagina, anders de basis. Demonummer alleen uit de telefonie-config.
        if (empty($branche['telefonie']['vragen']) && ! empty($tel['gesprekken'])) {
            $vragen = [];
            foreach ((array) $tel['gesprekken'] as $g) {
                if (! empty($g['vraag'])) {
                    $vragen[] = (string) $g['vraag'];
                }
            }
            if ($vragen) {
                $c['telefonie']['vragen'] = array_slice($vragen, 0, 4);
            }
        }
        $bron['telefonie'] = ! empty($branche['telefonie']['vragen']) ? 'branche' : (! empty($tel['gesprekken']) ? 'telefonie' : 'basis');
        $c['telefonie']['demo_nummer'] = (string) ($tel['demo_nummer'] ?? '');
        $c['telefonie']['url'] = $c['fasen']['ai']['url'];
        $c['heeftTelefonie'] = (bool) $telKey;

        // FAQ: basisvragen, per vraag vervangbaar op 'q' (zelfde vraagtekst), plus extra's.
        $faq = (array) $basis['faq'];
        foreach ((array) ($branche['faq'] ?? []) as $over) {
            foreach ($faq as $i => $vraag) {
                if (($vraag['q'] ?? null) === ($over['q'] ?? null)) {
                    $faq[$i] = $over;
                    continue 2;
                }
            }
            $faq[] = $over;
        }
        $c['faq'] = $faq;
        $bron['faq'] = ! empty($branche['faq']) ? 'branche' : 'basis';
        $bron['hero'] = ! empty($branche['hero']) ? 'branche' : 'basis';

        $c['bron'] = $bron;
        // De losse woorden voor de view (tekst die niet uit de config komt).
        $c['w'] = ['bedrijf' => $bedrijf, 'bedrijven' => $map[':bedrijven'], 'trades' => $tokens[':trades'], 'zaak' => $tokens[':zaak']];
        $c['configKey'] = $cfgKey;
        $c['afspraakUrl'] = $site->url('afspraak');
        $c['brancheKey'] = (string) $site->brancheKey();

        return self::vervang($c, $map);
    }

    /** Welke kanalen draaien (deels) op terugvalcontent? Voor het eindrapport/admin. */
    public static function bronnen(ChannelSite $site): array
    {
        return (array) (self::for($site)['bron'] ?? []);
    }

    private static function ucfirst(string $s): string
    {
        return mb_strtoupper(mb_substr($s, 0, 1)) . mb_substr($s, 1);
    }

    /** @param array<string,string> $map */
    private static function vervang(mixed $v, array $map): mixed
    {
        if (is_array($v)) {
            return array_map(fn ($x) => self::vervang($x, $map), $v);
        }
        return is_string($v) ? strtr($v, $map) : $v;
    }
}
