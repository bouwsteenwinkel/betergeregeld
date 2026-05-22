<?php

namespace App\Models\Seo;

use Illuminate\Database\Eloquent\Model;

class SeoPsiDaily extends Model
{
	protected $table = 'seo_psi_daily';

	public $timestamps = false;

	protected $fillable = [
		'property_id', 'url', 'strategy', 'date',
		'lcp_ms', 'cls', 'inp_ms', 'ttfb_ms',
		'performance_score', 'seo_score',
		'accessibility_score', 'best_practices_score',
		'error_message',
	];

	protected function casts(): array
	{
		return [
			'date'                 => 'date',
			'lcp_ms'               => 'integer',
			'cls'                  => 'float',
			'inp_ms'               => 'integer',
			'ttfb_ms'              => 'integer',
			'performance_score'    => 'integer',
			'seo_score'            => 'integer',
			'accessibility_score'  => 'integer',
			'best_practices_score' => 'integer',
		];
	}
}
