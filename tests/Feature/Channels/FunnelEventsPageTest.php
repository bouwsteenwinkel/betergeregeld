<?php

namespace Tests\Feature\Channels;

use App\Filament\Pages\FunnelEvents;
use App\Models\ChannelEvent;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * De funnel-berekening van het admin-dashboard: unieke bezoeken per stap + ratio's.
 * Sqlite-wegwerp-DB met handmatige migratie (zie ChannelEventTest / SchedulingSchema).
 */
class FunnelEventsPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->fail('Alleen op de sqlite-wegwerpdatabase.');
        }
        (require database_path('migrations/2026_07_18_120000_create_channel_events_table.php'))->up();
    }

    public function test_funnel_telt_unieke_bezoeken_en_ratios(): void
    {
        // Bezoek A: hele funnel (site-x). B: tot planner (site-x). C: alleen voorbeeld (site-y).
        $this->seedVisit('a', 'site-x', ['preview_ready', 'planner_opened', 'appointment_booked']);
        $this->seedVisit('b', 'site-x', ['preview_ready', 'planner_opened']);
        $this->seedVisit('c', 'site-y', ['preview_ready', 'preview_ready']); // dubbele trigger = 1 bezoek

        $page = new FunnelEvents;
        $page->days = 30;
        $funnel = collect($page->getFunnel())->keyBy('event');

        $this->assertSame(3, $funnel['preview_ready']['visits']);      // A, B, C
        $this->assertSame(4, $funnel['preview_ready']['total']);       // ruwe events (C telt 2x)
        $this->assertSame(2, $funnel['planner_opened']['visits']);     // A, B
        $this->assertSame(1, $funnel['appointment_booked']['visits']); // A

        // Ratio's t.o.v. de vorige stap.
        $this->assertEqualsWithDelta(66.7, $funnel['planner_opened']['ratio'], 0.1);   // 2/3
        $this->assertSame(50.0, $funnel['appointment_booked']['ratio']);               // 1/2

        // Per site: site-x heeft de boeking (1/2 = 50%), site-y niet.
        $sites = $page->getPerSite()->keyBy('site');
        $this->assertSame(1, $sites['site-x']['booked']);
        $this->assertSame(50.0, $sites['site-x']['rate']);
        $this->assertSame(0, $sites['site-y']['booked']);
        $this->assertSame(0.0, $sites['site-y']['rate']);
    }

    private function seedVisit(string $ref, string $site, array $events): void
    {
        foreach ($events as $e) {
            ChannelEvent::create([
                'event' => $e,
                'site_key' => $site,
                'visit_ref' => str_pad($ref, 32, $ref),
                'path' => '/x',
                'created_at' => now(),
            ]);
        }
    }
}
