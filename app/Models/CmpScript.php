<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpScript
 * 
 * @property int $id
 * @property string $tenant_key
 * @property string $category_key
 * @property string $name
 * @property string $script_type
 * @property string|null $src_url
 * @property string|null $inline_code
 * @property array|null $attributes_json
 * @property bool $is_enabled
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmpScript extends Model
{
	protected $table = 'cmp_scripts';

	protected $casts = [
		'attributes_json' => 'json',
		'is_enabled' => 'bool',
		'sort_order' => 'int'
	];

	protected $fillable = [
		'tenant_key',
		'category_key',
		'name',
		'script_type',
		'src_url',
		'inline_code',
		'attributes_json',
		'is_enabled',
		'sort_order'
	];
}
