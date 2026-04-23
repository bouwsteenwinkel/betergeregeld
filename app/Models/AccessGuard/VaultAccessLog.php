<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class VaultAccessLog extends Model
{
	public $timestamps = false;

	protected $table = 'accessguard_vault_access_log';

	protected $fillable = [
		'tenant_id', 'credential_id', 'user_id',
		'action', 'ip_hash', 'meta', 'occurred_at',
	];

	protected function casts(): array
	{
		return [
			'meta' => 'array',
			'occurred_at' => 'datetime',
		];
	}
}
