<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Shipment
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $order_id
 * @property int|null $packed_weight_g
 * @property string|null $box_id
 * @property Carbon|null $packed_at
 * @property string|null $carrier
 * @property string|null $tracking_code
 * @property string|null $video_uri
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Order $order
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class Shipment extends Model
{
	protected $table = 'shipments';
	public $incrementing = false;

	protected $casts = [
		'packed_weight_g' => 'int',
		'packed_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'packed_weight_g',
		'box_id',
		'packed_at',
		'carrier',
		'tracking_code',
		'video_uri'
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
