<?php

namespace App\Models\Seo;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eén GSC-property die we monitoren. site_url is óf 'sc-domain:host' óf
 * 'https://host/' (URL-prefix). Beide vormen worden geaccepteerd door de
 * GSC API, maar 'sc-domain' is doorgaans accurater omdat het alle
 * sub-domeinen en protocollen samenvoegt.
 */
class SeoProperty extends Model
{
	protected $table = 'seo_properties';

	protected $fillable = [
		'tenant_id', 'site_url', 'label', 'is_active',
		'last_imported_date', 'last_import_error',
	];

	public function tenant(): BelongsTo
	{
		return $this->belongsTo(Tenant::class);
	}

	public function queries(): HasMany
	{
		return $this->hasMany(SeoQueryDaily::class, 'property_id');
	}

	protected function casts(): array
	{
		return [
			'is_active'          => 'boolean',
			'last_imported_date' => 'date',
		];
	}
}
