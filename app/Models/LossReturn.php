<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LossReturn
 * 
 * @property int $id
 * @property string $tenant_id
 * @property int $order_id
 * @property int $return_weight_g
 * @property Carbon $received_at
 * @property Carbon $updated_at
 * 
 * @property LossOrder $loss_order
 *
 * @package App\Models
 */
class LossReturn extends Model
{
	protected $table = 'loss_returns';
	public $timestamps = false;

	protected $casts = [
		'order_id' => 'int',
		'return_weight_g' => 'int',
		'received_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'return_weight_g',
		'received_at'
	];

	public function loss_order()
	{
		return $this->belongsTo(LossOrder::class, 'order_id');
	}
}
