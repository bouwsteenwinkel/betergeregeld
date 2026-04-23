<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccessGuardDigest extends Mailable
{
	use Queueable, SerializesModels;

	public function __construct(
		public readonly string $tenantId,
		public readonly string $userId,
		public readonly array $digest,
		public readonly string $unsubscribeToken,
	) {}

	public function envelope(): Envelope
	{
		$parts = [];
		if (($this->digest['open_risks_count'] ?? 0) > 0) $parts[] = $this->digest['open_risks_count'] . ' risico\'s';
		if (($this->digest['open_actions_count'] ?? 0) > 0) $parts[] = $this->digest['open_actions_count'] . ' open acties';

		$subject = 'AccessGuard dagelijks overzicht';
		if ($parts) $subject .= ' — ' . implode(', ', $parts);

		return new Envelope(subject: $subject);
	}

	public function content(): Content
	{
		return new Content(
			markdown: 'emails.accessguard.digest',
			with: [
				'digest' => $this->digest,
				'unsubscribeToken' => $this->unsubscribeToken,
				'tenantId' => $this->tenantId,
			],
		);
	}
}
