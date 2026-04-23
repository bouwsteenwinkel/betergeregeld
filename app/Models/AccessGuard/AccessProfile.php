<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessProfile extends Model
{
	protected $table = 'accessguard_access_profiles';

	protected $fillable = ['tenant_id', 'name', 'description', 'is_active'];

	protected function casts(): array
	{
		return ['is_active' => 'boolean'];
	}

	public function items(): HasMany
	{
		return $this->hasMany(ProfileItem::class, 'profile_id');
	}
}
