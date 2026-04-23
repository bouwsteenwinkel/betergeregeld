<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Process extends Model
{
	public const KINDS = ['onboarding', 'offboarding'];
	public const STATUSES = ['active', 'completed', 'cancelled'];

	protected $table = 'accessguard_processes';

	protected $fillable = [
		'tenant_id', 'person_id', 'kind', 'status', 'title',
		'created_by_user_id', 'started_at', 'due_at', 'completed_at', 'notes',
	];

	protected function casts(): array
	{
		return [
			'started_at' => 'date',
			'due_at' => 'date',
			'completed_at' => 'datetime',
		];
	}

	public function person(): BelongsTo
	{
		return $this->belongsTo(Person::class, 'person_id');
	}

	public function items(): HasMany
	{
		return $this->hasMany(ProcessItem::class, 'process_id')->orderBy('sort_order');
	}

	public function isOpen(): bool
	{
		return $this->status === 'active';
	}
}
