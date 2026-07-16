<?php

namespace Tests\Feature\Scheduling;

use App\Models\Appointment;
use App\Models\WebsiteLead;
use App\Services\Scheduling\BookingService;
use App\Services\Scheduling\SlotEngine;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * De wachtwoordloze annuleer-/verzetpagina achter de cancel_token-link uit de
 * bevestigingsmail (GET + POST /afspraak/annuleren/{token}).
 */
class AppointmentCancelTest extends SchedulingTestCase
{
    private function boek(string $time = '11:00'): Appointment
    {
        return $this->app->make(BookingService::class)->book([
            'name'      => 'Jan Jansen',
            'email'     => 'jan@example.nl',
            'starts_at' => self::VRIJDAG . ' ' . $time,
        ]);
    }

    // ── Pagina tonen ─────────────────────────────────────────────────────

    #[Test]
    public function een_geldig_token_toont_de_afspraak_met_annuleerknop(): void
    {
        $appt = $this->boek();

        $this->get('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertOk()
            ->assertSee('Jan Jansen');
    }

    #[Test]
    public function een_onbekend_token_geeft_404(): void
    {
        $this->boek();

        $this->get('/afspraak/annuleren/' . str_repeat('x', 48))->assertNotFound();
    }

    #[Test]
    public function een_te_kort_token_geeft_404(): void
    {
        // De lengte-ondergrens houdt een afgeknotte of lege token buiten de query.
        $this->get('/afspraak/annuleren/abc123')->assertNotFound();
    }

    #[Test]
    public function een_afspraak_zonder_token_is_niet_te_vinden_met_een_leeg_token(): void
    {
        // Buiten BookingService om aangemaakte afspraken hebben geen cancel_token. Zonder
        // de lengte-ondergrens zou zo'n rij via een korte/lege token te raken zijn.
        $this->afspraak(self::VRIJDAG, '11:00', ['cancel_token' => null]);

        $this->get('/afspraak/annuleren/')->assertNotFound();
        $this->post('/afspraak/annuleren/x')->assertNotFound();
    }

    // ── Annuleren ────────────────────────────────────────────────────────

    #[Test]
    public function een_geldig_token_annuleert_de_afspraak_en_geeft_het_slot_vrij(): void
    {
        $appt = $this->boek('11:00');

        $this->post('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertRedirect('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertSessionHas('appointment_cancelled', true);

        $this->assertSame('cancelled', $appt->fresh()->status);
        $this->assertTrue(
            $this->app->make(SlotEngine::class)->isSlotAvailable($this->moment(self::VRIJDAG, '11:00')),
            'het slot moet weer te boeken zijn'
        );
    }

    #[Test]
    public function annuleren_via_een_onbekend_token_faalt_netjes_met_404(): void
    {
        $appt = $this->boek();

        $this->post('/afspraak/annuleren/' . str_repeat('y', 48))->assertNotFound();

        $this->assertSame('booked', $appt->fresh()->status, 'de echte afspraak blijft staan');
    }

    #[Test]
    public function twee_keer_annuleren_crasht_niet(): void
    {
        $appt = $this->boek();

        $this->post('/afspraak/annuleren/' . $appt->cancel_token)->assertRedirect();
        // De annuleerlink uit de mail kan nu eenmaal twee keer aangeklikt worden.
        $this->post('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertRedirect('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertSessionMissing('appointment_cancelled');

        $this->assertSame('cancelled', $appt->fresh()->status);
    }

    #[Test]
    public function een_geannuleerde_afspraak_toont_geen_knoppen_meer(): void
    {
        $appt = $this->boek();
        $this->post('/afspraak/annuleren/' . $appt->cancel_token);

        $this->get('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertOk()
            ->assertSee('geannuleerd', false);
    }

    #[Test]
    public function een_afspraak_die_al_geweest_is_kan_niet_meer_worden_geannuleerd(): void
    {
        $appt = $this->boek();
        // Verzetten van de klok is eerlijker dan een afspraak in het verleden boeken:
        // dat laatste laat book() niet eens toe.
        $this->travelTo(CarbonImmutable::parse(self::VRIJDAG . ' 18:00', self::TZ));

        $this->post('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertRedirect()
            ->assertSessionMissing('appointment_cancelled');

        $this->assertSame('booked', $appt->fresh()->status, 'achteraf afzeggen heeft geen zin');
    }

    // ── Verzetten ────────────────────────────────────────────────────────

    #[Test]
    public function verzetten_stuurt_door_naar_de_nieuwe_annuleerlink(): void
    {
        $appt = $this->boek('11:00');

        $response = $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [
            'starts_at' => self::VRIJDAG . ' 14:00',
        ]);

        $nieuw = Appointment::where('status', 'booked')->sole();

        $response->assertRedirect('/afspraak/annuleren/' . $nieuw->cancel_token)
            ->assertSessionHas('appointment_rescheduled', true);

        $this->assertSame('cancelled', $appt->fresh()->status);
        $this->assertSame(
            $this->moment(self::VRIJDAG, '14:00')->toDateTimeString(),
            $nieuw->starts_at->toDateTimeString()
        );
    }

    #[Test]
    public function verzetten_naar_een_bezet_moment_geeft_een_nette_melding(): void
    {
        $appt = $this->boek('11:00');
        $this->afspraak(self::VRIJDAG, '14:00', ['email' => 'iemand@example.nl']);

        $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [
            'starts_at' => self::VRIJDAG . ' 14:00',
        ])->assertRedirect('/afspraak/annuleren/' . $appt->cancel_token)
            ->assertSessionHasErrors('starts_at');

        $this->assertSame('booked', $appt->fresh()->status, 'de klant houdt zijn bestaande afspraak');
    }

    #[Test]
    public function verzetten_naar_hetzelfde_moment_geeft_een_nette_melding(): void
    {
        $appt = $this->boek('11:00');

        $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [
            'starts_at' => self::VRIJDAG . ' 11:00',
        ])->assertSessionHasErrors('starts_at');

        $this->assertSame('booked', $appt->fresh()->status);
        $this->assertSame(1, Appointment::count(), 'geen tweede afspraak op hetzelfde moment');
    }

    #[Test]
    public function verzetten_naar_een_moment_in_het_verleden_wordt_geweigerd(): void
    {
        $appt = $this->boek('11:00');

        $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [
            'starts_at' => '2026-07-01 11:00',
        ])->assertSessionHasErrors('starts_at');

        $this->assertSame('booked', $appt->fresh()->status);
    }

    #[Test]
    public function verzetten_zonder_moment_wordt_geweigerd(): void
    {
        $appt = $this->boek('11:00');

        $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [])
            ->assertSessionHasErrors('starts_at');

        $this->assertSame('booked', $appt->fresh()->status);
    }

    #[Test]
    public function verzetten_neemt_de_lead_mee_naar_het_nieuwe_moment(): void
    {
        $appt = $this->boek('11:00');
        $lead = WebsiteLead::create([
            'email'              => 'jan@example.nl',
            'status'             => 'appointment',
            'appointment_at'     => $appt->starts_at,
            'appointment_status' => 'confirmed',
        ]);

        $this->post('/afspraak/annuleren/' . $appt->cancel_token . '/verzetten', [
            'starts_at' => self::VRIJDAG . ' 14:00',
        ])->assertRedirect();

        $lead->refresh();

        // Zonder dit blijft de lead achter als 'contacted/cancelled' terwijl er een
        // nieuwe afspraak staat: de admin ziet dan een gesprek dat niemand opvolgt.
        $this->assertSame('appointment', $lead->status);
        $this->assertSame('confirmed', $lead->appointment_status);
        $this->assertSame(
            $this->moment(self::VRIJDAG, '14:00')->toDateTimeString(),
            $lead->appointment_at->toDateTimeString()
        );
    }
}
