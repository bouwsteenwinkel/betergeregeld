<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Je afspraak is bevestigd');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-confirmation',
            with: [
                'appt'      => $this->appointment,
                'tz'        => (string) config('scheduling.timezone', 'Europe/Amsterdam'),
                'cancelUrl' => $this->cancelUrl(),
            ],
        );
    }

    /**
     * De persoonlijke annuleer-/verzetlink. Altijd op het HOOFDDOMEIN
     * (config('app.url')): geboekt wordt er vanaf elk channel-domein en de mail kan
     * vanuit de CLI vertrekken, dus url()/de huidige host is hier niet betrouwbaar.
     * Null bij een afspraak zonder token (buiten BookingService aangemaakt), dan
     * valt de mail terug op "beantwoord deze mail".
     */
    private function cancelUrl(): ?string
    {
        return $this->appointment->cancel_token
            ? rtrim((string) config('app.url'), '/') . '/afspraak/annuleren/' . $this->appointment->cancel_token
            : null;
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->ics(), 'afspraak.ics')
                ->withMime('text/calendar'),
        ];
    }

    private function ics(): string
    {
        $a   = $this->appointment;
        $fmt = fn ($d) => Carbon::parse($d)->utc()->format('Ymd\THis\Z');
        $desc = 'Online afspraak met ' . config('scheduling.organizer_name', 'Betergeregeld ICT') . '.'
            . ($a->meet_url ? ' Google Meet: ' . $a->meet_url : '');

        $lines = array_filter([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Betergeregeld//Afspraak//NL',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:appt-' . $a->id . '@betergeregeld',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $fmt($a->starts_at),
            'DTEND:' . $fmt($a->ends_at),
            'SUMMARY:' . $this->esc('Afspraak met ' . config('scheduling.organizer_name', 'Betergeregeld ICT')),
            'DESCRIPTION:' . $this->esc($desc),
            'LOCATION:' . $this->esc($a->meet_url ?: 'Google Meet'),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ]);

        return implode("\r\n", $lines);
    }

    private function esc(string $s): string
    {
        return str_replace([',', ';', "\n"], ['\\,', '\\;', '\\n'], $s);
    }
}
