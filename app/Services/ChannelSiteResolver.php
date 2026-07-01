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

    /** @return array<string,string> slug => weergavenaam */
    public function places(): array
    {
        $out = [];
        foreach ((array) config('nl_places', []) as $place) {
            $out[ChannelSite::slug($place)] = $place;
        }
        return $out;
    }

    public function placeName(string $slug): ?string
    {
        return $this->places()[$slug] ?? null;
    }
}
