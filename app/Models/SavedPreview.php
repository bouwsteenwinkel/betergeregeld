<?php

namespace App\Models;

use App\Models\Channel\Site;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén door de klant opgeslagen voorbeeldsite, gekoppeld aan een {@see WebsiteLead}
 * (het account). De preview zelf blijft een channel_sites-rij (key preview-...);
 * de ontwerp-voorkeuren staan daar in meta.preview.input.
 */
class SavedPreview extends Model
{
    protected $fillable = ['website_lead_id', 'site_key', 'favorite'];

    protected $casts = ['favorite' => 'boolean'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(WebsiteLead::class, 'website_lead_id');
    }

    /** De onderliggende preview-Site (of null als 'ie inmiddels is opgeruimd). */
    public function site(): ?Site
    {
        return Site::where('key', $this->site_key)->first();
    }

    /** Absolute URL naar de preview (hoofddomein). */
    public function url(): string
    {
        return url('/_site/' . $this->site_key);
    }

    /**
     * Ontwerp-samenvatting uit de preview-meta, voor het team (Filament) en de
     * revisit-pagina: bedrijf, type, doel, sfeer, kleur.
     *
     * @return array<string,string>
     */
    public function designSummary(): array
    {
        $input = (array) data_get(optional($this->site())->meta, 'preview.input', []);

        return array_filter([
            'bedrijf' => $input['company'] ?? '',
            'type'    => $input['business_type'] ?? '',
            'doel'    => $input['goal'] ?? '',
            'sfeer'   => $input['sfeer'] ?? '',
            'kleur'   => $input['color'] ?? '',
        ], fn ($v) => $v !== '');
    }
}
