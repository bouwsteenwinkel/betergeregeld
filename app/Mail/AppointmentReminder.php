<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Herinnering aan een geboekte afspraak. $kind stuurt de toon: '24h' (dag ervoor,
 * ruimte om te verzetten) of '1h' (laatste zetje, Meet-link binnen handbereik).
 *
 * Bewust geen .ics-bijlage zoals in AppointmentConfirmation: die zat al bij de
 * bevestiging, en een tweede PUBLISH met dezelfde UID laat agenda's het item
 * dupliceren of stil overschrijven.
 */
class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $kind = '24h',
    ) {
    }

    public function envelope(): Envelope
    {
        // Geen "morgen" in het onderwerp: de 24u-mail kan na scheduler-uitval ook
        // een paar uur voor de afspraak vertrekken, en dan zou dat gewoon liegen.
        $subject = $this->kind === '1h'
            ? 'Je afspraak begint zo'
            : 'Herinnering: je afspraak staat gepland';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
            with: [
                'appt'      => $this->appointment,
                'kind'      => $this->kind,
                'tz'        => (string) config('scheduling.timezone', 'Europe/Amsterdam'),
                'cancelUrl' => $this->appointment->cancelUrl(),
            ],
        );
    }
}
