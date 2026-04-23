<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessItem extends Model
{
	public const TYPES = ['role', 'licence', 'account', 'key', 'badge', 'group', 'other'];

	protected $table = 'accessguard_access_items';

	protected $fillable = [
		'tenant_id', 'system_id', 'name', 'type', 'description', 'is_active', 'sort_order',
	];

	protected function casts(): array
	{
		return ['is_active' => 'boolean', 'sort_order' => 'integer'];
	}

	public function system(): BelongsTo
	{
		return $this->belongsTo(BusinessSystem::class, 'system_id');
	}

	public function personAccessItems(): HasMany
	{
		return $this->hasMany(PersonAccessItem::class, 'access_item_id');
	}
}
