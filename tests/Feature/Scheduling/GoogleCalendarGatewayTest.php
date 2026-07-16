<?php

namespace Tests\Feature\Scheduling;

use App\Models\Appointment;
use App\Services\Scheduling\CalendarSyncException;
use App\Services\Scheduling\CalendarUnavailableException;
use App\Services\Scheduling\GoogleCalendarGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\Scheduling\SchedulingTestCase;

/**
 * De Google-koppeling zelf, met een nagebootste Google. Draait om één vraag: kan een
 * storing er ooit uitzien als "de agenda is leeg"? Dat is de fout die dubbele
 * boekingen oplevert, en hij is van buitenaf onzichtbaar.
 */
class GoogleCalendarGatewayTest extends SchedulingTestCase
{
    private function gateway(): GoogleCalendarGateway
    {
        config([
            'scheduling.google.enabled'     => true,
            'scheduling.google.client_id'   => 'cid',
            'scheduling.google.client_secret' => 'secret',
            'scheduling.google.calendar_id' => 'info@bouwsteenwinkel.nl',
            'scheduling.google.send_updates' => 'all',
        ]);

        // Een geldig access-token in de cache: dan hoeft geen enkele test de
        // OAuth-dans na te bootsen om bij de agenda-acties te komen.
        Cache::put('google_agenda_access_token', 'at-123', now()->addMinutes(50));

        return $this->app->make(GoogleCalendarGateway::class);
    }

    // ── Bezetting uitlezen ───────────────────────────────────────────────

    #[Test]
    public function een_agenda_id_met_punten_erin_levert_de_bezetting_gewoon_op(): void
    {
        // De regressie: de bezetting werd opgehaald met dot-notatie
        // ($resp->json('calendars.' . $id . '.busy')), en die splitst het pad op punten.
        // Bij een agenda-id als info@bouwsteenwinkel.nl werd dat calendars →
        // info@bouwsteenwinkel → nl → busy: altijd leeg. Elk bezet uur werd dan als vrij
        // aangeboden, zonder één logregel.
        Http::fake(['*/freeBusy' => Http::response([
            'calendars' => [
                'info@bouwsteenwinkel.nl' => [
                    'busy' => [
                        ['start' => '2026-07-17T11:00:00+02:00', 'end' => '2026-07-17T12:00:00+02:00'],
                    ],
                ],
            ],
        ])]);

        $busy = $this->gateway()->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00'));

        $this->assertCount(1, $busy);
        $this->assertSame('2026-07-17 11:00:00', $busy[0]['start']->setTimezone(self::TZ)->toDateTimeString());
    }

    #[Test]
    public function primary_opvragen_werkt_ook_als_google_op_het_echte_adres_keyt(): void
    {
        // Dit IS de productie-instelling (GOOGLE_CALENDAR_ID=primary). Google mag de alias
        // 'primary' oplossen en de map keyen op de canonieke agenda-id. Een strikte
        // lookup op 'primary' mist die sleutel dan, en met fail-closed betekent dat een
        // 503 op élke widget-load: een totale storing in plaats van de stille die we
        // repareerden.
        config(['scheduling.google.calendar_id' => 'primary']);

        Http::fake(['*/freeBusy' => Http::response([
            'calendars' => [
                'info@bouwsteenwinkel.nl' => [
                    'busy' => [['start' => '2026-07-17T11:00:00+02:00', 'end' => '2026-07-17T12:00:00+02:00']],
                ],
            ],
        ])]);

        $gateway = $this->gateway();
        config(['scheduling.google.calendar_id' => 'primary']);

        $busy = $gateway->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00'));

        $this->assertCount(1, $busy);
    }

    #[Test]
    public function een_lege_agenda_levert_een_lege_bezetting_op(): void
    {
        Http::fake(['*/freeBusy' => Http::response([
            'calendars' => ['info@bouwsteenwinkel.nl' => ['busy' => []]],
        ])]);

        $this->assertSame([], $this->gateway()->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00')));
    }

    #[Test]
    public function een_storing_bij_google_is_geen_lege_agenda(): void
    {
        Http::fake(['*/freeBusy' => Http::response('boem', 500)]);

        $this->expectException(CalendarUnavailableException::class);

        $this->gateway()->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00'));
    }

    #[Test]
    public function een_fout_op_de_agenda_zelf_is_geen_lege_agenda(): void
    {
        // Google antwoordt met HTTP 200, maar meldt per agenda een fout (bijv. notFound
        // na het intrekken van rechten). Zonder deze controle las dat als "niets bezet".
        Http::fake(['*/freeBusy' => Http::response([
            'calendars' => ['info@bouwsteenwinkel.nl' => ['errors' => [['reason' => 'notFound']], 'busy' => []]],
        ])]);

        $this->expectException(CalendarUnavailableException::class);

        $this->gateway()->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00'));
    }

    #[Test]
    public function zonder_token_weten_we_de_bezetting_niet_en_zeggen_we_dat_ook(): void
    {
        Cache::forget('google_agenda_access_token');
        Http::fake();

        $this->expectException(CalendarUnavailableException::class);

        $gateway = $this->app->make(GoogleCalendarGateway::class);
        config(['scheduling.google.client_id' => 'cid']);
        $gateway->busyPeriods($this->moment(self::VRIJDAG, '09:00'), $this->moment(self::VRIJDAG, '17:00'));
    }

    // ── Events schrijven ─────────────────────────────────────────────────

    #[Test]
    public function een_geweigerd_event_valt_niet_stil_maar_gooit(): void
    {
        Http::fake(['*/events*' => Http::response(['error' => 'nope'], 403)]);

        $this->expectException(CalendarSyncException::class);

        $this->gateway()->createMeetEvent($this->afspraak(self::VRIJDAG, '11:00'));
    }

    #[Test]
    public function de_klant_wordt_door_google_uitgenodigd(): void
    {
        Http::fake(['*/events*' => Http::response(['id' => 'evt-1', 'hangoutLink' => 'https://meet.google.com/abc-defg-hij'])]);

        $appt = $this->afspraak(self::VRIJDAG, '11:00', ['email' => 'klant@example.nl']);
        $res  = $this->gateway()->createMeetEvent($appt);

        $this->assertSame('evt-1', $res['event_id']);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $res['meet_url']);

        Http::assertSent(function ($request) {
            // sendUpdates=all is het verschil tussen "het staat in mijn agenda" en "de
            // klant heeft het ook".
            return str_contains($request->url(), 'sendUpdates=all')
                && $request['attendees'][0]['email'] === 'klant@example.nl';
        });
    }

    #[Test]
    public function een_al_verwijderd_event_is_geen_fout(): void
    {
        // De annuleerlink uit de mail kan twee keer aangeklikt worden; 410 Gone is dan
        // gewoon de gewenste eindtoestand.
        Http::fake(['*/events/*' => Http::response('', 410)]);

        $this->gateway()->deleteEvent('evt-1');

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function een_mislukte_verwijdering_valt_niet_stil(): void
    {
        // Blijft het event staan, dan houdt het dat uur via free/busy permanent bezet
        // terwijl de afspraak geannuleerd is. Dat lost zich niet vanzelf op.
        Http::fake(['*/events/*' => Http::response('boem', 500)]);

        $this->expectException(CalendarSyncException::class);

        $this->gateway()->deleteEvent('evt-1');
    }
}
