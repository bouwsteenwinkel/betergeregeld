<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TenantApiKey
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $name
 * @property string $key_hash
 * @property string $scopes
 * @property bool $is_active
 * @property Carbon|null $last_used_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class TenantApiKey extends Model
{
	protected $table = 'tenant_api_keys';
	public $incrementing = false;

	protected $casts = [
		'is_active' => 'bool',
		'last_used_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'name',
		'key_hash',
		'scopes',
		'is_active',
		'last_used_at'
	];

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}
}
