<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSession
 * 
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon|null $revoked_at
 * @property string $user_id
 * @property string $session_id
 * @property string $ip
 * @property string $user_agent
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserSession extends Model
{
	protected $table = 'user_sessions';
	public $timestamps = false;

	protected $casts = [
		'revoked_at' => 'datetime',
		'created_at' => 'datetime',
	];

	protected $hidden = [
		'session_id',
	];

	protected $fillable = [
		'created_at',
		'revoked_at',
		'user_id',
		'session_id',
		'ip',
		'user_agent',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
