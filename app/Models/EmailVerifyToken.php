<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailVerifyToken
 * 
 * @property string $user_id
 * @property string $token_hash
 * @property Carbon $sent_at
 * @property Carbon $expires_at
 *
 * @package App\Models
 */
class EmailVerifyToken extends Model
{
	protected $table = 'email_verify_tokens';
	protected $primaryKey = 'user_id';
	protected $keyType = 'string';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'sent_at' => 'datetime',
		'expires_at' => 'datetime',
	];

	protected $hidden = [
		'token_hash',
	];

	protected $fillable = [
		'token_hash',
		'sent_at',
		'expires_at',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
