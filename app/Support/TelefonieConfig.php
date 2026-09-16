<?php

namespace App\Support;

/**
 * De inhoud van een AI-telefonie-landingspagina (/ai-telefonie-{key}) voor een kanaal.
 *
 * Per kanaal staat de branche-eigen inhoud in config/{key}_telefonie.php (geregistreerd in
 * config/channel_telefonie.php): wanneer de telefoon stoort, voorbeeldgesprekken, wat de
 * assistent voor dít vak doet en niet doet, de branche-FAQ en de startstand van de rekenhulp.
 * Wat voor elk kanaal hetzelfde is -- prijs, eigen nummer, privacy, de vier inrichtstappen,
 * de algemene FAQ -- staat één keer in config/telefonie_basis.php en wordt hier
 * samengevoegd. Lijsten uit het kanaal komen vóór de lijsten uit de basis, zodat het
 * vakspecifieke bovenaan staat.
 *
 * Plaatshouders in alle teksten: :bedrijf ("je bakkerij"), :bedrijven ("bakkerijen"),
 * :klant / :klanten ("patiënt" / "patiënten"), :gegevens (wat de ondernemer aanlevert),
 * plus de gewone kanaaltokens (:trade, :trades, :zaak) uit ChannelTokens. De woorden komen
 * uit 'woorden' in de kanaalconfig; ontbreekt er een, dan valt hij terug op de kanaaltokens.
 */
final class TelefonieConfig
{
    /** @return array<string,mixed> Leeg als dit kanaal geen telefoniepagina heeft. */
    public static function for(ChannelSite $site): array
    {
        $cfgKey = config('channel_telefonie.' . $site->key);
        if (! $cfgKey) {
            return [];
        }
        $kanaal = (array) config($cfgKey, []);
        $basis  = (array) config('telefonie_basis', []);
        if (! $kanaal) {
            return [];
        }

        $tokens = ChannelTokens::map((array) $site->get('places', []), $site->brancheKey());
        $w = (array) ($kanaal['woorden'] ?? []);
        $map = $tokens + [
            ':bedrijven' => (string) ($w['bedrijven'] ?? $tokens[':trades']),
            ':bedrijf'   => (string) ($w['bedrijf'] ?? ('je ' . $tokens[':zaak'])),
            ':klanten'   => (string) ($w['klanten'] ?? 'klanten'),
            ':klant'     => (string) ($w['klant'] ?? 'klant'),
            ':gegevens'  => (string) ($w['gegevens'] ?? 'openingstijden, diensten en tarieven'),
        ];
        // Langste sleutels eerst, anders vervangt :bedrijf de kop van :bedrijven.
        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));

        $c = array_replace($basis, $kanaal);
        foreach (['kan', 'niet', 'faq'] as $lijst) {
            $c[$lijst] = array_merge((array) ($kanaal[$lijst] ?? []), (array) ($basis[$lijst . '_basis'] ?? []));
            unset($c[$lijst . '_basis']);
        }
        // 'verder' (de drie kaarten onderaan) is óf helemaal van het kanaal, óf de basis.
        $c['verder'] = (array) ($kanaal['verder'] ?? $basis['verder_basis'] ?? []);
        unset($c['verder_basis']);
        // Rekenhulp: cijfers en teksten uit de basis, per kanaal overschreven waar het kanaal iets zegt.
        $c['rekenhulp'] = array_replace_recursive((array) ($basis['rekenhulp'] ?? []), (array) ($kanaal['rekenhulp'] ?? []));
        $c['configKey'] = $cfgKey;

        return self::vervang($c, $map);
    }

    /** @param array<string,mixed> $map */
    private static function vervang(mixed $v, array $map): mixed
    {
        if (is_array($v)) {
            return array_map(fn ($x) => self::vervang($x, $map), $v);
        }
        return is_string($v) ? strtr($v, $map) : $v;
    }
}
