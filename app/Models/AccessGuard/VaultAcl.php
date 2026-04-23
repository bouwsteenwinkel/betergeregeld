<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaultAcl extends Model
{
	public $timestamps = false;

	protected $table = 'accessguard_vault_acl';

	protected $fillable = [
		'tenant_id', 'credential_id', 'user_id',
		'can_view', 'can_decrypt', 'can_rotate', 'can_admin',
		'granted_at', 'granted_by_user_id',
		'revoked_at', 'revoked_by_user_id',
	];

	protected function casts(): array
	{
		return [
			'can_view' => 'boolean',
			'can_decrypt' => 'boolean',
			'can_rotate' => 'boolean',
			'can_admin' => 'boolean',
			'granted_at' => 'datetime',
			'revoked_at' => 'datetime',
		];
	}

	public function credential(): BelongsTo
	{
		return $this->belongsTo(VaultCredential::class, 'credential_id');
	}

	public function isActive(): bool
	{
		return $this->revoked_at === null;
	}
}
