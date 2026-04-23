<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileItem extends Model
{
	protected $table = 'accessguard_profile_items';

	protected $fillable = [
		'tenant_id', 'profile_id', 'system_id', 'access_item_id', 'default_state',
	];

	public function profile(): BelongsTo
	{
		return $this->belongsTo(AccessProfile::class, 'profile_id');
	}

	public function system(): BelongsTo
	{
		return $this->belongsTo(BusinessSystem::class, 'system_id');
	}

	public function accessItem(): BelongsTo
	{
		return $this->belongsTo(AccessItem::class, 'access_item_id');
	}
}
