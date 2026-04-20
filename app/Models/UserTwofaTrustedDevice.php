<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserTwofaTrustedDevice
 * 
 * @property int $id
 * @property string $user_id
 * @property string $selector
 * @property string $token_hash
 * @property string $user_agent
 * @property string $ip
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon|null $revoked_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserTwofaTrustedDevice extends Model
{
	protected $table = 'user_twofa_trusted_devices';
	public $timestamps = false;

	protected $casts = [
		'expires_at' => 'datetime',
		'revoked_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'selector',
		'token_hash',
		'user_agent',
		'ip',
		'expires_at',
		'revoked_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
