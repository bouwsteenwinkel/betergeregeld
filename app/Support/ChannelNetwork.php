<?php

namespace App\Support;

use App\Models\Channel\Site;
use Illuminate\Support\Facades\Cache;

/**
 * De live kanaalsites als netwerk: één bron voor de onderlinge links.
 *
 * WAAROM DIT BESTAAT. We hebben 17 live branche-domeinen die tot 02-08-2026 alleen
 * naar zichzelf linkten. Op betergeregeld.com én in de footers stonden de vaknamen
 * als dode `<span>`s — de lijst was drie keer met de hand overgetypt en liep dus
 * vanzelf uit de pas met wat er echt live staat. Voor Google is een domein zonder
 * inkomende links een eiland; gemeten op jouw-bedrijfswebsite.nl: 241 vertoningen
 * in 90 dagen op gemiddelde positie 35 (= pagina 4, nul kliks).
 *
 * Eén query per 6 uur, gecacht: dit draait in de footer van élke pagina van élk
 * kanaal, dus een ongecachte query zou een paar duizend keer per dag afgaan voor
 * data die per maand hoogstens één keer verandert.
 */
class ChannelNetwork
{
    /** Cache-duur in seconden. Nieuwe site live? `php artisan cache:clear` of 6 uur wachten. */
    private const TTL = 21600;

    /**
     * Alle live kanaalsites, alfabetisch op vaknaam.
     *
     * @param  string|null  $behalve  Sluit dit kanaal uit — een site linkt niet naar zichzelf.
     * @return array<int, array{key:string, name:string, domain:string, url:string}>
     */
    public static function live(?string $behalve = null): array
    {
        $alle = Cache::remember('channel_network_live', self::TTL, function (): array {
            return Site::query()
                ->where('status', 'live')
                ->orderBy('name')
                ->get(['key', 'name', 'domain'])
                ->filter(fn ($s) => filled($s->domain))
                ->map(fn ($s) => [
                    'key'    => (string) $s->key,
                    'name'   => (string) $s->name,
                    'domain' => (string) $s->domain,
                    'url'    => 'https://' . ltrim((string) $s->domain, '/'),
                ])
                ->values()
                ->all();
        });

        if ($behalve === null) {
            return $alle;
        }

        return array_values(array_filter($alle, fn ($s) => $s['key'] !== $behalve));
    }

    /** Eén kanaal opzoeken; null als het niet (meer) live staat. */
    public static function find(string $key): ?array
    {
        foreach (self::live() as $s) {
            if ($s['key'] === $key) {
                return $s;
            }
        }

        return null;
    }

    /** De URL van een kanaal, of null als het niet live staat. Voor losse links in views. */
    public static function url(string $key): ?string
    {
        return self::find($key)['url'] ?? null;
    }
}
