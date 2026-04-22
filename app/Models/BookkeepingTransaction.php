<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BookkeepingTransaction extends Model
{
	use HasUuids;

	protected $table = 'bookkeeping_transactions';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'transaction_date' => 'date',
		'amount' => 'decimal:2',
		'vat_included' => 'bool',
		'vat_deductible' => 'bool',
	];

	protected $fillable = [
		'id',
		'tenant_id', 'created_by_user_id',
		'type', 'transaction_date', 'amount',
		'vat_rate_id', 'vat_included', 'vat_deductible',
		'category_id', 'relation_id',
		'description', 'counterparty', 'invoice_number', 'receipt_path',
	];

	public function category()
	{
		return $this->belongsTo(BookkeepingCategory::class, 'category_id');
	}

	public function vatRate()
	{
		return $this->belongsTo(BookkeepingVatRate::class, 'vat_rate_id');
	}

	public function relation()
	{
		return $this->belongsTo(BookkeepingRelation::class, 'relation_id');
	}

	/** Display name for lists — prefer linked relation over free-text counterparty. */
	public function counterpartyLabel(): ?string
	{
		return $this->relation?->name ?: $this->counterparty;
	}

	/**
	 * VAT amount = amount * rate / (100 + rate) if vat_included,
	 * else amount * rate / 100.
	 */
	public function vatAmount(): float
	{
		if (! $this->vatRate) {
			return 0.0;
		}
		$rate = (float) $this->vatRate->rate;
		if ($rate <= 0) {
			return 0.0;
		}
		$amount = (float) $this->amount;
		return $this->vat_included
			? round($amount * $rate / (100 + $rate), 2)
			: round($amount * $rate / 100, 2);
	}

	public function netAmount(): float
	{
		return round((float) $this->amount - $this->vatAmount(), 2);
	}

	public function grossAmount(): float
	{
		return $this->vat_included
			? (float) $this->amount
			: round((float) $this->amount + $this->vatAmount(), 2);
	}
}
