<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DirectoryConnection extends Model
{
	public const PROVIDERS = ['m365'];
	public const STATUSES = ['connected', 'disconnected', 'error'];

	protected $table = 'accessguard_directory_connections';

	protected $fillable = [
		'tenant_id', 'provider', 'external_tenant_id', 'display_name',
		'access_token_enc', 'refresh_token_enc', 'expires_at', 'scopes',
		'status', 'last_synced_at', 'last_sync_status', 'last_sync_message',
		'last_sync_users_seen', 'last_sync_users_created', 'last_sync_users_updated',
		'last_sync_groups_seen', 'last_sync_groups_upserted', 'last_sync_memberships_seen',
	];

	protected function casts(): array
	{
		return [
			'expires_at' => 'datetime',
			'last_synced_at' => 'datetime',
		];
	}

	protected function accessToken(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->access_token_enc ? Crypt::decryptString($this->access_token_enc) : null,
			set: fn (?string $v) => ['access_token_enc' => $v ? Crypt::encryptString($v) : null],
		);
	}

	protected function refreshToken(): Attribute
	{
		return Attribute::make(
			get: fn () => $this->refresh_token_enc ? Crypt::decryptString($this->refresh_token_enc) : null,
			set: fn (?string $v) => ['refresh_token_enc' => $v ? Crypt::encryptString($v) : null],
		);
	}

	public function isExpired(): bool
	{
		return $this->expires_at && $this->expires_at->isPast();
	}
}
