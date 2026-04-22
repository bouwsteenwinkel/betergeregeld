<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BookkeepingInvoice extends Model
{
	use HasUuids;

	protected $table = 'bookkeeping_invoices';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'issue_date' => 'date',
		'due_date' => 'date',
		'subtotal' => 'decimal:2',
		'vat_amount' => 'decimal:2',
		'total' => 'decimal:2',
		'sent_at' => 'datetime',
		'paid_at' => 'datetime',
	];

	protected $fillable = [
		'id', 'tenant_id', 'relation_id', 'invoice_number',
		'issue_date', 'due_date', 'status',
		'subtotal', 'vat_amount', 'total',
		'notes', 'reference', 'payment_terms_days',
		'sent_at', 'paid_at', 'created_by_user_id',
	];

	public function relation()
	{
		return $this->belongsTo(BookkeepingRelation::class, 'relation_id');
	}

	public function lines()
	{
		return $this->hasMany(BookkeepingInvoiceLine::class, 'invoice_id')->orderBy('sort_order')->orderBy('id');
	}

	public function transactions()
	{
		return $this->hasMany(BookkeepingTransaction::class, 'invoice_id');
	}

	public function recalculate(): void
	{
		$subtotal = 0.0;
		$vatTotal = 0.0;
		foreach ($this->lines as $line) {
			$net = (float) $line->quantity * (float) $line->unit_price;
			$subtotal += $net;
			if ($line->vatRate) {
				$vatTotal += $net * (float) $line->vatRate->rate / 100;
			}
		}
		$this->subtotal = round($subtotal, 2);
		$this->vat_amount = round($vatTotal, 2);
		$this->total = round($subtotal + $vatTotal, 2);
	}

	public function isEditable(): bool
	{
		return $this->status === 'draft';
	}
}
