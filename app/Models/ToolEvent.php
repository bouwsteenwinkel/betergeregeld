<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ToolEvent
 * 
 * @property int $id
 * @property string $tool
 * @property string $event
 * @property string $session_id
 * @property array|null $meta_json
 * @property Carbon $created_at
 *
 * @package App\Models
 */
class ToolEvent extends Model
{
	protected $table = 'tool_events';
	public $timestamps = false;

	protected $casts = [
		'meta_json' => 'json'
	];

	protected $fillable = [
		'tool',
		'event',
		'session_id',
		'meta_json'
	];
}
