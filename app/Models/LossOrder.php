<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LossOrder
 * 
 * @property int $id
 * @property string $tenant_id
 * @property string $external_id
 * @property string $platform
 * @property string $currency
 * @property float $gross_amount
 * @property Carbon|null $ordered_at
 * @property string|null $customer_ref
 * @property string|null $country_ship
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|LossCase[] $loss_cases
 * @property Collection|LossReturn[] $loss_returns
 * @property Collection|LossShipment[] $loss_shipments
 *
 * @package App\Models
 */
class LossOrder extends Model
{
	protected $table = 'loss_orders';

	protected $casts = [
		'gross_amount' => 'float',
		'ordered_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'external_id',
		'platform',
		'currency',
		'gross_amount',
		'ordered_at',
		'customer_ref',
		'country_ship'
	];

	public function loss_cases()
	{
		return $this->hasMany(LossCase::class, 'order_id');
	}

	public function loss_returns()
	{
		return $this->hasMany(LossReturn::class, 'order_id');
	}

	public function loss_shipments()
	{
		return $this->hasMany(LossShipment::class, 'order_id');
	}
}
