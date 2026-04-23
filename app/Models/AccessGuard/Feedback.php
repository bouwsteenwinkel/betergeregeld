<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
	public const CATEGORIES = ['bug', 'feature', 'praise', 'question', 'other'];
	public const STATUSES = ['new', 'triaged', 'resolved', 'wontfix'];

	protected $table = 'accessguard_feedback';

	protected $fillable = [
		'tenant_id', 'user_id', 'category', 'message',
		'page_url', 'user_agent', 'status', 'admin_note', 'resolved_at',
	];

	protected function casts(): array
	{
		return ['resolved_at' => 'datetime'];
	}
}
