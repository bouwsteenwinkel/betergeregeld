<?php

namespace App\Models\Security;

use App\Models\Seo\SeoProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén stuk software dat een site draait (core/plugin/theme), gepusht door de
 * companion-plugin. has_update + vulnerable worden bij ingest bepaald.
 */
class SiteComponent extends Model
{
	protected $table = 'site_components';

	protected $fillable = [
		'property_id', 'type', 'slug', 'name', 'version', 'latest_version',
		'has_update', 'wp_active', 'vulnerable', 'vuln_count', 'reported_at',
	];

	protected $casts = [
		'has_update'  => 'boolean',
		'wp_active'   => 'boolean',
		'vulnerable'  => 'boolean',
		'vuln_count'  => 'integer',
		'reported_at' => 'datetime',
	];

	public function property(): BelongsTo
	{
		return $this->belongsTo(SeoProperty::class, 'property_id');
	}
}
