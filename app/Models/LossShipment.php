<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LossShipment
 * 
 * @property int $id
 * @property string $tenant_id
 * @property int $order_id
 * @property int $packed_weight_g
 * @property string|null $box_id
 * @property Carbon $packed_at
 * @property Carbon $updated_at
 * 
 * @property LossOrder $loss_order
 *
 * @package App\Models
 */
class LossShipment extends Model
{
	protected $table = 'loss_shipments';
	public $timestamps = false;

	protected $casts = [
		'order_id' => 'int',
		'packed_weight_g' => 'int',
		'packed_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'packed_weight_g',
		'box_id',
		'packed_at'
	];

	public function loss_order()
	{
		return $this->belongsTo(LossOrder::class, 'order_id');
	}
}
