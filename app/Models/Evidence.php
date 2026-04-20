<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Evidence
 * 
 * @property string $id
 * @property string $tenant_id
 * @property string $case_id
 * @property string $kind
 * @property string|null $uri
 * @property string|null $hash_sha256
 * @property Carbon|null $captured_at
 * @property string|null $meta
 * @property Carbon $created_at
 * 
 * @property Case $case
 * @property Tenant $tenant
 *
 * @package App\Models
 */
class Evidence extends Model
{
	protected $table = 'evidence';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'captured_at' => 'datetime'
	];

	protected $fillable = [
		'tenant_id',
		'case_id',
		'kind',
		'uri',
		'hash_sha256',
		'captured_at',
		'meta'
	];

	public function case()
	{
		return $this->belongsTo(SupportCase::class);
	}

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}
}
