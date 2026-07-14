<?php

namespace App\Mail;

use App\Models\WebsiteLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Reminder aan een klant die een voorbeeld opsloeg maar nog niets liet horen.
 * $kind stuurt de toon: 'dag1' (zachte nudge) of 'dag4' (laatste herinnering).
 */
class PreviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly WebsiteLead $lead,
        public readonly string $kind = 'dag1',
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->kind === 'dag4'
            ? 'Nog een korte herinnering aan je voorbeeldwebsite'
            : 'Je voorbeeldwebsite wacht nog op je';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.previews.reminder',
            with: [
                'lead'       => $this->lead,
                'kind'       => $this->kind,
                'revisitUrl' => $this->lead->revisitUrl(),
                'afmeldUrl'  => $this->lead->unsubscribeUrl(),
            ],
        );
    }
}
