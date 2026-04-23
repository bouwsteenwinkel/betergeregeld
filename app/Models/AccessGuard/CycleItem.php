<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleItem extends Model
{
	public const DECISIONS = ['keep', 'revoke', 'change'];

	protected $table = 'accessguard_review_items';

	protected $fillable = [
		'tenant_id', 'cycle_id', 'person_id', 'system_id', 'access_item_id',
		'person_label', 'system_label', 'snapshot_item_name',
		'snapshot_state', 'snapshot_level', 'snapshot_identifier',
		'decision', 'decision_note', 'decided_by_user_id', 'decided_at',
	];

	protected function casts(): array
	{
		return [
			'decided_at' => 'datetime',
		];
	}

	public function cycle(): BelongsTo
	{
		return $this->belongsTo(Cycle::class, 'cycle_id');
	}
}
