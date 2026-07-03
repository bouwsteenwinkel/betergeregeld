<?php

namespace App\Services;

use App\Models\Channel\Site;
use App\Support\ChannelSite;
use Illuminate\Support\Facades\Schema;

/**
 * Vindt channel-sites op basis van host (live) of key (preview). DB-first: sites
 * worden in de admin beheerd (channel_sites). Zolang de tabellen er nog niet zijn
 * of een key alleen in config staat, valt hij terug op config/channel_sites.php.
 */
class ChannelSiteResolver
{
    private static ?bool $db = null;

    /** Zijn de channel-tabellen beschikbaar? (gecachet per request) */
    private function dbReady(): bool
    {
        if (self::$db === null) {
            try {
                self::$db = Schema::hasTable('channel_sites');
            } catch (\Throwable $e) {
                self::$db = false;
            }
        }
        return self::$db;
    }

    /** @return array<string,array<string,mixed>> config-kanalen (legacy/fallback) */
    public function allConfig(): array
    {
        return (array) config('channel_sites.channels', []);
    }

    public function byKey(string $key): ?ChannelSite
    {
        if ($this->dbReady()) {
            $model = Site::with(['branche', 'blocks'])->where('key', $key)->first();
            if ($model) {
                return $model->toChannelSite();
            }
        }
        // Fallback: config-kanaal (nog niet gemigreerd naar DB).
        $cfg = config('channel_sites.channels.' . $key);
        return is_array($cfg) ? new ChannelSite($key, $cfg) : null;
    }

    /** Live channel met dit domein (host zonder www/poort), of null. */
    public function byHost(string $host): ?ChannelSite
    {
        $host = strtolower(preg_replace('/:\d+$/', '', $host));
        $host = preg_replace('/^www\./', '', $host);

        if ($this->dbReady()) {
            $model = Site::with(['branche', 'blocks'])
                ->where('status', 'live')
                ->where('domain', $host)
                ->first();
            return $model?->toChannelSite();
        }

        foreach ($this->allConfig() as $key => $cfg) {
            $site = new ChannelSite($key, $cfg);
            if ($site->isLive() && $site->domain() === $host) {
                return $site;
            }
        }
        return null;
    }

    /** @return ChannelSite[] alleen live kanalen met een gekoppeld domein */
    public function live(): array
    {
        if ($this->dbReady()) {
            return Site::with(['branche', 'blocks'])
                ->where('status', 'live')
                ->whereNotNull('domain')
                ->get()
                ->map(fn (Site $m) => $m->toChannelSite())
                ->all();
        }

        $out = [];
        foreach ($this->allConfig() as $key => $cfg) {
            $site = new ChannelSite($key, $cfg);
            if ($site->isLive()) {
                $out[] = $site;
            }
        }
        return $out;
    }

    /**
     * @return array<string,string> slug => weergavenaam
     * Config-vorm: slug => ['naam','provincie','context'] (rijk) of een platte lijst.
     */
    public function places(): array
    {
        $out = [];
        foreach ((array) config('nl_places', []) as $k => $v) {
            if (is_int($k)) {                              // platte lijst 'Naam'
                $out[ChannelSite::slug($v)] = $v;
            } else {                                       // slug => [...] of slug => naam
                $out[(string) $k] = is_array($v) ? ($v['naam'] ?? $k) : $v;
            }
        }
        return $out;
    }

    /** @return array<string,string> slug => provincie ('Nederland' als onbekend) */
    public function regions(): array
    {
        $out = [];
        foreach ((array) config('nl_places', []) as $k => $v) {
            if (is_int($k)) {
                $out[ChannelSite::slug($v)] = 'Nederland';
            } elseif (is_array($v)) {
                $out[(string) $k] = $v['provincie'] ?? 'Nederland';
            } else {
                $out[(string) $k] = is_string($v) && $v !== '' ? $v : 'Nederland';
            }
        }
        return $out;
    }

    public function placeName(string $slug): ?string
    {
        return $this->places()[$slug] ?? null;
    }

    public function placeRegion(string $slug): string
    {
        return $this->regions()[$slug] ?? 'Nederland';
    }

    /**
     * Provincies (voor de /plaatsen-hub + footer).
     * @return array<string,array{name:string,slug:string,count:int}> provincie-slug => info
     */
    public function provinces(): array
    {
        $out = [];
        foreach ($this->regions() as $prov) {
            $slug = ChannelSite::slug($prov);
            if (! isset($out[$slug])) {
                $out[$slug] = ['name' => $prov, 'slug' => $slug, 'count' => 0];
            }
            $out[$slug]['count']++;
        }
        unset($out[ChannelSite::slug('Nederland')]);   // restgroep niet als provincie tonen
        ksort($out);
        return $out;
    }

    /** @return array<string,string> slug => naam voor alle plaatsen in een provincie. */
    public function provincePlaces(string $provSlug): array
    {
        $out   = [];
        $names = $this->places();
        foreach ($this->regions() as $slug => $prov) {
            if (ChannelSite::slug($prov) === $provSlug) {
                $out[$slug] = $names[$slug] ?? $slug;
            }
        }
        asort($out);
        return $out;
    }

    public function provinceName(string $provSlug): ?string
    {
        return $this->provinces()[$provSlug]['name'] ?? null;
    }

    /** Volledige plaats-data voor één slug, of null. */
    public function placeData(string $slug): ?array
    {
        $name = $this->placeName($slug);
        if ($name === null) {
            return null;
        }
        $raw = (array) (config('nl_places')[$slug] ?? []);
        return [
            'slug'      => $slug,
            'naam'      => $name,
            'provincie' => $raw['provincie'] ?? 'Nederland',
            'context'   => $raw['context'] ?? null,
        ];
    }
}
