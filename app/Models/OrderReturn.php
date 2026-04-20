<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $order_id
 * @property int|null $return_weight_g
 * @property Carbon|null $returned_at
 * @property string|null $carrier
 * @property string|null $tracking_code
 * @property string|null $video_uri
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Order $order
 * @property Tenant $tenant
 */
class OrderReturn extends Model
{
	protected $table = 'returns';
	public $incrementing = false;
	protected $keyType = 'string';

	protected $casts = [
		'return_weight_g' => 'int',
		'returned_at' => 'datetime',
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'return_weight_g',
		'returned_at',
		'carrier',
		'tracking_code',
		'video_uri',
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
