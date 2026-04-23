<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonAccessItem extends Model
{
	public const STATES = ['unknown', 'has_access', 'no_access', 'needs_review'];

	protected $table = 'accessguard_person_access_items';

	protected $fillable = [
		'tenant_id', 'person_id', 'system_id', 'access_item_id',
		'access_state', 'access_level', 'account_identifier',
		'last_verified_at', 'notes',
	];

	protected function casts(): array
	{
		return ['last_verified_at' => 'datetime'];
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class, 'person_id');
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
