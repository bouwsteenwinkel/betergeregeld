<?php

namespace App\Models\AccessGuard\Radar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoftwareInstance extends Model
{
	public const DETECTION_METHODS = [
		'http_header', 'generator_meta', 'wp_json',
		'tls_banner', 'osv_lookup', 'manual', 'agent',
	];

	protected $table = 'accessguard_radar_software_instances';

	protected $fillable = [
		'tenant_id', 'asset_id',
		'vendor', 'product', 'version',
		'detection_method', 'path',
		'first_detected_at', 'last_seen_at',
	];

	protected function casts(): array
	{
		return [
			'first_detected_at' => 'datetime',
			'last_seen_at' => 'datetime',
		];
	}

	public function asset(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'asset_id');
	}

	public function findings(): HasMany
	{
		return $this->hasMany(Finding::class, 'software_instance_id');
	}
}
