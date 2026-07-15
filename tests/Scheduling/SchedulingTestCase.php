<?php

namespace Tests\Scheduling;

use App\Http\Middleware\CaptureAdAttribution;
use App\Models\Appointment;
use App\Services\Scheduling\Contracts\CalendarGateway;
use App\Services\Scheduling\StubCalendarGateway;
use Carbon\CarbonImmutable;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\SchedulingSchema;
use Tests\TestCase;

/**
 * Gedeelde basis voor de afspraken-keten: bevroren klok, verse sqlite-tabellen, en
 * gegarandeerd géén echte mail of agenda-call.
 *
 * De klok staat vast omdat vrijwel elke verwachting in deze suite relatief is aan "nu"
 * (voorlooptijd, horizon, verlopen holds). Met een lopende klok zou een test die om
 * 23:59 draait ineens over een dagrand vallen.
 */
abstract class SchedulingTestCase extends TestCase
{
    use SchedulingSchema;

    /** Donderdag (ISO-weekdag 4), 08:00. Ver genoeg van de dagranden voor de 4u voorlooptijd. */
    protected const NU = '2026-07-16 08:00:00';

    protected const TZ = 'Europe/Amsterdam';

    /** Vrijdag: de standaard-testdag, want die ligt buiten de voorlooptijd van vandaag. */
    protected const VRIJDAG = '2026-07-17';

    /** Zaterdag: geen werkdag in default_hours, dus leeg tenzij een 'extra'-uitzondering. */
    protected const ZATERDAG = '2026-07-18';

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootSchedulingSchema();
        $this->travelTo(CarbonImmutable::parse(self::NU, self::TZ));

        Mail::fake();

        // De container kiest normaal zelf tussen Google en de stub. Hier hard vastgezet:
        // een test mag nooit afhangen van GOOGLE_CALENDAR_ENABLED in de omgeving.
        $this->app->bind(CalendarGateway::class, fn () => new StubCalendarGateway());

        config([
            'scheduling.timezone'         => self::TZ,
            'scheduling.slot_minutes'     => 60,
            'scheduling.meeting_minutes'  => 60,
            'scheduling.min_notice_hours' => 4,
            'scheduling.horizon_days'     => 21,
            'scheduling.buffer_minutes'   => 0,
            'scheduling.default_hours'    => [
                1 => [['09:00', '17:00']],
                2 => [['09:00', '17:00']],
                3 => [['09:00', '17:00']],
                4 => [['09:00', '17:00']],
                5 => [['09:00', '17:00']],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        $this->dropSchedulingSchema();

        parent::tearDown();
    }

    /** Een moment op de testdag, in de agenda-tijdzone. */
    protected function moment(string $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse("{$date} {$time}", self::TZ);
    }

    /**
     * Zet een uitzondering weg met een KALE datum in de date-kolom.
     *
     * Niet via AvailabilityException::create(): de 'date'-cast laat Laravel er
     * '2026-07-17 00:00:00' van maken, en sqlite bewaart dat letterlijk. De echte kolom
     * is een MariaDB DATE, die de tijd afkapt tot '2026-07-17'. SlotEngine zoekt met
     * whereBetween op kale datums, dus zonder deze afvlakking zou de uitzondering hier
     * onvindbaar zijn terwijl hij in productie prima werkt: een testartefact, geen bug.
     */
    protected function uitzondering(string $date, string $kind, ?string $start = null, ?string $end = null): void
    {
        DB::table('availability_exceptions')->insert([
            'date'       => $date,
            'kind'       => $kind,
            'start_time' => $start,
            'end_time'   => $end,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Stuurt de klik-herkomst-cookie mee zoals een echte browser hem terugstuurt.
     *
     * De withCredentials() is geen franje: postJson() stuurt standaard GEEN cookies mee
     * (prepareCookiesForJsonRequest geeft dan een lege lijst). Zonder deze regel komt de
     * herkomst dus nooit aan en zou een test die 'geen gclid' verwacht groen staan om de
     * verkeerde reden. De echte widget doet een same-origin fetch(), en die stuurt de
     * cookie wél mee, dus dit is het eerlijke model.
     *
     * De waarde gaat er als platte JSON in: withCookie() laat het testframework zelf
     * versleutelen, precies zoals EncryptCookies dat op een echt request verwacht.
     *
     * @param  array<string,string>  $attributie
     */
    protected function metHerkomstCookie(array $attributie): static
    {
        return $this->withCredentials()
            ->withCookie(CaptureAdAttribution::COOKIE, (string) json_encode($attributie));
    }

    /** Zet een afspraak rechtstreeks in de DB (dus buiten BookingService om). */
    protected function afspraak(string $date, string $time, array $overrides = []): Appointment
    {
        $start = $this->moment($date, $time);

        return Appointment::create(array_merge([
            'name'      => 'Bezet',
            'email'     => 'bezet@example.nl',
            'starts_at' => $start,
            'ends_at'   => $start->addHour(),
            'status'    => 'booked',
        ], $overrides));
    }
}
