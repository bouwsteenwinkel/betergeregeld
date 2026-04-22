<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingInvoiceLine extends Model
{
	protected $table = 'bookkeeping_invoice_lines';

	protected $casts = [
		'quantity' => 'decimal:2',
		'unit_price' => 'decimal:2',
		'sort_order' => 'int',
	];

	protected $fillable = [
		'invoice_id', 'description', 'quantity', 'unit_price', 'vat_rate_id', 'sort_order',
	];

	public function invoice()
	{
		return $this->belongsTo(BookkeepingInvoice::class, 'invoice_id');
	}

	public function vatRate()
	{
		return $this->belongsTo(BookkeepingVatRate::class, 'vat_rate_id');
	}

	public function lineNet(): float
	{
		return round((float) $this->quantity * (float) $this->unit_price, 2);
	}

	public function lineVat(): float
	{
		if (! $this->vatRate) {
			return 0.0;
		}
		return round($this->lineNet() * (float) $this->vatRate->rate / 100, 2);
	}

	public function lineTotal(): float
	{
		return round($this->lineNet() + $this->lineVat(), 2);
	}
}
