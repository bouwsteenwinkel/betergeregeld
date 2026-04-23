<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingRecurringInvoiceLine extends Model
{
	public $timestamps = false;

	protected $table = 'bookkeeping_recurring_invoice_lines';

	protected $casts = [
		'quantity' => 'decimal:2',
		'unit_price' => 'decimal:2',
		'sort_order' => 'int',
	];

	protected $fillable = [
		'recurring_invoice_id', 'description', 'quantity', 'unit_price', 'vat_rate_id', 'sort_order',
	];

	public function vatRate()
	{
		return $this->belongsTo(BookkeepingVatRate::class, 'vat_rate_id');
	}
}
