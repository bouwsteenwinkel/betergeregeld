<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserTwofa
 * 
 * @property string $user_id
 * @property bool $enabled
 * @property varbinary|null $secret_enc
 * @property Carbon|null $confirmed_at
 * @property int|null $last_used_counter
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserTwofa extends Model
{
	protected $table = 'user_twofa';
	protected $primaryKey = 'user_id';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'enabled' => 'bool',
		'confirmed_at' => 'datetime',
		'last_used_counter' => 'int',
	];

	protected $hidden = [
		'secret_enc'
	];

	protected $fillable = [
		'enabled',
		'secret_enc',
		'confirmed_at',
		'last_used_counter'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
