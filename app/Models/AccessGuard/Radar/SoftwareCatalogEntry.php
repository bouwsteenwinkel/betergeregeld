<?php

namespace App\Models\AccessGuard\Radar;

use Illuminate\Database\Eloquent\Model;

/**
 * Tenant-agnostic catalog of "what does the world consider current"
 * per (vendor, product). Joined into SoftwareInstance freshness
 * calculations at display time.
 */
class SoftwareCatalogEntry extends Model
{
	public const SOURCES = [
		'wp_api', 'endoflife_date', 'npm', 'packagist', 'manual', 'wpvuln',
	];

	protected $table = 'accessguard_radar_software_catalog';

	protected $fillable = [
		'vendor', 'product',
		'latest_stable_version', 'latest_lts_version', 'latest_beta_version',
		'source', 'release_history', 'eol_dates', 'product_url',
		'last_synced_at', 'last_sync_error',
	];

	protected function casts(): array
	{
		return [
			'release_history' => 'array',
			'eol_dates' => 'array',
			'last_synced_at' => 'datetime',
		];
	}
}
