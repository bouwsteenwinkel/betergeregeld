<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessCell extends Model
{
	use HasFactory;

	public const STATES = ['unknown', 'has_access', 'no_access', 'needs_review'];

	protected $table = 'accessguard_access_cells';

	protected $fillable = [
		'tenant_id', 'person_id', 'system_id',
		'access_state', 'access_level', 'account_identifier',
		'last_verified_at', 'notes',
	];

	protected function casts(): array
	{
		return [
			'last_verified_at' => 'datetime',
		];
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class, 'person_id');
	}

	public function system(): BelongsTo
	{
		return $this->belongsTo(BusinessSystem::class, 'system_id');
	}
}
