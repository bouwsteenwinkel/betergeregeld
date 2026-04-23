<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BookkeepingRecurringInvoice extends Model
{
	use HasUuids;

	protected $table = 'bookkeeping_recurring_invoices';
	protected $keyType = 'string';
	public $incrementing = false;

	protected $casts = [
		'start_date' => 'date',
		'end_date' => 'date',
		'next_run_at' => 'date',
		'last_run_at' => 'datetime',
		'is_active' => 'bool',
		'auto_send_email' => 'bool',
		'day_of_month' => 'int',
		'payment_terms_days' => 'int',
	];

	protected $fillable = [
		'id', 'tenant_id', 'relation_id', 'title',
		'frequency', 'day_of_month',
		'start_date', 'end_date', 'next_run_at', 'last_run_at',
		'payment_terms_days', 'notes', 'auto_send_email', 'is_active',
		'created_by_user_id',
	];

	public function relation()
	{
		return $this->belongsTo(BookkeepingRelation::class, 'relation_id');
	}

	public function lines()
	{
		return $this->hasMany(BookkeepingRecurringInvoiceLine::class, 'recurring_invoice_id')->orderBy('sort_order')->orderBy('id');
	}

	public function invoices()
	{
		return $this->hasMany(BookkeepingInvoice::class, 'recurring_template_id');
	}
}
