<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShippingSurcharge
 * 
 * @property int $id
 * @property string $carrier
 * @property string $name
 * @property string $service
 * @property string $zone
 * @property float $amount_ex
 * @property string $currency
 * @property string $rule_text
 * @property bool $active
 * @property int $sort_order
 * @property string $source_label
 * @property string $source_url
 * @property Carbon $valid_from
 * @property Carbon $valid_to
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ShippingSurcharge extends Model
{
	protected $table = 'shipping_surcharges';

	protected $casts = [
		'amount_ex' => 'float',
		'active' => 'bool',
		'sort_order' => 'int',
		'valid_from' => 'datetime',
		'valid_to' => 'datetime'
	];

	protected $fillable = [
		'carrier',
		'name',
		'service',
		'zone',
		'amount_ex',
		'currency',
		'rule_text',
		'active',
		'sort_order',
		'source_label',
		'source_url',
		'valid_from',
		'valid_to'
	];
}
