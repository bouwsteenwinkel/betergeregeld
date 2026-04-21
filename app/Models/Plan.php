<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Plan
 * 
 * @property int $id
 * @property string $plan_key
 * @property string $name
 * @property float $price_monthly
 * @property float|null $price_yearly
 * @property int $trial_days
 * @property bool $is_active
 * @property Carbon|null $created_at
 * 
 * @property Collection|PlanFeature[] $plan_features
 * @property Collection|TenantSubscription[] $tenant_subscriptions
 *
 * @package App\Models
 */
class Plan extends Model
{
	protected $table = 'plans';
	public $timestamps = false;

	protected $casts = [
		'price_monthly' => 'float',
		'price_yearly' => 'float',
		'trial_days' => 'int',
		'is_active' => 'bool',
		'sort_order' => 'int',
	];

	protected $fillable = [
		'product',
		'plan_key',
		'name',
		'price_monthly',
		'price_yearly',
		'trial_days',
		'is_active',
		'sort_order',
	];

	public function scopeProduct($q, string $product)
	{
		return $q->where('product', $product);
	}

	public function plan_features()
	{
		return $this->hasMany(PlanFeature::class);
	}

	public function tenant_subscriptions()
	{
		return $this->hasMany(TenantSubscription::class);
	}
}
