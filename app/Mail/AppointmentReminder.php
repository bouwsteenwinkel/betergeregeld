<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Herinnering aan een geboekte afspraak. $kind stuurt de toon: '2d' (ruim vooraf,
 * ruimte om te verzetten) of 'day_of' (de ochtend van de afspraak zelf).
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
        public readonly string $kind = '2d',
    ) {
    }

    public function envelope(): Envelope
    {
        // Geen "morgen" of "over 2 dagen" in het onderwerp: na scheduler-uitval kan een
        // mail later vertrekken dan bedoeld, en dan zou dat gewoon liegen.
        $subject = $this->kind === 'day_of'
            ? 'Vandaag: je afspraak staat gepland'
            : 'Herinnering: je afspraak staat gepland';

        return new Envelope(
            subject: $subject,
            replyTo: $this->organizerReplyTo(),
        );
    }

    /**
     * De mail vraagt expliciet om te antwoorden als het niet uitkomt. Zonder Reply-To
     * belandt dat antwoord op MAIL_FROM_ADDRESS, een afzender die niemand leest.
     *
     * @return array<int,Address>
     */
    private function organizerReplyTo(): array
    {
        $email = (string) config('scheduling.organizer_email');

        return $email === '' ? [] : [new Address($email, (string) config('scheduling.organizer_name', 'Betergeregeld ICT'))];
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
