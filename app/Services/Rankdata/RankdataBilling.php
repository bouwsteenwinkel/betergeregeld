<?php

namespace App\Services\Rankdata;

use App\Models\Agency;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

/**
 * Rankdata-prijsberekening: per klant = basis (plan.price_monthly) + per extra
 * site boven included_sites (plan.price_per_site), minus korting. De korting is
 * het bureau-override (agencies.discount_percent) of anders die van het plan.
 * Het bureau-maandtotaal is de som van z'n actieve klanten.
 */
class RankdataBilling
{
	/** Het standaard actieve Rankdata-plan (laagste sort_order). */
	public function defaultPlan(): ?Plan
	{
		return Plan::query()->where('product', 'rankdata')->where('is_active', true)
			->orderBy('sort_order')->first();
	}

	/** Het geldende plan voor een bureau (eigen plan, anders het standaard). */
	public function planFor(?Agency $agency): ?Plan
	{
		if ($agency && $agency->rankdata_plan_id && $agency->rankdataPlan) {
			return $agency->rankdataPlan;
		}

		return $this->defaultPlan();
	}

	/**
	 * Maandkosten voor één klant, met opbouw.
	 *
	 * @return array{plan:?string,sites:int,base:float,addon:float,subtotal:float,discount_percent:float,discount:float,total:float}
	 */
	public function costForTenant(Tenant $tenant, ?Agency $agency = null): array
	{
		$agency ??= $tenant->agency;
		$plan = $this->planFor($agency);
		$sites = (int) DB::table('seo_properties')->where('tenant_id', $tenant->id)->where('is_active', 1)->count();

		if (! $plan) {
			return ['plan' => null, 'sites' => $sites, 'base' => 0.0, 'addon' => 0.0,
				'subtotal' => 0.0, 'discount_percent' => 0.0, 'discount' => 0.0, 'total' => 0.0];
		}

		$base = (float) $plan->price_monthly;
		$extraSites = max(0, $sites - (int) ($plan->included_sites ?: 1));
		$addon = $extraSites * (float) ($plan->price_per_site ?: 0);
		$subtotal = $base + $addon;

		$discountPct = $agency?->discount_percent ?? (float) ($plan->discount_percent ?: 0);
		$discount = round($subtotal * $discountPct / 100, 2);

		return [
			'plan'             => $plan->name,
			'sites'            => $sites,
			'base'             => round($base, 2),
			'addon'            => round($addon, 2),
			'subtotal'         => round($subtotal, 2),
			'discount_percent' => (float) $discountPct,
			'discount'         => $discount,
			'total'            => round($subtotal - $discount, 2),
		];
	}

	/**
	 * Maandtotaal voor een bureau = som van de actieve klanten.
	 *
	 * @return array{plan:?string,clients:int,total:float,lines:array<int,array{tenant:string,sites:int,total:float}>}
	 */
	public function costForAgency(Agency $agency): array
	{
		$lines = [];
		$total = 0.0;
		foreach ($agency->tenants()->where('is_active', true)->orderBy('name')->get() as $tenant) {
			$c = $this->costForTenant($tenant, $agency);
			$lines[] = ['tenant' => (string) $tenant->name, 'sites' => $c['sites'], 'total' => $c['total']];
			$total += $c['total'];
		}

		return [
			'plan'    => $this->planFor($agency)?->name,
			'clients' => count($lines),
			'total'   => round($total, 2),
			'lines'   => $lines,
		];
	}
}
