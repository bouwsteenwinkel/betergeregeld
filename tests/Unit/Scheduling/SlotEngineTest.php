<?php

namespace Tests\Unit\Scheduling;

use App\Models\AvailabilityRule;
use App\Services\Scheduling\Contracts\CalendarGateway;
use App\Services\Scheduling\SlotEngine;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * SlotEngine: wekelijks sjabloon (of default_hours) minus uitzonderingen, bezette
 * afspraken en externe agenda, begrensd door voorlooptijd en horizon.
 */
class SlotEngineTest extends SchedulingTestCase
{
    private function engine(): SlotEngine
    {
        return $this->app->make(SlotEngine::class);
    }

    /** De sloten van één dag. */
    private function slotsOp(string $date): array
    {
        $dag = $this->moment($date, '00:00');

        return $this->engine()->slots($dag, $dag->endOfDay())[$date] ?? [];
    }

    // ── Openingstijden ───────────────────────────────────────────────────

    #[Test]
    public function openingstijden_komen_uit_availability_rules(): void
    {
        AvailabilityRule::create(['weekday' => 5, 'start_time' => '10:00', 'end_time' => '13:00', 'active' => true]);

        // 10:00-13:00 bij een uur per gesprek: 12:00 is de laatste die nog past.
        $this->assertSame(['10:00', '11:00', '12:00'], $this->slotsOp(self::VRIJDAG));
    }

    #[Test]
    public function een_gevulde_regeltabel_zet_de_default_hours_volledig_opzij(): void
    {
        // Alleen een regel voor vrijdag. Donderdag heeft wél default_hours, maar zodra er
        // regels staan is de config-fallback uit: anders zou een beheerder die één dag
        // openzet stilzwijgend de hele werkweek open houden.
        AvailabilityRule::create(['weekday' => 5, 'start_time' => '10:00', 'end_time' => '13:00', 'active' => true]);

        $this->assertSame([], $this->slotsOp('2026-07-20')); // maandag, wél in default_hours
    }

    #[Test]
    public function een_inactieve_regel_opent_niets(): void
    {
        AvailabilityRule::create(['weekday' => 5, 'start_time' => '10:00', 'end_time' => '13:00', 'active' => false]);

        // De tabel telt als leeg (alleen actieve regels tellen), dus de fallback pakt weer.
        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
            $this->slotsOp(self::VRIJDAG)
        );
    }

    #[Test]
    public function zonder_regels_valt_de_engine_terug_op_config_default_hours(): void
    {
        $this->assertSame(0, AvailabilityRule::count());

        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
            $this->slotsOp(self::VRIJDAG)
        );
        $this->assertSame([], $this->slotsOp(self::ZATERDAG), 'zaterdag staat niet in default_hours');
    }

    // ── Uitzonderingen ───────────────────────────────────────────────────

    #[Test]
    public function een_block_zonder_tijden_sluit_de_hele_dag(): void
    {
        $this->uitzondering(self::VRIJDAG, 'block');

        $this->assertSame([], $this->slotsOp(self::VRIJDAG));
        $this->assertNotEmpty($this->slotsOp('2026-07-20'), 'alleen die ene dag mag dicht');
    }

    #[Test]
    public function een_block_met_tijden_knipt_alleen_dat_venster_weg(): void
    {
        $this->uitzondering(self::VRIJDAG, 'block', '12:00', '14:00');

        // 11:00 eindigt precies om 12:00 en overlapt dus niet; 12:00 en 13:00 wel.
        $this->assertSame(
            ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
            $this->slotsOp(self::VRIJDAG)
        );
    }

    #[Test]
    public function een_extra_venster_opent_een_dag_die_normaal_dicht_is(): void
    {
        $this->uitzondering(self::ZATERDAG, 'extra', '10:00', '12:00');

        $this->assertSame(['10:00', '11:00'], $this->slotsOp(self::ZATERDAG));
    }

    // ── Voorlooptijd en horizon ──────────────────────────────────────────

    #[Test]
    public function min_notice_hours_snijdt_de_eerstvolgende_uren_weg(): void
    {
        // Nu = donderdag 08:00, voorlooptijd 4u, dus alles vóór 12:00 valt af.
        $this->assertSame(['12:00', '13:00', '14:00', '15:00', '16:00'], $this->slotsOp('2026-07-16'));

        config(['scheduling.min_notice_hours' => 0]);

        $this->assertSame(
            ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
            $this->slotsOp('2026-07-16'),
            'zonder voorlooptijd is de hele werkdag weer boekbaar'
        );
    }

    #[Test]
    public function een_slot_binnen_de_voorlooptijd_is_niet_beschikbaar(): void
    {
        $this->assertFalse($this->engine()->isSlotAvailable($this->moment('2026-07-16', '11:00')));
        $this->assertTrue($this->engine()->isSlotAvailable($this->moment('2026-07-16', '12:00')));
    }

    #[Test]
    public function horizon_days_begrenst_hoe_ver_vooruit_je_kunt_boeken(): void
    {
        config(['scheduling.horizon_days' => 2]);

        // Donderdag + vrijdag; zaterdag (dag 2) is geen werkdag en valt vanzelf weg.
        $this->assertSame(['2026-07-16', '2026-07-17'], array_keys($this->engine()->slots()));
    }

    #[Test]
    public function de_standaard_horizon_reikt_precies_tot_de_ingestelde_dag(): void
    {
        $dagen   = array_keys($this->engine()->slots());
        $laatste = CarbonImmutable::parse(end($dagen), self::TZ);

        $this->assertTrue($laatste->lte(CarbonImmutable::now(self::TZ)->addDays(21)->endOfDay()));
        $this->assertSame('2026-08-06', end($dagen), 'donderdag, precies 21 dagen na nu');
    }

    // ── Bezetting ────────────────────────────────────────────────────────

    #[Test]
    public function een_geboekt_slot_verdwijnt_uit_de_lijst(): void
    {
        $this->assertContains('11:00', $this->slotsOp(self::VRIJDAG));

        $this->afspraak(self::VRIJDAG, '11:00');

        $this->assertSame(
            ['09:00', '10:00', '12:00', '13:00', '14:00', '15:00', '16:00'],
            $this->slotsOp(self::VRIJDAG)
        );
        $this->assertFalse($this->engine()->isSlotAvailable($this->moment(self::VRIJDAG, '11:00')));
    }

    #[Test]
    public function een_geannuleerde_afspraak_bezet_niets(): void
    {
        $this->afspraak(self::VRIJDAG, '11:00', ['status' => 'cancelled']);

        $this->assertContains('11:00', $this->slotsOp(self::VRIJDAG));
    }

    #[Test]
    public function een_hold_bezet_het_slot_tot_hij_verloopt(): void
    {
        $hold = $this->afspraak(self::VRIJDAG, '11:00', [
            'status'          => 'held',
            'hold_expires_at' => CarbonImmutable::now()->addMinutes(10),
        ]);

        $this->assertNotContains('11:00', $this->slotsOp(self::VRIJDAG), 'een geldige hold houdt het slot bezet');

        $hold->update(['hold_expires_at' => CarbonImmutable::now()->subMinute()]);

        $this->assertContains('11:00', $this->slotsOp(self::VRIJDAG), 'een verlopen hold geeft het slot terug');
    }

    #[Test]
    public function externe_agenda_bezetting_haalt_het_slot_weg(): void
    {
        // Bewijst dat de engine de CalendarGateway écht raadpleegt; met de stub (die
        // altijd leeg teruggeeft) zou een kapotte koppeling ongemerkt blijven.
        $start = $this->moment(self::VRIJDAG, '13:00');
        $this->app->bind(CalendarGateway::class, fn () => new class($start) extends \App\Services\Scheduling\StubCalendarGateway
        {
            public function __construct(private CarbonImmutable $start) {}

            public function busyPeriods(CarbonInterface $from, CarbonInterface $to): array
            {
                return [['start' => $this->start, 'end' => $this->start->addHour()]];
            }
        });

        $this->assertNotContains('13:00', $this->slotsOp(self::VRIJDAG));
        $this->assertContains('14:00', $this->slotsOp(self::VRIJDAG));
    }

    #[Test]
    public function buffer_minutes_houdt_de_belendende_sloten_vrij(): void
    {
        config(['scheduling.buffer_minutes' => 30]);
        $this->afspraak(self::VRIJDAG, '11:00');

        $slots = $this->slotsOp(self::VRIJDAG);

        $this->assertNotContains('10:00', $slots, 'eindigt om 11:00, valt binnen de buffer');
        $this->assertNotContains('12:00', $slots, 'begint om 12:00, valt binnen de buffer');
        $this->assertContains('09:00', $slots);
        $this->assertContains('13:00', $slots);
    }
}
