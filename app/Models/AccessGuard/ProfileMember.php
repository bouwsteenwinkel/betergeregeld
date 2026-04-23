<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileMember extends Model
{
	public const SOURCES = ['manual', 'm365', 'google'];

	protected $table = 'accessguard_profile_members';

	protected $fillable = [
		'tenant_id', 'profile_id', 'person_id', 'source', 'last_synced_at',
	];

	protected function casts(): array
	{
		return ['last_synced_at' => 'datetime'];
	}

	public function profile(): BelongsTo
	{
		return $this->belongsTo(AccessProfile::class, 'profile_id');
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class, 'person_id');
	}
}
