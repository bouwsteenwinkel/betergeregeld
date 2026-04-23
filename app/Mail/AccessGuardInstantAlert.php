<?php

namespace App\Mail;

use App\Models\AccessGuard\RiskFlag;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessGuardInstantAlert extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly string $tenantId,
		public readonly RiskFlag $flag,
		public readonly string $unsubscribeToken,
	) {}

	public function envelope(): Envelope
	{
		return new Envelope(
			subject: '🚨 AccessGuard kritiek risico: ' . $this->flag->title,
		);
	}

	public function content(): Content
	{
		return new Content(
			markdown: 'emails.accessguard.instant-alert',
			with: [
				'flag' => $this->flag,
				'unsubscribeToken' => $this->unsubscribeToken,
			],
		);
	}
}
