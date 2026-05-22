<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;

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
		'site_url', 'label', 'is_active',
		'last_imported_date', 'last_import_error',
	];

	protected function casts(): array
	{
		return [
			'is_active'          => 'boolean',
			'last_imported_date' => 'date',
		];
	}
}
