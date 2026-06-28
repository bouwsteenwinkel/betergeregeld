<?php

namespace App\Mail;

use App\Models\WebsiteLead;
use App\Support\Intake\AppointmentSlots;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IntakeConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public WebsiteLead $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bevestiging afspraak — je nieuwe website',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.intake-confirmation',
            with: [
                'lead'    => $this->lead,
                'when'    => $this->lead->appointment_at
                    ? AppointmentSlots::labelFor($this->lead->appointment_at->format('Y-m-d H:i'))
                    : '',
                'isOnsite' => $this->lead->appointment_type === 'onsite',
                'branche' => WebsiteLead::BRANCHES[$this->lead->branche] ?? $this->lead->branche,
            ],
        );
    }
}
