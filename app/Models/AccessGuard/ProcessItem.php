<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProcessItem extends Model
{
	public const STATUSES = ['todo', 'in_progress', 'done', 'blocked', 'na'];

	protected $table = 'accessguard_process_items';

	protected $fillable = [
		'tenant_id', 'process_id', 'title', 'description',
		'status', 'status_reason', 'sort_order', 'requires_evidence',
		'completed_at', 'completed_by_user_id',
	];

	protected function casts(): array
	{
		return [
			'requires_evidence' => 'boolean',
			'completed_at' => 'datetime',
		];
	}

	public function process(): BelongsTo
	{
		return $this->belongsTo(Process::class, 'process_id');
	}

	public function evidence(): HasMany
	{
		return $this->hasMany(ProcessEvidence::class, 'process_item_id')->orderBy('uploaded_at');
	}
}
