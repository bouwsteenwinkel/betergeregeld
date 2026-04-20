<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $tenant_id
 * @property string $order_id
 * @property string $case_type
 * @property string $status
 * @property string $severity
 * @property float $confidence
 * @property string|null $auto_decision
 * @property string|null $reason_code
 * @property string|null $summary
 * @property string|null $assigned_to
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Order $order
 * @property Tenant $tenant
 * @property User|null $user
 * @property Collection|CaseEvent[] $case_events
 * @property Collection|Evidence[] $evidence
 */
class SupportCase extends Model
{
	protected $table = 'cases';
	public $incrementing = false;
	protected $keyType = 'string';

	protected $casts = [
		'confidence' => 'float',
	];

	protected $fillable = [
		'tenant_id',
		'order_id',
		'case_type',
		'status',
		'severity',
		'confidence',
		'auto_decision',
		'reason_code',
		'summary',
		'assigned_to',
	];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function tenant()
	{
		return $this->belongsTo(Tenant::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'assigned_to');
	}

	public function case_events()
	{
		return $this->hasMany(CaseEvent::class);
	}

	public function evidence()
	{
		return $this->hasMany(Evidence::class);
	}
}
