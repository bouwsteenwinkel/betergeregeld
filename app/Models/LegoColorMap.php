<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class LegoColorMap
 * 
 * @property int $bl_color_id
 * @property int $rb_color_id
 *
 * @package App\Models
 */
class LegoColorMap extends Model
{
	protected $table = 'lego_color_map';
	protected $primaryKey = 'bl_color_id';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'bl_color_id' => 'int',
		'rb_color_id' => 'int'
	];

	protected $fillable = [
		'rb_color_id'
	];
}
