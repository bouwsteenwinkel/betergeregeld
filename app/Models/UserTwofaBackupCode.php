<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserTwofaBackupCode
 * 
 * @property int $id
 * @property string $user_id
 * @property string $code_hash
 * @property Carbon|null $used_at
 * @property Carbon $created_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class UserTwofaBackupCode extends Model
{
	protected $table = 'user_twofa_backup_codes';
	public $timestamps = false;

	protected $casts = [
		'used_at' => 'datetime'
	];

	protected $fillable = [
		'user_id',
		'code_hash',
		'used_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
