<?php

namespace Tests\Feature\Scheduling;

use App\Mail\AppointmentReminder;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * De herinnerings-cadans: 2 dagen vooraf en de ochtend van de afspraak zelf.
 *
 * Deze keten had geen enkele test terwijl hij ongezien op de scheduler draait: valt hij
 * stil of stuurt hij dubbel, dan merkt niemand het tot een klant zich meldt. De klok
 * staat vast (SchedulingTestCase::NU = donderdag 2026-07-16 08:00).
 */
class AppointmentRemindersTest extends SchedulingTestCase
{
    private function draai(): void
    {
        $this->artisan('appointments:send-reminders')->assertSuccessful();
    }

    /** Een afspraak die lang genoeg vooruit staat om beide herinneringen te verdienen. */
    private function afspraakOp(string $date, string $time, array $overrides = []): Appointment
    {
        return $this->afspraak($date, $time, array_merge([
            'name'         => 'Jan Jansen',
            'email'        => 'jan@example.nl',
            'cancel_token' => 'tok' . str_repeat('a', 45),
            // Ruim vóór elk verzendmoment geboekt: de "was het al geboekt?"-eis mag geen
            // rol spelen in tests die over de timing gaan.
            'created_at'   => $this->moment('2026-07-01', '09:00'),
        ], $overrides));
    }

    // ── 2 dagen vooraf ───────────────────────────────────────────────────

    #[Test]
    public function twee_dagen_voor_de_afspraak_gaat_de_eerste_herinnering(): void
    {
        // Zaterdag 11:00; het verzendmoment is dus donderdag 11:00.
        $appt = $this->afspraakOp('2026-07-18', '11:00');

        $this->travelTo($this->moment('2026-07-16', '11:00'));
        $this->draai();

        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === '2d' && $m->hasTo('jan@example.nl'));
        $this->assertNotNull($appt->fresh()->reminder_2d_sent_at);
    }

    #[Test]
    public function voor_het_verzendmoment_gaat_er_niets(): void
    {
        $this->afspraakOp('2026-07-18', '11:00');

        // Een uur te vroeg: het venster opent pas om 11:00.
        $this->travelTo($this->moment('2026-07-16', '10:00'));
        $this->draai();

        Mail::assertNothingSent();
    }

    #[Test]
    public function de_herinnering_gaat_maar_een_keer(): void
    {
        $this->afspraakOp('2026-07-18', '11:00');

        $this->travelTo($this->moment('2026-07-16', '11:00'));
        $this->draai();
        $this->draai();

        Mail::assertSentCount(1);
    }

    // ── De dag van de afspraak ───────────────────────────────────────────

    #[Test]
    public function op_de_dag_zelf_gaat_de_tweede_herinnering_om_acht_uur(): void
    {
        // Vrijdag 15:00. De dag-van-mail hoort vrijdagochtend 08:00 te vertrekken, en
        // dat is géén vast aantal uren vooraf — precies wat de oude 24u/1u-logica niet
        // kon uitdrukken.
        $appt = $this->afspraakOp('2026-07-17', '15:00', [
            'reminder_2d_sent_at' => $this->moment('2026-07-15', '15:00'),
        ]);

        $this->travelTo($this->moment('2026-07-17', '08:00'));
        $this->draai();

        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === 'day_of');
        $this->assertNotNull($appt->fresh()->reminder_day_of_sent_at);
    }

    #[Test]
    public function voor_acht_uur_gaat_de_dag_van_mail_nog_niet(): void
    {
        $this->afspraakOp('2026-07-17', '15:00', [
            'reminder_2d_sent_at' => $this->moment('2026-07-15', '15:00'),
        ]);

        $this->travelTo($this->moment('2026-07-17', '07:45'));
        $this->draai();

        Mail::assertNothingSent();
    }

    #[Test]
    public function een_vroege_afspraak_krijgt_zijn_mail_naar_voren_geschoven(): void
    {
        // Afspraak om 08:00: het ochtenduur valt samen met de aanvang, dus de mail zou
        // precies bij binnenkomst van de klant vertrekken. Hij schuift naar 07:00.
        $appt = $this->afspraakOp('2026-07-17', '08:00', [
            'reminder_2d_sent_at' => $this->moment('2026-07-15', '08:00'),
        ]);

        $this->travelTo($this->moment('2026-07-17', '07:00'));
        $this->draai();

        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === 'day_of');
        $this->assertNotNull($appt->fresh()->reminder_day_of_sent_at);
    }

    // ── Samenloop en uitval ──────────────────────────────────────────────

    #[Test]
    public function na_scheduler_uitval_gaat_alleen_de_dag_van_mail_en_niet_alsnog_die_van_twee_dagen(): void
    {
        // Scheduler lag stil; beide vensters staan open op de dag zelf. Er mag dan geen
        // "je afspraak staat gepland"-mail meer uit over iets van vandaag.
        $appt = $this->afspraakOp('2026-07-17', '15:00');

        $this->travelTo($this->moment('2026-07-17', '09:00'));
        $this->draai();
        $this->draai();

        Mail::assertSentCount(1);
        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === 'day_of');

        // De 2d-stempel is afgestempeld zonder verzending; anders stuurt de volgende run
        // hem alsnog.
        $this->assertNotNull($appt->fresh()->reminder_2d_sent_at);
    }

    #[Test]
    public function na_uitval_stuurt_een_vroege_run_op_de_dag_zelf_geen_losse_twee_dagen_mail(): void
    {
        // Het venijn zit vóór 08:00: de dag-van-mail is dan nog niet toe, maar het
        // 2d-venster staat na uitval al dagen open. Zonder voorrang vertrok die om 06:00
        // ("we spreken elkaar vrijdag" — over vandaag), gevolgd door de dag-van-mail om
        // 08:00. Twee herinneringen binnen twee uur.
        $appt = $this->afspraakOp('2026-07-17', '15:00');

        $this->travelTo($this->moment('2026-07-17', '06:00'));
        $this->draai();

        Mail::assertNothingSent();
        $this->assertNotNull($appt->fresh()->reminder_2d_sent_at, 'afgestempeld, niet verstuurd');

        $this->travelTo($this->moment('2026-07-17', '08:00'));
        $this->draai();

        Mail::assertSentCount(1);
        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === 'day_of');
    }

    #[Test]
    public function wie_binnen_het_venster_boekt_krijgt_geen_herinnering_aan_iets_dat_hij_net_inplande(): void
    {
        // Vanochtend om 07:00 geboekt voor vanmiddag 15:00: beide verzendmomenten lagen
        // al achter ons toen de boeking binnenkwam. De bevestigingsmail is nog vers.
        $this->afspraakOp('2026-07-16', '15:00', [
            'created_at' => $this->moment('2026-07-16', '07:00'),
        ]);

        $this->travelTo($this->moment('2026-07-16', '09:00'));
        $this->draai();

        Mail::assertNothingSent();
    }

    #[Test]
    public function een_geannuleerde_afspraak_krijgt_geen_herinnering(): void
    {
        $this->afspraakOp('2026-07-18', '11:00', ['status' => 'cancelled']);

        $this->travelTo($this->moment('2026-07-16', '11:00'));
        $this->draai();

        Mail::assertNothingSent();
    }

    #[Test]
    public function een_afspraak_in_het_verleden_krijgt_geen_herinnering(): void
    {
        $this->afspraakOp('2026-07-15', '11:00');

        $this->travelTo($this->moment('2026-07-16', '11:00'));
        $this->draai();

        Mail::assertNothingSent();
    }

    #[Test]
    public function een_mislukte_mail_laat_de_stempel_leeg_zodat_de_volgende_run_het_opnieuw_probeert(): void
    {
        $appt = $this->afspraakOp('2026-07-18', '11:00');

        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->travelTo($this->moment('2026-07-16', '11:00'));
        $this->draai();

        // Zou de claim blijven staan, dan kreeg deze klant nooit meer een herinnering.
        $this->assertNull($appt->fresh()->reminder_2d_sent_at);
    }

    #[Test]
    public function de_zomertijdgrens_verschuift_de_mail_niet_naar_een_ander_klokuur(): void
    {
        // In de nacht van 24 op 25 oktober 2026 gaat de klok een uur terug. "2 dagen
        // vooraf" moet op hetzelfde klokuur uitkomen; subHours(48) zou er 10:00 van maken.
        $this->travelTo(CarbonImmutable::parse('2026-10-24 10:30', self::TZ));

        $appt = $this->afspraakOp('2026-10-26', '11:00', [
            'created_at' => $this->moment('2026-10-01', '09:00'),
        ]);

        $this->draai();
        Mail::assertNothingSent();

        $this->travelTo(CarbonImmutable::parse('2026-10-24 11:00', self::TZ));
        $this->draai();

        Mail::assertSent(AppointmentReminder::class, fn ($m) => $m->kind === '2d');
        $this->assertNotNull($appt->fresh()->reminder_2d_sent_at);
    }
}
