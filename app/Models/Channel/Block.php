<?php

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén blok op een channel-site. Type kiest het sjabloon uit de bibliotheek;
 * content/style zijn per type. Status = checklist; locked = funnel-blok.
 */
class Block extends Model
{
    protected $table = 'channel_blocks';

    protected $fillable = [
        'channel_site_id', 'type', 'block_key', 'sort',
        'enabled', 'locked', 'status', 'content', 'style', 'design_notes',
    ];

    protected $casts = [
        'content' => 'array',
        'style'   => 'array',
        'enabled' => 'boolean',
        'locked'  => 'boolean',
        'sort'    => 'integer',
    ];

    /** Checklist-statussen (key => label) voor de admin. */
    public const STATUSES = [
        'placeholder' => 'Placeholder',
        'bewerking'   => 'In bewerking',
        'klaar'       => 'Klaar',
    ];

    /** Blokken-bibliotheek (type => label) — bron voor de admin-selects. */
    public const TYPES = [
        'hero'     => 'Hero',
        'uspbar'   => 'USP-balk',
        'features' => 'Features',
        'steps'    => 'Stappen',
        'about'    => 'Over ons',
        'proof'    => 'Citaat / proof',
        'reviews'  => 'Reviews',
        'faq'      => 'FAQ',
        'gallery'  => 'Galerij / portfolio',
        'pricelist' => 'Prijslijst (diensten)',
        'pricing'  => 'Pakketten',
        'cta'      => 'CTA-band',
        'logos'    => 'Logo\'s / partners',
        'location' => 'Locatie + openingstijden',
        'blog'     => 'Blog-uitgelicht',
        'richtext' => 'Vrij tekstblok',
        'groeipad' => 'Groeidiamant-selector',
        'wizard'   => 'Lead-wizard (funnel)',
    ];

    /** Funnel-blokken die niet verwijderd mogen worden. */
    public const FUNNEL_TYPES = ['wizard'];

    /** Actieve Groeidiamant-fase (transient) — stuurt facet-content-overrides. */
    public ?string $activeFacet = null;

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'channel_site_id');
    }

    public function setActiveFacet(?string $facet): static
    {
        $this->activeFacet = $facet;

        return $this;
    }

    /**
     * Per-blok content-veld met fallback. Als er een actieve fase is en het blok
     * een override heeft onder content.facets.{facet}.{key}, wint die.
     */
    public function c(string $key, mixed $default = null): mixed
    {
        if ($this->activeFacet) {
            $override = data_get($this->content, 'facets.' . $this->activeFacet . '.' . $key);
            // Lege override (null/''/[]) telt als "niet ingesteld" → basis-inhoud.
            if ($override !== null && $override !== '' && $override !== []) {
                return $override;
            }
        }

        return data_get($this->content, $key, $default);
    }
}
