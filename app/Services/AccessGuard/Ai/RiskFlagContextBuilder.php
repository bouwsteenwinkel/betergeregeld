<?php

namespace App\Services\AccessGuard\Ai;

use App\Models\AccessGuard\RiskFlag;

/**
 * Builds the "what should the LLM know?" payload for a single RiskFlag
 * without leaking secrets. We serialize only the risk's own fields plus
 * the kind-specific payload — no user emails, no vault content, no
 * free-form notes from the actor.
 */
class RiskFlagContextBuilder
{
	private const KIND_EXPLANATION = [
		'stale_admin' => 'An admin-type access item where the last verification is older than 90 days. Privileged access needs regular review to catch orphaned or forgotten grants.',
		'orphan_access' => 'A person is marked inactive in HR but still has access granted somewhere in the matrix. This is a GDPR + security issue.',
		'excessive_access' => 'A single person has has_access on many systems (default threshold: 10). Often a sign of role creep or shared account usage.',
		'overdue_review' => 'A review cycle is past its due date without being completed. The organisation commits to a review cadence for audit/compliance.',
		'overdue_action' => 'An open action (revoke / review) has been open for >14 days. Review decisions must be followed through promptly.',
		'pending_onboarding' => 'A person is scheduled to start but no onboarding process is active yet. Risk of the person starting without proper access/IT setup.',
		'stale_credential' => 'A stored credential has passed its expires_at or its rotation interval. Password age is a key control; overdue credentials should be rotated.',
	];

	public function build(RiskFlag $flag): array
	{
		return [
			'kind' => $flag->kind,
			'kind_context' => self::KIND_EXPLANATION[$flag->kind] ?? 'An AccessGuard risk detected by the scheduled scanner.',
			'severity' => $flag->severity,
			'title' => $flag->title,
			'description' => $flag->description,
			'detected_at' => $flag->detected_at?->toIso8601String(),
			'payload' => $this->redact($flag->payload ?? []),
		];
	}

	/**
	 * Strip any personally identifying free-form fields from the payload.
	 * Kind-specific payloads only contain counts and ids we're willing to
	 * send upstream, but we're paranoid as a policy.
	 */
	private function redact(array $payload): array
	{
		$allowed = [
			'person_id', 'access_item_id', 'system_id', 'credential_id',
			'overdue_days', 'due_at', 'start_date', 'last_verified_at',
			'has_access_cells', 'has_access_items', 'systems_count',
			'threshold', 'age_days', 'kind',
			'expired', 'rotation_due',
		];
		return array_intersect_key($payload, array_flip($allowed));
	}
}
