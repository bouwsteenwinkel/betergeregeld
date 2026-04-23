<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleAction extends Model
{
	public const STATUSES = ['open', 'done', 'cancelled'];
	public const KINDS = ['revoke_access', 'review_level'];

	protected $table = 'accessguard_review_actions';

	protected $fillable = [
		'tenant_id', 'cycle_id', 'review_item_id', 'process_id',
		'person_id', 'system_id', 'kind', 'title', 'status',
		'assigned_to_user_id', 'due_at', 'completed_at', 'note',
	];

	protected function casts(): array
	{
		return [
			'due_at' => 'date',
			'completed_at' => 'datetime',
		];
	}

	public function cycle(): BelongsTo
	{
		return $this->belongsTo(Cycle::class, 'cycle_id');
	}

	public function item(): BelongsTo
	{
		return $this->belongsTo(CycleItem::class, 'review_item_id');
	}
}
