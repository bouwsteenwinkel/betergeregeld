<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LossTenantSetting
 * 
 * @property string $tenant_id
 * @property bool $has_orders
 * @property bool $has_packed_weight
 * @property bool $has_return_weight
 * @property bool $has_shipments
 * @property bool $has_payments
 * @property int $packed_missing_after_hours
 * @property bool $packed_outlier_enabled
 * @property float $packed_outlier_low_ratio
 * @property float $packed_outlier_high_ratio
 * @property int $packed_outlier_min_median_g
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @package App\Models
 */
class LossTenantSetting extends Model
{
	protected $table = 'loss_tenant_settings';
	protected $primaryKey = 'tenant_id';
	public $incrementing = false;

	protected $casts = [
		'has_orders' => 'bool',
		'has_packed_weight' => 'bool',
		'has_return_weight' => 'bool',
		'has_shipments' => 'bool',
		'has_payments' => 'bool',
		'packed_missing_after_hours' => 'int',
		'packed_outlier_enabled' => 'bool',
		'packed_outlier_low_ratio' => 'float',
		'packed_outlier_high_ratio' => 'float',
		'packed_outlier_min_median_g' => 'int'
	];

	protected $fillable = [
		'has_orders',
		'has_packed_weight',
		'has_return_weight',
		'has_shipments',
		'has_payments',
		'packed_missing_after_hours',
		'packed_outlier_enabled',
		'packed_outlier_low_ratio',
		'packed_outlier_high_ratio',
		'packed_outlier_min_median_g'
	];
}
