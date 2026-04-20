<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpPolicy
 * 
 * @property int $id
 * @property string $tenant_key
 * @property int $policy_version
 * @property string $config_hash
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmpPolicy extends Model
{
	protected $table = 'cmp_policy';
	public $timestamps = false;

	protected $casts = [
		'policy_version' => 'int'
	];

	protected $fillable = [
		'tenant_key',
		'policy_version',
		'config_hash'
	];
}
