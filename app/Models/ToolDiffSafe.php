<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ToolDiffSafe
 * 
 * @property int $id
 * @property string $user_id
 * @property string $title
 * @property string $left_text
 * @property string $right_text
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ToolDiffSafe extends Model
{
	protected $table = 'tool_diff_saves';

	protected $fillable = [
		'user_id',
		'title',
		'left_text',
		'right_text'
	];
}
