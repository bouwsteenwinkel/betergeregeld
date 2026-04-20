<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TenantSubscription
 * 
 * @property int $id
 * @property string $tenant_key
 * @property int $plan_id
 * @property string $status
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_ends_at
 * @property Carbon|null $created_at
 * 
 * @property Plan $plan
 *
 * @package App\Models
 */
class TenantSubscription extends Model
{
	protected $table = 'tenant_subscriptions';
	public $timestamps = false;

	protected $casts = [
		'plan_id' => 'int',
		'trial_ends_at' => 'datetime',
		'current_period_ends_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_key',
		'plan_id',
		'status',
		'trial_ends_at',
		'current_period_ends_at'
	];

	public function plan()
	{
		return $this->belongsTo(Plan::class);
	}
}
