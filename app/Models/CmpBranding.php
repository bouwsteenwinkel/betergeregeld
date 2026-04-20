<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpBranding
 * 
 * @property int $id
 * @property string $tenant_key
 * @property array $branding_json
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmpBranding extends Model
{
	protected $table = 'cmp_branding';
	public $timestamps = false;

	protected $casts = [
		'branding_json' => 'json'
	];

	protected $fillable = [
		'tenant_key',
		'branding_json'
	];
}
