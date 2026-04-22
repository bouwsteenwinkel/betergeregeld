<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingRelation extends Model
{
	protected $table = 'bookkeeping_relations';

	protected $casts = [
		'is_active' => 'bool',
	];

	protected $fillable = [
		'tenant_id', 'name', 'type',
		'email', 'phone',
		'kvk_number', 'vat_number', 'iban',
		'address', 'postal_code', 'city', 'country',
		'notes', 'is_active',
	];

	public function transactions()
	{
		return $this->hasMany(BookkeepingTransaction::class, 'relation_id');
	}

	public function scopeActive($q)
	{
		return $q->where('is_active', true);
	}

	public function scopeMatchingType($q, ?string $transactionType)
	{
		if ($transactionType === 'income') {
			return $q->whereIn('type', ['client', 'both']);
		}
		if ($transactionType === 'expense') {
			return $q->whereIn('type', ['supplier', 'both']);
		}
		return $q;
	}
}
