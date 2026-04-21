<?php

namespace App\Services\Features;

use App\Models\Plan;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class FeatureResolver
{
	private const CACHE_TTL = 300;

	public function __construct(private readonly string $product = 'tools') {}

	/**
	 * Resolve features for an authenticated user (via their tenant subscription).
	 * Falls back to the product's free plan if no active subscription is found.
	 */
	public function forUser(User $user): FeatureBag
	{
		$plan = $this->planForTenant($user->tenant_id);
		return $this->bagForPlan($plan);
	}

	/**
	 * Resolve features for an anonymous visitor (always the free plan).
	 */
	public function anonymous(): FeatureBag
	{
		return $this->bagForPlan($this->freePlan());
	}

	public function forRequest(?User $user): FeatureBag
	{
		return $user ? $this->forUser($user) : $this->anonymous();
	}

	private function planForTenant(?string $tenantId): Plan
	{
		if (! $tenantId) {
			return $this->freePlan();
		}

		$sub = TenantSubscription::query()
			->where('tenant_key', $tenantId)
			->whereIn('status', ['active', 'trial', 'trialing'])
			->whereHas('plan', fn ($q) => $q->where('product', $this->product)->where('is_active', true))
			->orderByDesc('id')
			->with('plan')
			->first();

		if ($sub && $sub->plan) {
			return $sub->plan;
		}

		return $this->freePlan();
	}

	private function freePlan(): Plan
	{
		return Plan::query()
			->where('product', $this->product)
			->where('plan_key', $this->product . '_free')
			->where('is_active', true)
			->firstOrFail();
	}

	private function bagForPlan(Plan $plan): FeatureBag
	{
		$features = Cache::remember(
			"features:plan:{$plan->id}",
			self::CACHE_TTL,
			fn () => $plan->plan_features()->pluck('value', 'feature_key')->all(),
		);

		return new FeatureBag($plan, is_array($features) ? $features : []);
	}

	public static function forget(int $planId): void
	{
		Cache::forget("features:plan:{$planId}");
	}
}
