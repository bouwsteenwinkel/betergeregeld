<?php

namespace App\Models\Channel;

use App\Support\ChannelSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eén concrete channel-site (in de admin beheerd). Levert via toChannelSite()
 * de read-only render-wrapper die de views gebruiken.
 */
class Site extends Model
{
    protected $table = 'channel_sites';

    protected $fillable = [
        'channel_branche_id', 'key', 'name', 'domain', 'status', 'locale',
        'theme', 'brand', 'meta', 'header', 'legacy_view',
    ];

    protected $casts = [
        'theme'  => 'array',
        'brand'  => 'array',
        'meta'   => 'array',
        'header' => 'array',
    ];

    public function branche(): BelongsTo
    {
        return $this->belongsTo(Branche::class, 'channel_branche_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(Block::class, 'channel_site_id')->orderBy('sort');
    }

    /** Actieve, ingeschakelde blokken in volgorde — voor de renderer. */
    public function visibleBlocks(): HasMany
    {
        return $this->blocks()->where('enabled', true);
    }

    /**
     * Genereert de basis-blokken uit de blueprint van de branche. Standaard alleen
     * als de site nog géén blokken heeft (zo overschrijf je geen designer-edits).
     * @return int aantal aangemaakte blokken
     */
    public function generateBlocksFromBlueprint(bool $force = false): int
    {
        // Bron: de blueprint-relatie (sleepbaar in de admin); val terug op het
        // oude JSON-veld zolang een branche nog geen relatie-rijen heeft.
        $rows = $this->branche?->blueprintBlocks()->get();
        $blueprint = ($rows && $rows->isNotEmpty())
            ? $rows->map(fn ($r) => ['type' => $r->type, 'status' => $r->status, 'locked' => $r->locked])->all()
            : (array) ($this->branche?->blueprint ?? []);
        if (! $blueprint) {
            return 0;
        }
        if (! $force && $this->blocks()->exists()) {
            return 0;
        }

        $existingKeys  = $this->blocks()->pluck('block_key')->all();
        $existingTypes = $this->blocks()->pluck('type')->all();
        $sort = (int) $this->blocks()->max('sort');
        $created = 0;

        foreach ($blueprint as $b) {
            $type = $b['type'] ?? null;
            if (! $type) {
                continue;
            }
            // Bij aanvullen (force): bestaande types overslaan i.p.v. dupliceren.
            if ($force && in_array($type, $existingTypes, true)) {
                continue;
            }
            $existingTypes[] = $type;
            // Uniek block_key per site afdwingen (type, type-2, …).
            $key = $type;
            $i = 1;
            while (in_array($key, $existingKeys, true)) {
                $key = $type . '-' . (++$i);
            }
            $existingKeys[] = $key;

            $this->blocks()->create([
                'type'      => $type,
                'block_key' => $key,
                'sort'      => $sort += 10,
                'enabled'   => true,
                'locked'    => (bool) ($b['locked'] ?? false),
                'status'    => $b['status'] ?? 'placeholder',
            ]);
            $created++;
        }

        return $created;
    }

    /** Bouwt de render-wrapper (incl. blokken) die de channel-views gebruiken. */
    public function toChannelSite(): ChannelSite
    {
        $cfg = [
            'name'        => $this->name,
            'branche'     => $this->branche?->lead_branche ?? 'overig',
            'branche_key' => $this->branche?->key,
            'locale'      => $this->locale,
            'domain'  => $this->domain,
            'status'  => $this->status,
            // Cascade: branche-thema als basis, site-thema overschrijft per token.
            'theme'   => array_merge((array) ($this->branche?->theme ?? []), (array) $this->theme),
            'brand'   => (array) $this->brand,
            'meta'    => (array) $this->meta,
            'header'  => (array) $this->header,
            'view'    => $this->legacy_view,
        ];

        return new ChannelSite($this->key, $cfg, $this->relationLoaded('blocks') ? $this->blocks : null);
    }
}
