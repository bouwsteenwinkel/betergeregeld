<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserAcquisition
 * 
 * @property string $user_id
 * @property string $tenant_id
 * @property Carbon|null $first_seen_at
 * @property Carbon $registered_at
 * @property string|null $landing_url
 * @property string|null $referrer_url
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property string|null $utm_campaign
 * @property string|null $utm_term
 * @property string|null $utm_content
 * @property string|null $gclid
 * @property string|null $fbclid
 * @property string|null $last_landing_url
 * @property string|null $last_referrer_url
 * @property string|null $last_utm_source
 * @property string|null $last_utm_medium
 * @property string|null $last_utm_campaign
 * @property string|null $last_utm_term
 * @property string|null $last_utm_content
 * @property string|null $last_gclid
 * @property string|null $last_fbclid
 * @property string|null $registered_from_tool
 * @property string|null $ip_hash
 * @property string|null $ua_hash
 * @property string|null $country_code
 * @property string|null $region_code
 * @property string|null $city
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class UserAcquisition extends Model
{
	protected $table = 'user_acquisition';
	protected $primaryKey = 'user_id';
	public $incrementing = false;

	protected $casts = [
		'first_seen_at' => 'datetime',
		'registered_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'first_seen_at',
		'registered_at',
		'landing_url',
		'referrer_url',
		'utm_source',
		'utm_medium',
		'utm_campaign',
		'utm_term',
		'utm_content',
		'gclid',
		'fbclid',
		'last_landing_url',
		'last_referrer_url',
		'last_utm_source',
		'last_utm_medium',
		'last_utm_campaign',
		'last_utm_term',
		'last_utm_content',
		'last_gclid',
		'last_fbclid',
		'registered_from_tool',
		'ip_hash',
		'ua_hash',
		'country_code',
		'region_code',
		'city'
	];
}
