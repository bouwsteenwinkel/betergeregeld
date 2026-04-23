<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VaultCredential extends Model
{
	public const TYPES = ['password', 'token', 'api_key', 'ssh_key', 'cert', 'other'];

	protected $table = 'accessguard_vault_credentials';

	protected $fillable = [
		'tenant_id', 'system_id', 'access_item_id', 'name', 'type',
		'username', 'secret_enc', 'metadata',
		'expires_at', 'rotation_interval_days', 'last_rotated_at',
		'created_by_user_id',
	];

	protected function casts(): array
	{
		return [
			'metadata' => 'array',
			'expires_at' => 'date',
			'last_rotated_at' => 'datetime',
			'rotation_interval_days' => 'integer',
		];
	}

	public function system(): BelongsTo
	{
		return $this->belongsTo(BusinessSystem::class, 'system_id');
	}

	public function accessItem(): BelongsTo
	{
		return $this->belongsTo(AccessItem::class, 'access_item_id');
	}

	public function acl(): HasMany
	{
		return $this->hasMany(VaultAcl::class, 'credential_id');
	}

	public function logs(): HasMany
	{
		return $this->hasMany(VaultAccessLog::class, 'credential_id');
	}

	public function isExpired(): bool
	{
		return $this->expires_at !== null && $this->expires_at->isPast();
	}

	public function isRotationDue(): bool
	{
		if (! $this->rotation_interval_days || ! $this->last_rotated_at) {
			return false;
		}
		return $this->last_rotated_at->copy()->addDays($this->rotation_interval_days)->isPast();
	}
}
