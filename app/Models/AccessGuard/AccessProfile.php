<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessProfile extends Model
{
	protected $table = 'accessguard_access_profiles';

	protected $fillable = [
		'tenant_id', 'name', 'description', 'is_active',
		'external_source', 'external_id', 'last_synced_at',
	];

	protected function casts(): array
	{
		return [
			'is_active' => 'boolean',
			'last_synced_at' => 'datetime',
		];
	}

	public function items(): HasMany
	{
		return $this->hasMany(ProfileItem::class, 'profile_id');
	}

	public function members(): HasMany
	{
		return $this->hasMany(ProfileMember::class, 'profile_id');
	}
}
