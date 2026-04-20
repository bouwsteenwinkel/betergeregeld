<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LegoElementMap
 * 
 * @property int $element_id
 * @property string|null $part_num
 * @property string|null $rb_part_num
 * @property int|null $rb_color_id
 * @property int|null $bl_color_id
 * @property string|null $color_name
 * @property string|null $rb_name
 * @property string|null $rb_part_img_url
 * @property string|null $rb_part_img_local
 * @property Carbon|null $rb_cached_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $rb_color_name
 *
 * @package App\Models
 */
class LegoElementMap extends Model
{
	protected $table = 'lego_element_map';
	protected $primaryKey = 'element_id';
	public $incrementing = false;

	protected $casts = [
		'element_id' => 'int',
		'rb_color_id' => 'int',
		'bl_color_id' => 'int',
		'rb_cached_at' => 'datetime'
	];

	protected $fillable = [
		'part_num',
		'rb_part_num',
		'rb_color_id',
		'bl_color_id',
		'color_name',
		'rb_name',
		'rb_part_img_url',
		'rb_part_img_local',
		'rb_cached_at',
		'rb_color_name'
	];
}
