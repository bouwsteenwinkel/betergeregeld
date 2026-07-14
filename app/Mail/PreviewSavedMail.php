<?php

namespace App\Mail;

use App\Models\WebsiteLead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Bevestiging (transactioneel) nadat een bezoeker zijn voorbeeld opsloeg: bevat de
 * persoonlijke, wachtwoordloze link om het voorbeeld altijd terug te vinden.
 */
class PreviewSavedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly WebsiteLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Je voorbeeldwebsite staat klaar');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.previews.saved',
            with: [
                'lead'       => $this->lead,
                'revisitUrl' => $this->lead->revisitUrl(),
                'afmeldUrl'  => $this->lead->unsubscribeUrl(),
            ],
        );
    }
}
