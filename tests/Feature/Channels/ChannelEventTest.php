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

        // Symfony's test-UA is letterlijk "Symfony" en telt (terecht) als gereedschap;
        // de beacon telt alleen browsers. Dus: als browser aanmelden.
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36');
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

    public function test_bot_user_agent_wordt_stil_genegeerd(): void
    {
        foreach (['Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)', 'pc', ''] as $ua) {
            $res = $this->withHeaders(['User-Agent' => $ua])
                ->postJson($this->url, ['e' => 'page_view', 'p' => '/']);
            $res->assertNoContent();
        }
        $this->assertSame(0, ChannelEvent::count());
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

    /**
     * Het kiosk-scherm telt page_view-rijen. Op betergeregeld.com zelf is er geen
     * channel-site; die bezoeken moeten wel landen, onder een herkenbare site_key.
     */
    public function test_page_view_op_het_hoofddomein_wordt_opgeslagen_als_betergeregeld(): void
    {
        $this->postJson('/_ev', ['e' => 'page_view', 'p' => '/nl/ai-telefoniste?utm_source=x'])
            ->assertNoContent();

        $row = ChannelEvent::latest('id')->first();
        $this->assertNotNull($row);
        $this->assertSame('page_view', $row->event);
        $this->assertSame('betergeregeld', $row->site_key);
        $this->assertSame('/nl/ai-telefoniste', $row->path);
    }

    /** De beacon zelf: zonder dit script komt er nooit een page_view binnen. */
    public function test_de_page_view_beacon_rendert(): void
    {
        $html = view('partials.page-view-beacon')->render();

        $this->assertStringContainsString("e: 'page_view'", $html);
        $this->assertStringContainsString('"\/_ev"', $html);
    }
}
