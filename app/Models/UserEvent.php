<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserEvent
 * 
 * @property int $id
 * @property string $tenant_id
 * @property string|null $user_id
 * @property string $event
 * @property string|null $page_url
 * @property array|null $meta_json
 * @property string|null $ip_hash
 * @property string|null $ua_hash
 * @property Carbon $created_at
 * @property string|null $country_code
 * @property string|null $region_code
 * @property string|null $city
 *
 * @package App\Models
 */
class UserEvent extends Model
{
	protected $table = 'user_events';
	public $timestamps = false;

	protected $casts = [
		'meta_json' => 'json'
	];

	protected $fillable = [
		'tenant_id',
		'user_id',
		'event',
		'page_url',
		'meta_json',
		'ip_hash',
		'ua_hash',
		'country_code',
		'region_code',
		'city'
	];
}
