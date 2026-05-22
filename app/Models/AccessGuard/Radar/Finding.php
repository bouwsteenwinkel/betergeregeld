<?php

namespace App\Models\AccessGuard\Radar;

use App\Models\AccessGuard\RiskFlag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The workhorse table: one row per (asset, software, vulnerability) triple.
 * Carries the workflow state (new / confirmed / patched / …) plus the
 * tenant-specific risk_score and risk_factors used to prioritise triage.
 *
 * When risk_score >= 80 a RiskFlag is upserted (kind='vulnerability') so the
 * finding surfaces in AccessGuard's unified risk dashboard and fires the
 * instant-alert mail.
 */
class Finding extends Model
{
	public const STATUSES = [
		'new', 'confirmed',
		'accepted_risk', 'planned', 'in_progress',
		'patched', 'mitigated', 'ignored',
		'false_positive', 'resolved', 'reopened',
	];
	public const SEVERITIES = ['low', 'medium', 'high', 'critical'];
	public const CHECK_TYPES = ['cve', 'security_headers', 'tls', 'cookies', 'cmp'];

	protected $table = 'accessguard_radar_findings';

	protected $fillable = [
		'tenant_id', 'asset_id', 'software_instance_id', 'vulnerability_id',
		'check_type', 'title', 'detail', 'finding_key',
		'risk_score', 'risk_factors', 'severity', 'status',
		'assigned_to_user_id', 'due_date',
		'accepted_by_user_id', 'accepted_until',
		'resolution_notes', 'last_verified_at', 'proof_of_fix',
		'linked_ticket_id', 'risk_flag_id', 'ai_explanation_cache_key',
		'first_detected_at', 'last_detected_at', 'resolved_at',
	];

	protected function casts(): array
	{
		return [
			'risk_factors' => 'array',
			'due_date' => 'date',
			'accepted_until' => 'date',
			'last_verified_at' => 'datetime',
			'first_detected_at' => 'datetime',
			'last_detected_at' => 'datetime',
			'resolved_at' => 'datetime',
		];
	}

	public function asset(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'asset_id');
	}

	public function softwareInstance(): BelongsTo
	{
		return $this->belongsTo(SoftwareInstance::class, 'software_instance_id');
	}

	public function vulnerability(): BelongsTo
	{
		return $this->belongsTo(Vulnerability::class, 'vulnerability_id');
	}

	public function riskFlag(): BelongsTo
	{
		return $this->belongsTo(RiskFlag::class, 'risk_flag_id');
	}

	public static function severityForScore(int $score): string
	{
		return match (true) {
			$score >= 81 => 'critical',
			$score >= 51 => 'high',
			$score >= 21 => 'medium',
			default => 'low',
		};
	}
}
