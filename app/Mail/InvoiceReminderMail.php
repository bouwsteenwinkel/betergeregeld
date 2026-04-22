<?php

namespace App\Mail;

use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingTenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly BookkeepingInvoice $invoice,
		public readonly BookkeepingTenantSettings $settings,
		public readonly string $kind,
	) {}

	public function envelope(): Envelope
	{
		$number = $this->invoice->invoice_number;
		$subject = match ($this->kind) {
			'pre_due' => "Herinnering: factuur {$number} vervalt binnenkort",
			'due' => "Factuur {$number} vervalt vandaag",
			'overdue_7' => "Factuur {$number} is vervallen",
			'overdue_21' => "Laatste herinnering: factuur {$number}",
			default => "Factuur {$number}",
		};

		$from = $this->settings->email
			? new Address($this->settings->email, $this->settings->company_name ?: config('app.name'))
			: null;

		return new Envelope(
			from: $from,
			replyTo: $from ? [$from] : [],
			subject: $subject,
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'emails.bookkeeping.invoice-reminder',
			with: [
				'invoice' => $this->invoice,
				'relation' => $this->invoice->relation,
				'settings' => $this->settings,
				'kind' => $this->kind,
				'tone' => match ($this->kind) {
					'pre_due' => 'friendly',
					'due' => 'neutral',
					'overdue_7' => 'firm',
					'overdue_21' => 'final',
					default => 'neutral',
				},
			],
		);
	}
}
