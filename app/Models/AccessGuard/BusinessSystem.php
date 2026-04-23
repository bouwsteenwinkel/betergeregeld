<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Named BusinessSystem (not "System") because PHP reserves System\* in
 * some auto-load patterns and to avoid confusing it with SystemsModel
 * from the V1 schema.
 */
class BusinessSystem extends Model
{
	use HasFactory;

	protected $table = 'accessguard_systems';

	protected $fillable = [
		'tenant_id', 'name', 'category', 'is_active', 'notes',
	];

	protected function casts(): array
	{
		return [
			'is_active' => 'boolean',
		];
	}

	public function cells(): HasMany
	{
		return $this->hasMany(AccessCell::class, 'system_id');
	}
}
