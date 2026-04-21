<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BillingIntent extends Model
{
	use HasUuids;

	protected $table = 'billing_intents';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'amount' => 'decimal:2',
		'meta_json' => 'array',
		'paid_at' => 'datetime',
	];

	protected $fillable = [
		'id',
		'tenant_id',
		'user_id',
		'plan_id',
		'period',
		'amount',
		'currency',
		'status',
		'mollie_payment_id',
		'mollie_checkout_url',
		'meta_json',
		'paid_at',
	];

	public function plan()
	{
		return $this->belongsTo(Plan::class);
	}

	public function isPending(): bool
	{
		return in_array($this->status, ['pending', 'open'], true);
	}

	public function isPaid(): bool
	{
		return $this->status === 'paid';
	}
}
