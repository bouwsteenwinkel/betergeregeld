<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Tenant
 * 
 * @property string $id
 * @property string $name
 * @property string $plan
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Collection|CaseEvent[] $case_events
 * @property Collection|Case[] $cases
 * @property Collection|Evidence[] $evidence
 * @property Collection|Order[] $orders
 * @property Collection|Payment[] $payments
 * @property Collection|Return[] $returns
 * @property Collection|Shipment[] $shipments
 * @property Collection|TenantApiKey[] $tenant_api_keys
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Tenant extends Model
{
	use HasUuids;

	protected $table = 'tenants';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'is_active' => 'bool',
		'is_demo' => 'bool',
	];

	protected $fillable = [
		'agency_id',
		'name',
		'plan',
		'is_active',
		'is_demo',
		'server_id'
	];

	public function agency()
	{
		return $this->belongsTo(Agency::class);
	}

	public function server()
	{
		return $this->belongsTo(\App\Models\Monitor\Server::class);
	}

	public function seoProperties()
	{
		return $this->hasMany(\App\Models\Seo\SeoProperty::class, 'tenant_id');
	}

	public function case_events()
	{
		return $this->hasMany(CaseEvent::class);
	}

	public function cases()
	{
		return $this->hasMany(SupportCase::class);
	}

	public function evidence()
	{
		return $this->hasMany(Evidence::class);
	}

	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	public function payments()
	{
		return $this->hasMany(Payment::class);
	}

	public function returns()
	{
		return $this->hasMany(OrderReturn::class);
	}

	public function shipments()
	{
		return $this->hasMany(Shipment::class);
	}

	public function tenant_api_keys()
	{
		return $this->hasMany(TenantApiKey::class);
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}
}
