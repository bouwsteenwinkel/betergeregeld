<?php

namespace App\Services;

use App\Support\ChannelSite;

/**
 * Vindt channel-sites op basis van host (live) of key (preview), en levert
 * de plaatsen-lijst + slug-lookup voor de plaatsen-pagina's.
 */
class ChannelSiteResolver
{
    /** @return array<string,array<string,mixed>> */
    public function all(): array
    {
        return (array) config('channel_sites.channels', []);
    }

    public function byKey(string $key): ?ChannelSite
    {
        $cfg = config('channel_sites.channels.' . $key);
        return is_array($cfg) ? new ChannelSite($key, $cfg) : null;
    }

    /** Live channel met dit domein (host zonder www/poort), of null. */
    public function byHost(string $host): ?ChannelSite
    {
        $host = strtolower(preg_replace('/:\d+$/', '', $host));
        $host = preg_replace('/^www\./', '', $host);

        foreach ($this->all() as $key => $cfg) {
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
        $out = [];
        foreach ($this->all() as $key => $cfg) {
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
