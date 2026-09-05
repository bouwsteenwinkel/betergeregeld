<?php

namespace App\Support;

/**
 * De branche-tokens waarmee de channel-teksten worden ingevuld.
 *
 * Deze map stond op negen plekken los overgeschreven -- in twee commando's, drie
 * services en vier views. Toen op 05-09-2026 het token `:zaak` erbij moest,
 * betekende dat negen bewerkingen die makkelijk uit de pas gaan lopen. Vandaar
 * één plek.
 *
 * De volgorde is niet vrijblijvend: `:trades` moet vóór `:trade`, anders vervangt
 * strtr() eerst het korte token en blijft er ":trades" -> "bakkerijs" over.
 * strtr() met een array pakt weliswaar de langste sleutel, maar de volgorde
 * hier houdt dat ook leesbaar voor wie het nakijkt.
 *
 * Over `:zaak` -- het token dat de aanleiding was:
 *
 * De verkoopteksten spreken de ondernemer aan met "je :trade". Dat werkt voor
 * een rijschool of een bakkerij, maar bij 55 van de 204 branches is `trade` een
 * mens: "je acupuncturist", "je advocaat". Dat is niet alleen krom, het draait
 * de hele pagina om -- van "wij bouwen jou een site" naar iets dat leest als
 * een consumentengids. In Search Console was dat te zien:
 * jouw-acupuncturist-website.nl werd gevonden op "acupunctuur zeeland", door
 * patienten dus. `:zaak` levert het woord voor de ondernemíng (praktijk,
 * kantoor, bureau, studio, zaak, bedrijf) en valt terug op `:trade` waar die al
 * een zaak is.
 */
class ChannelTokens
{
    /**
     * @param  array<string,mixed> $tokens  branche->places (of site-override)
     * @param  string|null         $brancheKey  voor het zaakwoord uit de config
     * @return array<string,string>
     */
    public static function map(array $tokens, ?string $brancheKey = null): array
    {
        $standaard = (array) config('channel_places.defaults', []);
        $t = array_merge($standaard, array_filter($tokens, fn ($v) => is_scalar($v) && $v !== ''));

        $trade = (string) ($t['trade'] ?? 'bedrijf');

        return [
            ':trades'  => (string) ($t['trades'] ?? 'bedrijven'),
            ':trade'   => $trade,
            ':niches'  => (string) ($t['niches'] ?? 'diensten'),
            ':niche'   => (string) ($t['niche'] ?? 'vak'),
            ':service' => (string) ($t['service'] ?? 'website'),
            ':zaak'    => self::zaak($t, $brancheKey, $trade),
        ];
    }

    /**
     * Het woord voor de onderneming.
     *
     * Volgorde: een expliciet `zaak` in de branche-tokens wint, dan de lijst in
     * channel_places.zaakwoord, en anders `trade` zelf -- goed voor de 149
     * branches die al een zaak zijn.
     *
     * @param array<string,mixed> $t
     */
    private static function zaak(array $t, ?string $brancheKey, string $trade): string
    {
        if (! empty($t['zaak']) && is_scalar($t['zaak'])) {
            return (string) $t['zaak'];
        }
        if ($brancheKey) {
            $uit = config('channel_places.zaakwoord.' . $brancheKey);
            if (is_string($uit) && $uit !== '') {
                return $uit;
            }
        }
        return $trade;
    }

    /** Vult de tokens in een tekst in. */
    public static function vul(string $tekst, array $tokens, ?string $brancheKey = null): string
    {
        return strtr($tekst, self::map($tokens, $brancheKey));
    }
}
