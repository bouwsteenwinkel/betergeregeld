<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpConsentLog
 * 
 * @property int $id
 * @property string $tenant_key
 * @property int $domain_id
 * @property string $consent_id
 * @property string $event
 * @property int $policy_version
 * @property array $choices_json
 * @property string|null $ip_hash
 * @property string|null $ua_hash
 * @property Carbon $created_at
 * 
 * @property CmpDomain $cmp_domain
 *
 * @package App\Models
 */
class CmpConsentLog extends Model
{
	protected $table = 'cmp_consent_logs';
	public $timestamps = false;

	protected $casts = [
		'domain_id' => 'int',
		'policy_version' => 'int',
		'choices_json' => 'json'
	];

	protected $fillable = [
		'tenant_key',
		'domain_id',
		'consent_id',
		'event',
		'policy_version',
		'choices_json',
		'ip_hash',
		'ua_hash'
	];

	public function cmp_domain()
	{
		return $this->belongsTo(CmpDomain::class, 'domain_id');
	}
}
