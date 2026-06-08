<?php

namespace App\Services\Billing;

use App\Models\BillingIntent;
use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Services\Features\FeatureResolver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BillingService
{
	public function __construct(
		private readonly MollieGateway $gateway,
		private readonly FeatureResolver $resolver,
	) {}

	/**
	 * Start a 14-day free trial for the user's tenant on the given plan.
	 * Idempotent — upserts the subscription row rather than duplicating.
	 */
	public function startTrial(User $user, string $planKey): TenantSubscription
	{
		$plan = $this->mustPlan($planKey);
		if ($plan->trial_days <= 0) {
			throw new RuntimeException("Plan {$planKey} does not offer a trial");
		}

		$now = CarbonImmutable::now();

		// Starting a trial on a different plan also supersedes any stale
		// active/trial rows — otherwise we'd leave two "active" rows around.
		TenantSubscription::query()
			->where('tenant_key', $user->tenant_id)
			->where('plan_id', '!=', $plan->id)
			->whereIn('status', ['active', 'trial', 'trialing'])
			->update(['status' => 'canceled', 'cancel_at' => $now]);

		return TenantSubscription::updateOrCreate(
			['tenant_key' => $user->tenant_id, 'plan_id' => $plan->id],
			[
				'status' => 'trial',
				'trial_ends_at' => $now->addDays((int) $plan->trial_days),
				'current_period_starts_at' => $now,
				'current_period_ends_at' => $now->addDays((int) $plan->trial_days),
				'seats' => 1,
			],
		);
	}

	/**
	 * Kick off a paid checkout. Creates a BillingIntent + Mollie payment,
	 * returns the Mollie checkout URL that the user should be redirected to.
	 */
	public function startCheckout(User $user, string $planKey, string $period = 'monthly'): BillingIntent
	{
		$plan = $this->mustPlan($planKey);
		$amount = $period === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
		if ($amount <= 0) {
			throw new RuntimeException("Plan {$planKey} has no price for period {$period}");
		}

		$intent = BillingIntent::create([
			'tenant_id' => $user->tenant_id,
			'user_id' => $user->getAuthIdentifier(),
			'plan_id' => $plan->id,
			'period' => $period,
			'amount' => $amount,
			'currency' => 'EUR',
			'status' => 'pending',
		]);

		$description = sprintf(
			'Beter Geregeld %s — %s',
			$plan->name,
			$period === 'yearly' ? 'jaarlijks' : 'maandelijks',
		);

		$result = $this->gateway->createPayment($intent, $description);

		$intent->update([
			'mollie_payment_id' => $result['mollie_payment_id'],
			'mollie_checkout_url' => $result['checkout_url'],
			'status' => 'open',
		]);

		return $intent;
	}

	/**
	 * Rankdata klant-direct: de klant (tenant) betaalt zélf z'n abonnement via
	 * Mollie. Het bedrag is dynamisch (basis + extra sites − korting) i.p.v. een
	 * vaste plan-prijs. Activatie loopt verder via dezelfde syncFromGateway-flow.
	 */
	public function startRankdataCheckout(User $user, \App\Models\Tenant $tenant): BillingIntent
	{
		$billing = app(\App\Services\Rankdata\RankdataBilling::class);
		$plan = $billing->planFor($tenant->agency);
		if (! $plan) {
			throw new RuntimeException('Geen Rankdata-plan ingesteld.');
		}

		$cost = $billing->costForTenant($tenant);
		if ($cost['total'] <= 0) {
			throw new RuntimeException('Geen te factureren bedrag voor deze klant.');
		}

		$intent = BillingIntent::create([
			'tenant_id' => $tenant->id,
			'user_id'   => $user->getAuthIdentifier(),
			'plan_id'   => $plan->id,
			'period'    => 'monthly',
			'amount'    => $cost['total'],
			'currency'  => 'EUR',
			'status'    => 'pending',
		]);

		$description = 'Rankdata-abonnement — ' . $tenant->name
			. ' (' . $cost['sites'] . ' site' . ($cost['sites'] === 1 ? '' : 's') . ')';

		$result = $this->gateway->createPayment($intent, $description);
		$intent->update([
			'mollie_payment_id'   => $result['mollie_payment_id'],
			'mollie_checkout_url' => $result['checkout_url'],
			'status'              => 'open',
		]);

		return $intent;
	}

	/**
	 * Called both by the real Mollie webhook and by /billing/fake-confirm.
	 * Looks the intent up, syncs status from Mollie (or fakes it), and
	 * extends/creates the tenant subscription when paid. Idempotent.
	 */
	public function syncFromGateway(string $molliePaymentId): ?BillingIntent
	{
		$intent = BillingIntent::where('mollie_payment_id', $molliePaymentId)->first();
		if (! $intent) {
			Log::warning('BillingService: webhook for unknown payment', ['id' => $molliePaymentId]);
			return null;
		}

		if ($intent->isPaid()) {
			return $intent;
		}

		$status = $this->gateway->fetchStatus($molliePaymentId);

		return DB::transaction(function () use ($intent, $status) {
			$intent->refresh();
			if ($intent->isPaid()) {
				return $intent;
			}

			$mapped = match ($status) {
				'paid' => 'paid',
				'open', 'pending', 'authorized' => 'open',
				'canceled' => 'cancelled',
				'expired' => 'expired',
				'failed' => 'failed',
				default => 'open',
			};

			$intent->status = $mapped;
			if ($mapped === 'paid') {
				$intent->paid_at = CarbonImmutable::now();
				$this->activateSubscription($intent);
			}
			$intent->save();

			return $intent;
		});
	}

	private function activateSubscription(BillingIntent $intent): TenantSubscription
	{
		$now = CarbonImmutable::now();
		$periodEnds = $intent->period === 'yearly' ? $now->addYear() : $now->addMonth();

		// Supersede any other active/trial subs on a different plan. Without this,
		// a Pro→Business upgrade would leave the Pro row "active" too; that
		// pollutes the table and breaks cancellation downgrade paths.
		TenantSubscription::query()
			->where('tenant_key', $intent->tenant_id)
			->where('plan_id', '!=', $intent->plan_id)
			->whereIn('status', ['active', 'trial', 'trialing'])
			->update(['status' => 'canceled', 'cancel_at' => $now]);

		$sub = TenantSubscription::where('tenant_key', $intent->tenant_id)
			->where('plan_id', $intent->plan_id)
			->first();

		if ($sub) {
			// Extend from either now or from the existing end-date, whichever is later.
			$from = $sub->current_period_ends_at && $sub->current_period_ends_at->isFuture()
				? CarbonImmutable::parse($sub->current_period_ends_at)
				: $now;
			$periodEnds = $intent->period === 'yearly' ? $from->addYear() : $from->addMonth();

			$sub->update([
				'status' => 'active',
				'current_period_starts_at' => $sub->current_period_starts_at ?: $now,
				'current_period_ends_at' => $periodEnds,
				'cancel_at' => null,
			]);
			return $sub;
		}

		return TenantSubscription::create([
			'tenant_key' => $intent->tenant_id,
			'plan_id' => $intent->plan_id,
			'status' => 'active',
			'current_period_starts_at' => $now,
			'current_period_ends_at' => $periodEnds,
			'seats' => 1,
		]);
	}

	private function mustPlan(string $planKey): Plan
	{
		$plan = Plan::query()
			->product('tools')
			->where('plan_key', $planKey)
			->where('is_active', true)
			->first();
		if (! $plan) {
			throw new RuntimeException("Plan not found or inactive: {$planKey}");
		}
		return $plan;
	}
}
