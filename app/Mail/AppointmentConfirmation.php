<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
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
        return new Envelope(
            subject: 'Je afspraak is bevestigd',
            replyTo: $this->organizerReplyTo(),
        );
    }

    /**
     * De mail nodigt uit om te antwoorden als het niet uitkomt. Zonder Reply-To komt
     * dat antwoord op MAIL_FROM_ADDRESS terecht, een afzender die niemand leest.
     *
     * @return array<int,Address>
     */
    private function organizerReplyTo(): array
    {
        $email = (string) config('scheduling.organizer_email');

        return $email === '' ? [] : [new Address($email, $this->organizerName())];
    }

    private function organizerName(): string
    {
        return (string) config('scheduling.organizer_name', 'Betergeregeld ICT');
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

    /**
     * Losse .ics als vangnet: staat de Google-koppeling aan, dan heeft de klant de
     * uitnodiging al van Google zelf. Deze bijlage is er voor wie 'm handmatig in een
     * andere agenda wil zetten.
     *
     * Bewust METHOD:PUBLISH en dus GEEN ATTENDEE-regel: PUBLISH is "hier is een item om
     * toe te voegen", niet "ik nodig je uit" (dat is METHOD:REQUEST). RFC 2446 verbiedt
     * ATTENDEE bij PUBLISH, en een tweede uitnodiging naast die van Google zou in de
     * agenda van de klant met zichzelf botsen. ORGANIZER is bij PUBLISH wél verplicht en
     * ontbrak, waardoor strikte clients (Outlook) het bestand konden weigeren.
     */
    private function ics(): string
    {
        $a   = $this->appointment;
        $fmt = fn ($d) => Carbon::parse($d)->utc()->format('Ymd\THis\Z');
        $organizerEmail = (string) config('scheduling.organizer_email');
        $desc = 'Online afspraak met ' . $this->organizerName() . '.'
            . ($a->meet_url ? ' Google Meet: ' . $a->meet_url : '');

        $lines = array_values(array_filter([
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Betergeregeld//Afspraak//NL',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $this->uid(),
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $fmt($a->starts_at),
            'DTEND:' . $fmt($a->ends_at),
            $organizerEmail !== ''
                ? 'ORGANIZER;CN=' . $this->param($this->organizerName()) . ':mailto:' . $organizerEmail
                : null,
            'SUMMARY:' . $this->esc('Afspraak met ' . $this->organizerName()),
            'DESCRIPTION:' . $this->esc($desc),
            'LOCATION:' . $this->esc($a->meet_url ?: 'Google Meet'),
            $a->meet_url ? 'URL:' . $a->meet_url : null,
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ]));

        return implode("\r\n", array_map(fn ($l) => $this->fold($l), $lines)) . "\r\n";
    }

    /**
     * Stabiel en wereldwijd uniek, zoals RFC5545 vraagt. Het domein erbij voorkomt dat
     * een 'appt-7' van deze app botst met een 'appt-7' uit een andere agenda-bron.
     */
    private function uid(): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'betergeregeld.com';

        return 'appt-' . $this->appointment->id . '-' . substr(md5((string) $this->appointment->cancel_token), 0, 8) . '@' . $host;
    }

    /**
     * RFC5545 staat maximaal 75 octets per regel toe; langer moet gevouwen worden met
     * CRLF + een spatie. Een Meet-link in DESCRIPTION haalt die grens ruim, en strikte
     * clients kappen zo'n regel af of weigeren het bestand.
     *
     * Telt in octets, niet in tekens: een vouw midden in een meerbyte-teken (é in een
     * naam) levert een kapot bestand op.
     */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $out   = '';
        $chunk = '';

        foreach (mb_str_split($line) as $char) {
            // 75 voor de eerste regel, 74 voor vervolgregels: de leidende spatie telt mee.
            $limit = $out === '' ? 75 : 74;

            if (strlen($chunk) + strlen($char) > $limit) {
                $out  .= ($out === '' ? '' : "\r\n ") . $chunk;
                $chunk = '';
            }
            $chunk .= $char;
        }

        return $out . ($out === '' ? '' : "\r\n ") . $chunk;
    }

    /**
     * Een PARAMETERwaarde (zoals CN=) volgt andere regels dan een tekstwaarde: hij kent
     * geen backslash-escaping, maar moet tussen dubbele quotes zodra er een ':', ';' of
     * ',' in staat. De tekst-escaping van esc() erop loslaten zou hier dus een letterlijke
     * backslash in de naam zetten. Dubbele quotes kunnen sowieso niet in een param en
     * gaan eruit.
     */
    private function param(string $s): string
    {
        $s = str_replace(['"', "\r", "\n"], ['', '', ''], $s);

        return preg_match('/[:;,]/', $s) ? '"' . $s . '"' : $s;
    }

    /**
     * Backslash eerst: doe je dat later, dan escape je de backslashes die je zelf net
     * hebt toegevoegd nog een keer. CR wordt meegenomen zodat een \r\n in een notitie
     * niet als losse regel in het bestand eindigt.
     */
    private function esc(string $s): string
    {
        return str_replace(['\\', "\r\n", "\r", "\n", ';', ','], ['\\\\', '\\n', '\\n', '\\n', '\\;', '\\,'], $s);
    }
}
