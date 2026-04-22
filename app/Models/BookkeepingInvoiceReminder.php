<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookkeepingInvoiceReminder extends Model
{
	public $timestamps = false;

	protected $table = 'bookkeeping_invoice_reminders';

	protected $casts = [
		'sent_at' => 'datetime',
		'was_manual' => 'bool',
	];

	protected $fillable = [
		'tenant_id', 'invoice_id', 'kind',
		'sent_at', 'sent_to_email', 'was_manual',
	];

	public function invoice()
	{
		return $this->belongsTo(BookkeepingInvoice::class, 'invoice_id');
	}
}
