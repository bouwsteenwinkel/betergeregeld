<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ContactMessage
 * 
 * @property int $id
 * @property string $public_id
 * @property Carbon $created_at
 * @property string $status
 * @property string $name
 * @property string $email
 * @property string|null $topic
 * @property string|null $website
 * @property string|null $company
 * @property string|null $phone
 * @property string|null $cms_platform
 * @property string|null $traffic
 * @property array|null $needs_json
 * @property string $subject
 * @property string $message
 * @property string $ip
 * @property string $user_agent
 * @property string|null $referer
 * @property string $page_uri
 * @property string $session_id
 * @property string $payload_hash
 * @property array $payload_json
 * @property int|null $user_id
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class ContactMessage extends Model
{
	protected $table = 'contact_messages';
	public $timestamps = false;

	protected $casts = [
		'needs_json' => 'json',
		'payload_json' => 'json',
		'user_id' => 'int'
	];

	protected $fillable = [
		'public_id',
		'status',
		'name',
		'email',
		'topic',
		'website',
		'company',
		'phone',
		'cms_platform',
		'traffic',
		'needs_json',
		'subject',
		'message',
		'ip',
		'user_agent',
		'referer',
		'page_uri',
		'session_id',
		'payload_hash',
		'payload_json',
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
