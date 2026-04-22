<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingCategory extends Model
{
	protected $table = 'bookkeeping_categories';

	protected $casts = [
		'is_active' => 'bool',
		'sort_order' => 'int',
	];

	protected $fillable = [
		'tenant_id', 'name', 'type', 'is_active', 'sort_order',
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
}
