<?php

namespace App\Models\AccessGuard;

use Illuminate\Database\Eloquent\Model;

class RiskFlag extends Model
{
	public const KINDS = [
		'stale_admin',
		'orphan_access',
		'excessive_access',
		'overdue_review',
		'overdue_action',
		'pending_onboarding',
		'stale_credential',
		'dormant_account',
		'admin_group_creep',
	];
	public const STATUSES = ['open', 'acknowledged', 'resolved'];

	protected $table = 'accessguard_risk_flags';

	protected $fillable = [
		'tenant_id', 'kind', 'severity',
		'subject_type', 'subject_id',
		'title', 'description', 'status',
		'detected_at', 'acknowledged_at', 'acknowledged_by_user_id',
		'resolved_at', 'resolved_by_user_id', 'payload',
	];

	protected function casts(): array
	{
		return [
			'severity' => 'integer',
			'detected_at' => 'datetime',
			'acknowledged_at' => 'datetime',
			'resolved_at' => 'datetime',
			'payload' => 'array',
		];
	}
}
