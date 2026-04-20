<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpText
 * 
 * @property int $id
 * @property string $tenant_key
 * @property string $lang
 * @property string $text_key
 * @property string $text_value
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class CmpText extends Model
{
	protected $table = 'cmp_texts';
	public $timestamps = false;

	protected $fillable = [
		'tenant_key',
		'lang',
		'text_key',
		'text_value'
	];
}
