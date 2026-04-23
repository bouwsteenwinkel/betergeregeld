<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
	public const STATUSES = ['planned', 'active', 'completed', 'cancelled'];
	public const SCOPES = ['all', 'active_people'];

	protected $table = 'accessguard_review_cycles';

	protected $fillable = [
		'tenant_id', 'created_by_user_id', 'title', 'scope', 'status',
		'starts_at', 'due_at', 'completed_at', 'notes',
	];

	protected function casts(): array
	{
		return [
			'starts_at' => 'date',
			'due_at' => 'date',
			'completed_at' => 'datetime',
		];
	}

	public function items(): HasMany
	{
		return $this->hasMany(CycleItem::class, 'cycle_id');
	}

	public function actions(): HasMany
	{
		return $this->hasMany(CycleAction::class, 'cycle_id');
	}

	public function isOpen(): bool
	{
		return in_array($this->status, ['planned', 'active'], true);
	}
}
