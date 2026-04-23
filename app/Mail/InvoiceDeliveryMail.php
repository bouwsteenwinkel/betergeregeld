<?php

namespace App\Mail;

use App\Models\BookkeepingInvoice;
use App\Models\BookkeepingTenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sends the invoice itself to the relation — used when a recurring
 * template is auto-generated with auto_send_email=true, or when the
 * user triggers a manual send from the invoice page.
 */
class InvoiceDeliveryMail extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly BookkeepingInvoice $invoice,
		public readonly BookkeepingTenantSettings $settings,
		public readonly string $pdfPath,
	) {}

	public function envelope(): Envelope
	{
		$from = $this->settings->email
			? new Address($this->settings->email, $this->settings->company_name ?: config('app.name'))
			: null;

		return new Envelope(
			from: $from,
			replyTo: $from ? [$from] : [],
			subject: 'Factuur ' . $this->invoice->invoice_number . ' van ' . ($this->settings->company_name ?: config('app.name')),
		);
	}

	public function content(): Content
	{
		return new Content(
			view: 'emails.bookkeeping.invoice-delivery',
			with: [
				'invoice' => $this->invoice,
				'relation' => $this->invoice->relation,
				'settings' => $this->settings,
			],
		);
	}

	public function attachments(): array
	{
		return [
			Attachment::fromPath($this->pdfPath)
				->as('factuur-' . $this->invoice->invoice_number . '.pdf')
				->withMime('application/pdf'),
		];
	}
}
