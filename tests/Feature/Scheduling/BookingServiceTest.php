<?php

namespace Tests\Feature\Scheduling;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\WebsiteLead;
use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\Contracts\CalendarGateway;
use App\Services\Scheduling\SlotTakenException;
use App\Services\Scheduling\StubCalendarGateway;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * BookingService: vastleggen, dubbel-boek-preventie, annuleren en verzetten.
 */
class BookingServiceTest extends SchedulingTestCase
{
    private function service(): BookingService
    {
        return $this->app->make(BookingService::class);
    }

    /** @param array<string,mixed> $overrides */
    private function boek(string $time = '11:00', array $overrides = []): Appointment
    {
        return $this->service()->book(array_merge([
            'name'      => 'Jan Jansen',
            'email'     => 'jan@example.nl',
            'starts_at' => self::VRIJDAG . ' ' . $time,
        ], $overrides));
    }

    // ── Boeken ───────────────────────────────────────────────────────────

    #[Test]
    public function een_boeking_legt_de_afspraak_vast_met_een_annuleertoken(): void
    {
        $appt = $this->boek();

        $this->assertSame('booked', $appt->status);
        $this->assertSame('meet', $appt->type);
        $this->assertSame(
            $this->moment(self::VRIJDAG, '12:00')->toDateTimeString(),
            $appt->ends_at->toDateTimeString(),
            'einde = start + meeting_minutes'
        );
        // Zonder token is de annuleerlink uit de bevestigingsmail dood.
        $this->assertSame(48, strlen((string) $appt->cancel_token));
    }

    #[Test]
    public function een_tweede_boeking_op_hetzelfde_slot_gooit_slottakenexception(): void
    {
        $this->boek('11:00');

        $this->expectException(SlotTakenException::class);

        try {
            $this->boek('11:00', ['email' => 'piet@example.nl']);
        } finally {
            // Het echte risico is niet de exception maar een dubbele boeking erachter.
            $this->assertSame(1, Appointment::where('status', 'booked')->count());
        }
    }

    #[Test]
    public function een_boeking_buiten_de_openingstijden_wordt_geweigerd(): void
    {
        $this->expectException(SlotTakenException::class);

        // Zaterdag staat niet in default_hours. De widget biedt dat moment niet aan, maar
        // deze POST is publiek, dus de service moet er zelf op passen.
        $this->boek('11:00', ['starts_at' => self::ZATERDAG . ' 11:00']);
    }

    #[Test]
    public function een_boeking_binnen_de_voorlooptijd_wordt_geweigerd(): void
    {
        $this->expectException(SlotTakenException::class);

        // Nu = donderdag 08:00, voorlooptijd 4u.
        $this->boek('11:00', ['starts_at' => '2026-07-16 11:00']);
    }

    #[Test]
    public function een_geannuleerd_slot_is_daarna_weer_boekbaar(): void
    {
        $eerste = $this->boek('11:00');
        $this->service()->cancel($eerste);

        $tweede = $this->boek('11:00', ['email' => 'piet@example.nl']);

        $this->assertSame('booked', $tweede->status);
        $this->assertNotSame($eerste->id, $tweede->id);
    }

    // ── Mail en agenda ───────────────────────────────────────────────────

    #[Test]
    public function de_klant_krijgt_een_bevestigingsmail(): void
    {
        $appt = $this->boek();

        Mail::assertSent(AppointmentConfirmation::class, fn ($mail) => $mail->hasTo('jan@example.nl'));
    }

    #[Test]
    public function een_mailfout_laat_de_boeking_gewoon_staan(): void
    {
        // De mailserver ligt eruit. Een afspraak die daarop sneuvelt zou het ergste van
        // twee werelden zijn: de klant denkt dat hij een gesprek heeft, wij zien niets.
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('mailserver ligt eruit'));

        $appt = $this->boek();

        $this->assertSame('booked', $appt->fresh()->status);
        $this->assertSame(1, Appointment::count());
    }

    #[Test]
    public function de_agenda_gegevens_landen_op_de_afspraak(): void
    {
        $this->app->bind(CalendarGateway::class, fn () => new class extends StubCalendarGateway
        {
            public function createMeetEvent(Appointment $appointment): array
            {
                return ['event_id' => 'evt-1', 'meet_url' => 'https://meet.google.com/abc-defg-hij'];
            }
        });

        $appt = $this->boek();

        $this->assertSame('evt-1', $appt->fresh()->google_event_id);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $appt->fresh()->meet_url);
    }

    #[Test]
    public function de_stub_gateway_levert_geen_meetlink_maar_blokkeert_de_boeking_niet(): void
    {
        $appt = $this->boek();

        $this->assertNull($appt->meet_url);
        $this->assertNull($appt->google_event_id);
        $this->assertSame('booked', $appt->status);
    }

    // ── Annuleren ────────────────────────────────────────────────────────

    #[Test]
    public function annuleren_geeft_het_slot_vrij(): void
    {
        $appt   = $this->boek('11:00');
        $engine = $this->app->make(\App\Services\Scheduling\SlotEngine::class);

        $this->assertFalse($engine->isSlotAvailable($this->moment(self::VRIJDAG, '11:00')));

        $this->service()->cancel($appt);

        $this->assertSame('cancelled', $appt->fresh()->status);
        $this->assertTrue($engine->isSlotAvailable($this->moment(self::VRIJDAG, '11:00')));
    }

    #[Test]
    public function annuleren_verwijdert_het_agenda_event_precies_een_keer(): void
    {
        $gateway = new class extends StubCalendarGateway
        {
            public array $verwijderd = [];

            public function createMeetEvent(Appointment $appointment): array
            {
                return ['event_id' => 'evt-1', 'meet_url' => null];
            }

            public function deleteEvent(string $eventId): void
            {
                $this->verwijderd[] = $eventId;
            }
        };
        $this->app->bind(CalendarGateway::class, fn () => $gateway);

        $appt = $this->boek();
        $this->service()->cancel($appt);
        $this->service()->cancel($appt->fresh());

        // Idempotent: de annuleerlink uit de mail kan twee keer worden aangeklikt.
        $this->assertSame(['evt-1'], $gateway->verwijderd);
    }

    #[Test]
    public function annuleren_zet_de_lead_terug_naar_contacted(): void
    {
        $appt = $this->boek();
        $lead = WebsiteLead::create([
            'email'              => 'jan@example.nl',
            'status'             => 'appointment',
            'appointment_at'     => $appt->starts_at,
            'appointment_status' => 'booked',
            'meet_link'          => 'https://meet.google.com/abc-defg-hij',
        ]);

        $this->service()->cancel($appt);

        $lead->refresh();
        $this->assertSame('contacted', $lead->status);
        $this->assertSame('cancelled', $lead->appointment_status);
        $this->assertNull($lead->meet_link, 'het event is weg, dus de Meet-link is een dode knop');
    }

    #[Test]
    public function annuleren_degradeert_een_lead_die_al_verder_is_niet(): void
    {
        $appt = $this->boek();
        $lead = WebsiteLead::create([
            'email'          => 'jan@example.nl',
            'status'         => 'quoted',
            'appointment_at' => $appt->starts_at,
        ]);

        $this->service()->cancel($appt);

        $this->assertSame('quoted', $lead->fresh()->status);
    }

    #[Test]
    public function annuleren_raakt_de_lead_van_een_andere_afspraak_niet(): void
    {
        $appt   = $this->boek('11:00');
        $tweede = $this->boek('14:00');

        $leadTweede = WebsiteLead::create([
            'email'          => 'jan@example.nl',
            'status'         => 'appointment',
            'appointment_at' => $tweede->starts_at,
        ]);

        $this->service()->cancel($appt);

        // Koppeling is op e-mail + moment: dezelfde persoon kan twee afspraken hebben.
        $this->assertSame('appointment', $leadTweede->fresh()->status);
    }

    // ── Verzetten ────────────────────────────────────────────────────────

    #[Test]
    public function verzetten_annuleert_het_oude_en_boekt_het_nieuwe_moment(): void
    {
        $oud  = $this->boek('11:00');
        $lead = WebsiteLead::create([
            'email'          => 'jan@example.nl',
            'status'         => 'appointment',
            'appointment_at' => $oud->starts_at,
        ]);

        $nieuw = $this->service()->reschedule($oud, self::VRIJDAG . ' 14:00');

        $this->assertSame('cancelled', $oud->fresh()->status);
        $this->assertSame('booked', $nieuw->status);
        $this->assertSame(
            $this->moment(self::VRIJDAG, '14:00')->toDateTimeString(),
            $nieuw->starts_at->toDateTimeString()
        );
        $this->assertNotSame($oud->cancel_token, $nieuw->cancel_token, 'de oude link is dood');

        // De lead moet meeverhuizen: anders staat er een afspraak zonder lead die erbij hoort.
        $lead->refresh();
        $this->assertSame('appointment', $lead->status);
        $this->assertSame('booked', $lead->appointment_status);
        $this->assertSame($nieuw->starts_at->toDateTimeString(), $lead->appointment_at->toDateTimeString());
    }

    #[Test]
    public function verzetten_naar_een_bezet_moment_laat_de_oude_afspraak_staan(): void
    {
        $oud = $this->boek('11:00');
        $this->afspraak(self::VRIJDAG, '14:00', ['email' => 'iemand@example.nl']);

        try {
            $this->service()->reschedule($oud, self::VRIJDAG . ' 14:00');
            $this->fail('reschedule had SlotTakenException moeten gooien');
        } catch (SlotTakenException) {
            // Eerst boeken, dan pas annuleren: de klant mag niet met lege handen achterblijven.
            $this->assertSame('booked', $oud->fresh()->status);
        }
    }
}
