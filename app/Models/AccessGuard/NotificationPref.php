<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NotificationPref extends Model
{
	protected $table = 'accessguard_notification_prefs';

	protected $fillable = [
		'tenant_id', 'user_id',
		'digest_enabled', 'instant_critical_enabled',
		'last_digest_sent_at', 'unsubscribe_token',
	];

	protected function casts(): array
	{
		return [
			'digest_enabled' => 'boolean',
			'instant_critical_enabled' => 'boolean',
			'last_digest_sent_at' => 'datetime',
		];
	}

	public static function forUser(string $tenantId, string $userId): self
	{
		return self::firstOrCreate(
			['tenant_id' => $tenantId, 'user_id' => $userId],
			[
				'digest_enabled' => true,
				'instant_critical_enabled' => true,
				'unsubscribe_token' => (string) Str::uuid(),
			],
		);
	}
}
