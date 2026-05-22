<?php

namespace App\Models\AccessGuard\Radar;

use App\Models\AccessGuard\BusinessSystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
	public const KINDS = [
		'website', 'webshop', 'cms', 'plugin',
		'server', 'endpoint', 'saas_app',
		'api', 'container', 'network_device',
	];
	public const ENVIRONMENTS = ['production', 'staging', 'test'];
	public const TRISTATE = ['yes', 'no', 'unknown'];
	public const CRITICALITY = ['low', 'medium', 'high', 'critical'];
	public const STATUSES = ['active', 'ignored', 'deleted', 'unknown'];

	protected $table = 'accessguard_radar_assets';

	protected $fillable = [
		'tenant_id', 'system_id',
		'kind', 'name', 'hostname', 'url',
		'vendor', 'product', 'current_version',
		'environment', 'is_public', 'auth_required', 'criticality', 'status',
		'discovered_at', 'last_seen_at', 'last_scanned_at',
		'owner_user_id', 'notes',
	];

	protected function casts(): array
	{
		return [
			'discovered_at' => 'datetime',
			'last_seen_at' => 'datetime',
			'last_scanned_at' => 'datetime',
		];
	}

	public function softwareInstances(): HasMany
	{
		return $this->hasMany(SoftwareInstance::class, 'asset_id');
	}

	public function findings(): HasMany
	{
		return $this->hasMany(Finding::class, 'asset_id');
	}

	public function scans(): HasMany
	{
		return $this->hasMany(Scan::class, 'asset_id');
	}

	public function system(): BelongsTo
	{
		return $this->belongsTo(BusinessSystem::class, 'system_id');
	}
}
