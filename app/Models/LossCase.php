<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LossCase
 * 
 * @property int $id
 * @property string $tenant_id
 * @property int $order_id
 * @property string $case_type
 * @property string $status
 * @property int|null $delta_g
 * @property int|null $threshold_g
 * @property int $severity
 * @property array|null $meta_json
 * @property Carbon $last_seen_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property LossOrder $loss_order
 *
 * @package App\Models
 */
class LossCase extends Model
{
	protected $table = 'loss_cases';

	protected $casts = [
		'order_id' => 'int',
		'delta_g' => 'int',
		'threshold_g' => 'int',
		'severity' => 'int',
		'meta_json' => 'json',
		'last_seen_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'case_type',
		'status',
		'delta_g',
		'threshold_g',
		'severity',
		'meta_json',
		'last_seen_at'
	];

	public function loss_order()
	{
		return $this->belongsTo(LossOrder::class, 'order_id');
	}
}
