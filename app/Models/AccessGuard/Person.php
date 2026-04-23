<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'accessguard_people';

	protected $fillable = [
		'tenant_id', 'type', 'first_name', 'middle_name', 'last_name',
		'email', 'phone', 'job_title', 'department', 'status',
		'start_date', 'end_date', 'notes',
		'external_source', 'external_id', 'last_sign_in_at', 'last_synced_at',
	];

	protected function casts(): array
	{
		return [
			'start_date' => 'date',
			'end_date' => 'date',
			'last_sign_in_at' => 'datetime',
			'last_synced_at' => 'datetime',
		];
	}

	public function cells(): HasMany
	{
		return $this->hasMany(AccessCell::class, 'person_id');
	}

	public function getFullNameAttribute(): string
	{
		return trim(implode(' ', array_filter([
			$this->first_name, $this->middle_name, $this->last_name,
		])));
	}
}
