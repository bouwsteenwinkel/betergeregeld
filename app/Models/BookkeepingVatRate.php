<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingVatRate extends Model
{
	protected $table = 'bookkeeping_vat_rates';

	protected $casts = [
		'rate' => 'decimal:2',
		'is_default' => 'bool',
		'effective_from' => 'date',
		'effective_to' => 'date',
	];

	protected $fillable = [
		'tenant_id', 'name', 'rate', 'effective_from', 'effective_to', 'is_default',
	];

	public function scopeForTenant($q, ?string $tenantId)
	{
		return $q->where(function ($q) use ($tenantId) {
			$q->whereNull('tenant_id');
			if ($tenantId) {
				$q->orWhere('tenant_id', $tenantId);
			}
		});
	}

	public function scopeActiveOn($q, string $date)
	{
		return $q->where('effective_from', '<=', $date)
			->where(function ($q) use ($date) {
				$q->whereNull('effective_to')->orWhere('effective_to', '>=', $date);
			});
	}
}
