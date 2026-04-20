<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $order_id
 * @property string $provider
 * @property string|null $provider_ref
 * @property string $status
 * @property float $gross
 * @property float $fee
 * @property float $net
 * @property Carbon|null $paid_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Order $order
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class Payment extends Model
{
	protected $table = 'payments';
	public $incrementing = false;

	protected $casts = [
		'gross' => 'float',
		'fee' => 'float',
		'net' => 'float',
		'paid_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'provider',
		'provider_ref',
		'status',
		'gross',
		'fee',
		'net',
		'paid_at'
	];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}
}
