<?php

namespace App\Models\Security;

use App\Models\Seo\SeoProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén afwijkend WP-core-bestand t.o.v. de officiële WP.org-checksums
 * (gewijzigd/ontbrekend/onverwacht), gepusht door de companion-plugin.
 */
class SiteIntegrityIssue extends Model
{
	protected $table = 'site_integrity_issues';

	protected $fillable = ['property_id', 'type', 'path'];

	public function property(): BelongsTo
	{
		return $this->belongsTo(SeoProperty::class, 'property_id');
	}
}
