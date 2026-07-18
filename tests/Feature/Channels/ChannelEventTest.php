<?php

namespace Tests\Feature\Channels;

use App\Models\ChannelEvent;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * First-party event-beacon: de funnel-triggers landen in onze eigen DB (channel_events),
 * dataminimaal en zonder consent-drempel. Alleen allowlisted events; micro-events vallen weg.
 *
 * Geen RefreshDatabase (migrate:fresh loopt in dit project stuk op sqlite, zie
 * Tests\Concerns\SchedulingSchema): we draaien alleen de eigen Blueprint-migratie op de
 * :memory:-wegwerpdatabase, met een harde sqlite-grendel zodat de echte DB nooit geraakt wordt.
 */
class ChannelEventTest extends TestCase
{
    private string $url = '/_site/barbershop/_ev';

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->fail('Deze test mag alleen op de sqlite-wegwerpdatabase draaien.');
        }
        (require database_path('migrations/2026_07_18_120000_create_channel_events_table.php'))->up();
    }

    public function test_allowlisted_event_wordt_opgeslagen(): void
    {
        $res = $this->postJson($this->url, [
            'e' => 'preview_ready',
            'p' => '/voorbeeld-maken?gclid=SHOULD_BE_STRIPPED',
            'd' => ['seconds' => 7, 'evil' => ['nested' => 'x']],
        ]);

        $res->assertNoContent(); // 204

        $row = ChannelEvent::latest('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('preview_ready', $row->event);
        $this->assertSame('barbershop', $row->site_key);
        // Query-string (met gclid) wordt gestript — geen PII in het pad.
        $this->assertSame('/voorbeeld-maken', $row->path);
        // Alleen scalaire params; geneste rommel valt weg.
        $this->assertSame(['seconds' => 7], $row->params);
        $this->assertNotEmpty($row->visit_ref);
    }

    public function test_onbekend_event_wordt_stil_genegeerd(): void
    {
        $res = $this->postJson($this->url, ['e' => 'section_view', 'p' => '/']);

        $res->assertNoContent();
        $this->assertSame(0, ChannelEvent::count());
    }

    public function test_bestaande_visit_ref_uit_de_sessie_wordt_hergebruikt(): void
    {
        // Een bestaande sessie-ref wordt hergebruikt (groepeert events per bezoek),
        // i.p.v. per event een nieuwe te genereren.
        $ref = str_repeat('a', 32);
        $this->withSession(['bg_ev_ref' => $ref])
            ->postJson($this->url, ['e' => 'appointment_booked', 'p' => '/afspraak-bevestigd'])
            ->assertNoContent();

        $this->assertSame($ref, ChannelEvent::latest('id')->first()->visit_ref);
    }
}
