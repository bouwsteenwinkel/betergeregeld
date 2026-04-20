<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpConsent
 * 
 * @property int $id
 * @property string $tenant_key
 * @property int $domain_id
 * @property string $consent_id
 * @property int $policy_version
 * @property string $status
 * @property array $choices_json
 * @property Carbon $first_seen_at
 * @property Carbon $last_updated_at
 * @property Carbon $expires_at
 * 
 * @property CmpDomain $cmp_domain
 *
 * @package App\Models
 */
class CmpConsent extends Model
{
	protected $table = 'cmp_consents';
	public $timestamps = false;

	protected $casts = [
		'domain_id' => 'int',
		'policy_version' => 'int',
		'choices_json' => 'json',
		'first_seen_at' => 'datetime',
		'last_updated_at' => 'datetime',
		'expires_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_key',
		'domain_id',
		'consent_id',
		'policy_version',
		'status',
		'choices_json',
		'first_seen_at',
		'last_updated_at',
		'expires_at'
	];

	public function cmp_domain()
	{
		return $this->belongsTo(CmpDomain::class, 'domain_id');
	}
}
