<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpCategory
 * 
 * @property int $id
 * @property string $tenant_key
 * @property string $key
 * @property bool $is_required
 * @property bool $is_enabled
 * @property int $sort_order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmpCategory extends Model
{
	protected $table = 'cmp_categories';

	protected $casts = [
		'is_required' => 'bool',
		'is_enabled' => 'bool',
		'sort_order' => 'int'
	];

	protected $fillable = [
		'tenant_key',
		'key',
		'is_required',
		'is_enabled',
		'sort_order'
	];
}
