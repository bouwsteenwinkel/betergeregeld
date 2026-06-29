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
            'theme'   => (array) $this->theme,
            'brand'   => (array) $this->brand,
            'meta'    => (array) $this->meta,
            'header'  => (array) $this->header,
            'view'    => $this->legacy_view,
        ];

        return new ChannelSite($this->key, $cfg, $this->relationLoaded('blocks') ? $this->blocks : null);
    }
}
