<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\Features\FeatureResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
	public function __construct(private readonly FeatureResolver $resolver) {}

	public function show(Request $request, string $locale): View
	{
		$plans = Plan::query()
			->product('tools')
			->where('is_active', true)
			->orderBy('sort_order')
			->with('plan_features')
			->get()
			->map(fn (Plan $p) => [
				'id' => $p->id,
				'key' => $p->plan_key,
				'name' => $p->name,
				'price_monthly' => (float) $p->price_monthly,
				'price_yearly' => (float) $p->price_yearly,
				'trial_days' => (int) $p->trial_days,
				'features' => $p->plan_features->pluck('value', 'feature_key')->all(),
			])
			->values()
			->all();

		$currentPlanKey = $request->user()
			? $this->resolver->forUser($request->user())->plan->plan_key
			: null;

		return view('pages.pricing', [
			'plans' => $plans,
			'currentPlanKey' => $currentPlanKey,
		]);
	}
}
