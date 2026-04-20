<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LegoRbColor
 * 
 * @property int $rb_color_id
 * @property string $name
 * @property string|null $rgb
 * @property bool|null $is_trans
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class LegoRbColor extends Model
{
	protected $table = 'lego_rb_colors';
	protected $primaryKey = 'rb_color_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'rb_color_id' => 'int',
		'is_trans' => 'bool'
	];

	protected $fillable = [
		'name',
		'rgb',
		'is_trans'
	];
}
