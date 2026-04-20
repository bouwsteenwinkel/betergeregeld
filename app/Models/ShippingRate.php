<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ShippingRate
 * 
 * @property int $id
 * @property string $carrier
 * @property string $service
 * @property string $zone
 * @property int $weight_from_g
 * @property int $weight_to_g
 * @property float $price_ex
 * @property string $currency
 * @property float $handling_ex
 * @property bool $active
 * @property int $sort_order
 * @property string|null $source_label
 * @property string|null $source_url
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_to
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ShippingRate extends Model
{
	protected $table = 'shipping_rates';

	protected $casts = [
		'weight_from_g' => 'int',
		'weight_to_g' => 'int',
		'price_ex' => 'float',
		'handling_ex' => 'float',
		'active' => 'bool',
		'sort_order' => 'int',
		'valid_from' => 'datetime',
		'valid_to' => 'datetime'
	];

	protected $fillable = [
		'carrier',
		'service',
		'zone',
		'weight_from_g',
		'weight_to_g',
		'price_ex',
		'currency',
		'handling_ex',
		'active',
		'sort_order',
		'source_label',
		'source_url',
		'valid_from',
		'valid_to'
	];
}
