<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlanFeature
 * 
 * @property int $id
 * @property int $plan_id
 * @property string $feature_key
 * @property string|null $value
 * 
 * @property Plan $plan
 *
 * @package App\Models
 */
class PlanFeature extends Model
{
	protected $table = 'plan_features';
	public $timestamps = false;

	protected $casts = [
		'plan_id' => 'int'
	];

	protected $fillable = [
		'plan_id',
		'feature_key',
		'value'
	];

	public function plan()
	{
		return $this->belongsTo(Plan::class);
	}
}
