<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $external_id
 * @property string $platform
 * @property string $currency
 * @property float $gross_amount
 * @property string|null $customer_ref
 * @property string|null $country_ship
 * @property string $status
 * @property Carbon|null $ordered_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Tenant $tenant
 * @property Collection|Case[] $cases
 * @property Collection|Payment[] $payments
 * @property Collection|Return[] $returns
 * @property Collection|Shipment[] $shipments
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';
	public $incrementing = false;

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
		'customer_ref',
		'country_ship',
		'status',
		'ordered_at'
	];

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}

	public function cases()
	{
		return $this->hasMany(SupportCase::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}

	public function returns()
	{
		return $this->hasMany(OrderReturn::class);
	}

	public function shipments()
	{
		return $this->hasMany(Shipment::class);
	}
}
