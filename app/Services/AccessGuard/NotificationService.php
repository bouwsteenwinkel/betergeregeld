<?php

namespace App\Services\AccessGuard;

use App\Mail\AccessGuardDigest;
use App\Mail\AccessGuardInstantAlert;
use App\Models\AccessGuard\NotificationPref;
use App\Models\AccessGuard\Reminder;
use App\Models\AccessGuard\RiskFlag;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Builds + sends AccessGuard emails. Two flavours:
 *   - digest: batched daily summary per user (08:00)
 *   - instant: sev-5 risk flagged right when it's detected
 */
class NotificationService
{
	/**
	 * Send the daily digest to every eligible user across all tenants.
	 * @return array{sent:int, skipped:int}
	 */
	public function sendDigests(?CarbonImmutable $now = null): array
	{
		$now ??= CarbonImmutable::now();
		$sent = 0; $skipped = 0;

		$users = DB::table('users')
			->where('is_active', 1)
			->whereNotNull('email')
			->get(['id', 'tenant_id', 'email']);

		foreach ($users as $u) {
			$pref = NotificationPref::forUser($u->tenant_id, (string) $u->id);
			if (! $pref->digest_enabled) { $skipped++; continue; }

			// Avoid duplicate digests within 20h window
			if ($pref->last_digest_sent_at && $pref->last_digest_sent_at->greaterThan($now->subHours(20))) {
				$skipped++;
				continue;
			}

			$digest = $this->buildDigestData($u->tenant_id, $now);
			if ($digest['has_content'] === false) { $skipped++; continue; }

			Mail::to($u->email)->send(new AccessGuardDigest(
				tenantId: $u->tenant_id,
				userId: (string) $u->id,
				digest: $digest,
				unsubscribeToken: $pref->unsubscribe_token,
			));

			$pref->update(['last_digest_sent_at' => $now]);
			$sent++;
		}

		return ['sent' => $sent, 'skipped' => $skipped];
	}

	/**
	 * Fire instant-alert emails to every user in a tenant who opted in,
	 * for a freshly-detected sev-5 risk flag.
	 */
	public function dispatchInstantAlert(RiskFlag $flag): int
	{
		if ($flag->severity < 5) return 0;

		$users = DB::table('users')
			->where('tenant_id', $flag->tenant_id)
			->where('is_active', 1)
			->whereNotNull('email')
			->get(['id', 'email']);

		$count = 0;
		foreach ($users as $u) {
			$pref = NotificationPref::forUser($flag->tenant_id, (string) $u->id);
			if (! $pref->instant_critical_enabled) continue;

			try {
				Mail::to($u->email)->send(new AccessGuardInstantAlert(
					tenantId: $flag->tenant_id,
					flag: $flag,
					unsubscribeToken: $pref->unsubscribe_token,
				));
				$count++;
			} catch (\Throwable $e) {
				Log::warning('accessguard: instant alert failed', [
					'tenant' => $flag->tenant_id,
					'user' => $u->id,
					'flag' => $flag->id,
					'error' => $e->getMessage(),
				]);
			}
		}
		return $count;
	}

	private function buildDigestData(string $tenantId, CarbonImmutable $now): array
	{
		$openRisks = RiskFlag::query()
			->where('tenant_id', $tenantId)
			->whereIn('status', ['open'])
			->orderByDesc('severity')
			->orderByDesc('detected_at')
			->limit(10)
			->get();

		$upcomingReminders = Reminder::query()
			->where('tenant_id', $tenantId)
			->where('status', 'open')
			->where(function ($q) use ($now) {
				$q->whereNull('due_at')->orWhere('due_at', '<=', $now->addDays(7)->toDateString());
			})
			->orderBy('due_at')
			->limit(10)
			->get();

		$openActionsCount = DB::table('accessguard_review_actions')
			->where('tenant_id', $tenantId)
			->where('status', 'open')
			->count();

		return [
			'open_risks' => $openRisks,
			'open_risks_count' => RiskFlag::query()->where('tenant_id', $tenantId)->where('status', 'open')->count(),
			'upcoming_reminders' => $upcomingReminders,
			'upcoming_reminders_count' => Reminder::query()->where('tenant_id', $tenantId)->where('status', 'open')->count(),
			'open_actions_count' => $openActionsCount,
			'has_content' => $openRisks->isNotEmpty() || $upcomingReminders->isNotEmpty() || $openActionsCount > 0,
			'tenant_id' => $tenantId,
			'generated_at' => $now,
		];
	}
}
