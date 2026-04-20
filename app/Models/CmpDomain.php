<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CmpDomain
 * 
 * @property int $id
 * @property string $tenant_key
 * @property string $domain
 * @property bool $is_primary
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|CmpConsentLog[] $cmp_consent_logs
 * @property Collection|CmpConsent[] $cmp_consents
 *
 * @package App\Models
 */
class CmpDomain extends Model
{
	protected $table = 'cmp_domains';

	protected $casts = [
		'is_primary' => 'bool'
	];

	protected $fillable = [
		'tenant_key',
		'domain',
		'is_primary',
		'status'
	];

	public function cmp_consent_logs()
	{
		return $this->hasMany(CmpConsentLog::class, 'domain_id');
	}

	public function cmp_consents()
	{
		return $this->hasMany(CmpConsent::class, 'domain_id');
	}
}
