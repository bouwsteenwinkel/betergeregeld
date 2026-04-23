<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class ReviewLogEntry extends Model
{
	protected $table = 'accessguard_review_log';

	public $timestamps = false;

	protected $fillable = [
		'tenant_id', 'cycle_id', 'review_item_id', 'review_action_id',
		'person_id', 'system_id',
		'actor_user_id', 'event', 'payload', 'created_at',
	];

	protected function casts(): array
	{
		return [
			'payload' => 'array',
			'created_at' => 'datetime',
		];
	}
}
