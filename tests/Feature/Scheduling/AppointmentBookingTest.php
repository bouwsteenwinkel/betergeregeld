<?php

namespace Tests\Feature\Scheduling;

use App\Models\Appointment;
use App\Models\WebsiteLead;
use App\Services\Scheduling\CalendarUnavailableException;
use App\Services\Scheduling\Contracts\CalendarGateway;
use App\Services\Scheduling\StubCalendarGateway;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * De publieke afspraken-API: POST /afspraak/boeken en GET /afspraak/beschikbaarheid.
 * Dit is het pad waar het advertentiebudget in eindigt, dus getest over echte HTTP.
 */
class AppointmentBookingTest extends SchedulingTestCase
{
    /** @param array<string,mixed> $overrides */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'      => 'Jan Jansen',
            'email'     => 'jan@example.nl',
            'phone'     => '0612345678',
            'starts_at' => self::VRIJDAG . ' 11:00',
        ], $overrides);
    }

    // ── Beschikbaarheid ──────────────────────────────────────────────────

    #[Test]
    public function beschikbaarheid_geeft_de_vrije_momenten_per_dag(): void
    {
        $response = $this->getJson('/afspraak/beschikbaarheid?from=' . self::VRIJDAG . '&to=' . self::VRIJDAG);

        $response->assertOk();
        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
            $response->json('days.' . self::VRIJDAG)
        );
    }

    // ── Weigeren ─────────────────────────────────────────────────────────

    #[Test]
    public function een_gevulde_honeypot_wordt_geweigerd(): void
    {
        $response = $this->postJson('/afspraak/boeken', $this->payload(['website' => 'https://spam.example']));

        $response->assertStatus(422)->assertJsonValidationErrors('website');
        $this->assertSame(0, Appointment::count());
        $this->assertSame(0, WebsiteLead::count(), 'een bot mag geen lead in de pijplijn achterlaten');
    }

    #[Test]
    public function een_lege_honeypot_gaat_gewoon_door(): void
    {
        // De regel is 'nullable|max:0'; een echte bezoeker stuurt het veld leeg mee.
        $this->postJson('/afspraak/boeken', $this->payload(['website' => '']))->assertOk();
    }

    #[Test]
    public function een_bezet_slot_geeft_409_met_een_nette_melding(): void
    {
        $this->afspraak(self::VRIJDAG, '11:00');

        $response = $this->postJson('/afspraak/boeken', $this->payload());

        $response->assertStatus(409)->assertJson([
            'ok'      => false,
            'message' => 'Dit tijdstip is net bezet. Kies een ander moment.',
        ]);
        $this->assertSame(1, Appointment::count(), 'geen tweede afspraak op hetzelfde moment');
    }

    #[Test]
    public function ontbrekende_velden_geven_een_validatiefout(): void
    {
        $response = $this->postJson('/afspraak/boeken', ['name' => 'Jan']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email', 'starts_at']);
        $this->assertSame(0, Appointment::count());
    }

    // ── Boeken ───────────────────────────────────────────────────────────

    #[Test]
    public function een_geslaagde_boeking_geeft_ok_en_het_tijdstip_terug(): void
    {
        $response = $this->postJson('/afspraak/boeken', $this->payload());

        $response->assertOk()->assertJson([
            'ok'        => true,
            'starts_at' => self::VRIJDAG . ' 11:00',
        ]);
        $this->assertSame(1, Appointment::where('status', 'booked')->count());
    }

    #[Test]
    public function een_geslaagde_boeking_legt_een_lead_vast_met_source_afspraak(): void
    {
        $this->postJson('/afspraak/boeken', $this->payload(['source_site' => 'bedrijfswebsite']))->assertOk();

        $lead = WebsiteLead::sole();

        $this->assertSame('afspraak', $lead->source);
        $this->assertSame('appointment', $lead->status);
        $this->assertSame('bedrijfswebsite', $lead->channel);
        $this->assertSame('Jan Jansen', $lead->contact_name);
        $this->assertSame('0612345678', $lead->phone);
        // 'confirmed' en niet 'booked': de lead-administratie kent alleen de waarden uit
        // WebsiteLead::APPOINTMENT_STATUSES, en de rauwe afspraak-status stond in geen
        // enkel filter of keuzemenu van de admin.
        $this->assertSame('confirmed', $lead->appointment_status);
        $this->assertSame(
            $this->moment(self::VRIJDAG, '11:00')->toDateTimeString(),
            $lead->appointment_at->toDateTimeString()
        );
    }

    // ── Dedupe ───────────────────────────────────────────────────────────

    #[Test]
    public function een_bestaande_lead_wordt_bijgewerkt_en_niet_gedupliceerd(): void
    {
        WebsiteLead::create([
            'email'        => 'jan@example.nl',
            'contact_name' => 'J. Jansen',
            'source'       => 'intake',
            'channel'      => 'voorbeeld-tool',
            'status'       => 'new',
        ]);

        $this->postJson('/afspraak/boeken', $this->payload(['source_site' => 'bedrijfswebsite']))->assertOk();

        $lead = WebsiteLead::sole();

        // De oorspronkelijke herkomst blijft staan: deze persoon kwam via de voorbeeld-tool
        // binnen, en de afspraak is een stap verder in diezelfde funnel, geen nieuwe bron.
        $this->assertSame('intake', $lead->source);
        $this->assertSame('voorbeeld-tool', $lead->channel);
        // De naam die de klant zelf al gaf wint van de naam uit dit formulier.
        $this->assertSame('J. Jansen', $lead->contact_name);
        // De afspraak wint wel: dit is het verste punt in de funnel.
        $this->assertSame('appointment', $lead->status);
    }

    #[Test]
    public function first_touch_wint_de_klik_herkomst_blijft_van_de_eerste_aanraking(): void
    {
        WebsiteLead::create([
            'email'        => 'jan@example.nl',
            'source'       => 'intake',
            'gclid'        => 'eerste-klik',
            'utm_campaign' => 'zoek-website-laten-maken',
        ]);

        $this->metHerkomstCookie(['gclid' => 'tweede-klik'])
            ->postJson('/afspraak/boeken', $this->payload())
            ->assertOk();

        $lead = WebsiteLead::sole();

        $this->assertSame('eerste-klik', $lead->gclid);
        $this->assertSame('zoek-website-laten-maken', $lead->utm_campaign);
    }

    #[Test]
    public function de_klik_herkomst_uit_de_cookie_landt_op_een_nieuwe_lead(): void
    {
        $this->metHerkomstCookie(['gclid' => 'abc123', 'utm_source' => 'google'])
            ->postJson('/afspraak/boeken', $this->payload())
            ->assertOk();

        $lead = WebsiteLead::sole();

        // Zonder dit leert Google Ads nooit welke klik een klant opleverde.
        $this->assertSame('abc123', $lead->gclid);
        $this->assertSame('google', $lead->utm_source);
    }

    #[Test]
    public function een_kapotte_lead_administratie_haalt_de_afspraak_niet_omver(): void
    {
        // recordLead is best-effort: de klant heeft zijn bevestiging al.
        \Illuminate\Support\Facades\Schema::drop('website_leads');

        $this->postJson('/afspraak/boeken', $this->payload())->assertOk();

        $this->assertSame(1, Appointment::where('status', 'booked')->count());
    }

    // ── Onbereikbare agenda ──────────────────────────────────────────────

    /** Vervangt de agenda door eentje die er het zwijgen toe doet. */
    private function agendaOnbereikbaar(): void
    {
        $this->app->bind(CalendarGateway::class, fn () => new class extends StubCalendarGateway
        {
            public function busyPeriods(\Carbon\CarbonInterface $from, \Carbon\CarbonInterface $to): array
            {
                throw new CalendarUnavailableException('Google onbereikbaar');
            }
        });
    }

    #[Test]
    public function een_onbereikbare_agenda_geeft_geen_lege_lijst_maar_een_503(): void
    {
        // Het verschil tussen "er is niets vrij" en "we weten het niet". Als 200 met een
        // lege lijst, dan meldt de widget doodleuk "geen momenten beschikbaar" terwijl de
        // agenda misschien juist bomvol staat.
        $this->agendaOnbereikbaar();

        $this->getJson('/afspraak/beschikbaarheid')
            ->assertStatus(503)
            ->assertJson(['error' => 'calendar_unavailable', 'days' => []]);
    }

    #[Test]
    public function boeken_op_een_onbereikbare_agenda_wordt_geweigerd(): void
    {
        $this->agendaOnbereikbaar();

        $this->postJson('/afspraak/boeken', $this->payload())
            ->assertStatus(503)
            ->assertJson(['ok' => false]);

        $this->assertSame(0, Appointment::count(), 'liever geen afspraak dan een dubbele');
    }

    #[Test]
    public function een_onzinnige_datum_geeft_een_nette_validatiefout_en_geen_500(): void
    {
        // Publieke, ongeknepen route: Carbon::parse('x') gooide hier ongevangen, dus elke
        // bot met een rare querystring kreeg een 500.
        $this->getJson('/afspraak/beschikbaarheid?from=x')->assertStatus(422);
    }
}
