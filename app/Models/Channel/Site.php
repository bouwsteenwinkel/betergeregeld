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

    /** De 5 "hero-beelden": de website-hero + de vier facet-hero's. */
    public const HERO_SLOTS = ['hero', 'facet-webshop', 'facet-klantenportaal', 'facet-automatisering', 'facet-ai'];

    /** De 5 "wat het oplevert"-beelden: de facet-previews. */
    public const OUTCOME_SLOTS = ['website-preview', 'webshop-preview', 'klantenportaal-preview', 'automatisering-preview', 'ai-preview'];

    public function branche(): BelongsTo
    {
        return $this->belongsTo(Branche::class, 'channel_branche_id');
    }

    // ── Voortgangs-/gereedheids-signalen voor de admin-lijst ──────────────────

    /** Is het domein via ons (OpenProvider) geregistreerd? Uit de gesyncte meta. */
    public function domainRegistered(): bool
    {
        return filled(data_get($this->meta, 'domain_registered_at'))
            || (bool) data_get($this->meta, 'op.registered');
    }

    /** Wijzen apex + www naar de VPS? Uit de gesyncte meta (knop "Domeinstatus verversen"). */
    public function dnsOk(): bool
    {
        return (bool) data_get($this->meta, 'op.dns_ok');
    }

    /** Heeft de site content (blokken) — de tekst-prefill is gedaan? */
    public function contentPrefilled(): bool
    {
        return (int) ($this->blocks_count ?? $this->blocks()->count()) > 0;
    }

    /** Is er een eigen logo voor deze site? */
    public function hasLogo(): bool
    {
        return is_file(public_path('channel-media/' . $this->key . '/logo.webp'))
            || filled(data_get($this->brand, 'logo_image'));
    }

    /** Aantal aanwezige hero-beelden (0..5), alleen site-eigen (geen branche-fallback). */
    public function heroImageCount(): int
    {
        return $this->heroCount ??= $this->countOwnImages(self::HERO_SLOTS);
    }

    /** Aantal aanwezige "wat het oplevert"-beelden (0..5), site-eigen. */
    public function outcomeImageCount(): int
    {
        return $this->outcomeCount ??= $this->countOwnImages(self::OUTCOME_SLOTS);
    }

    private ?int $heroCount = null;

    private ?int $outcomeCount = null;

    private function countOwnImages(array $slots): int
    {
        $gen = app(\App\Services\ChannelSites\ChannelImageGenerator::class);
        $n = 0;
        foreach ($slots as $slot) {
            if ($gen->url($this->key, $slot) !== null) {
                $n++;
            }
        }
        return $n;
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
            'branche_name'=> $this->branche?->name,
            'locale'      => $this->locale,
            'domain'  => $this->domain,
            'status'  => $this->status,
            // Cascade: branche-thema als basis, site-thema overschrijft per token.
            'theme'   => array_merge((array) ($this->branche?->theme ?? []), (array) $this->theme),
            'brand'   => (array) $this->brand,
            'meta'    => (array) $this->meta,
            'header'  => (array) $this->header,
            'view'    => $this->legacy_view,
            // Plaatsen-teksten leven op de niche-branche (geldt voor alle sites
            // van die branche); per-site override kan later via een site-veld.
            'places'  => (array) ($this->branche?->places ?? []),
        ];

        return new ChannelSite($this->key, $cfg, $this->relationLoaded('blocks') ? $this->blocks : null);
    }
}
