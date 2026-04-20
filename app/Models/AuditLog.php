<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 * 
 * @property int $id
 * @property Carbon $created_at
 * @property string $event
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property string $page_uri
 * @property string $ip
 * @property string $user_agent
 * @property string $session_id
 * @property array $meta_json
 * @property string $meta_hash
 *
 * @package App\Models
 */
class AuditLog extends Model
{
	protected $table = 'audit_log';
	public $timestamps = false;

	protected $casts = [
		'entity_id' => 'int',
		'meta_json' => 'json'
	];

	protected $fillable = [
		'event',
		'entity_type',
		'entity_id',
		'page_uri',
		'ip',
		'user_agent',
		'session_id',
		'meta_json',
		'meta_hash'
	];
}
