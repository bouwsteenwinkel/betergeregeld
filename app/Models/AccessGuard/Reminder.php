<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
	public const KINDS = [
		'cycle_due', 'process_due', 'action_overdue',
		'person_starting', 'person_leaving',
	];
	public const STATUSES = ['open', 'done', 'dismissed'];

	protected $table = 'accessguard_reminders';

	protected $fillable = [
		'tenant_id', 'kind', 'subject_type', 'subject_id',
		'title', 'description', 'due_at', 'status',
		'dismissed_at', 'dismissed_by_user_id',
	];

	protected function casts(): array
	{
		return [
			'due_at' => 'date',
			'dismissed_at' => 'datetime',
		];
	}
}
