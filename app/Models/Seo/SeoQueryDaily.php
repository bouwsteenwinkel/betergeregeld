<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;

/**
 * Eén (property, date, query, page)-rij uit GSC searchAnalytics.
 *
 * Unique-key (property_id, date, row_hash) wordt door de DB afgedwongen
 * (zie migration). row_hash = SHA1(query|page), generated column.
 */
class SeoQueryDaily extends Model
{
	protected $table = 'seo_query_daily';

	public $timestamps = false; // alleen created_at, geen updated_at

	protected $fillable = [
		'property_id', 'date', 'query', 'page',
		'clicks', 'impressions', 'ctr', 'position',
	];

	protected function casts(): array
	{
		return [
			'date'        => 'date',
			'clicks'      => 'integer',
			'impressions' => 'integer',
			'ctr'         => 'float',
			'position'    => 'float',
		];
	}
}
