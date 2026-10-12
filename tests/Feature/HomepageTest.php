<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Homepage met de ProcesScan-positionering (12-10-2026).
 *
 * /nl rendert home.blade.php, /en houdt welcome.blade.php. De branchelinks komen uit
 * ChannelNetwork; die lijst zetten we hier in de cache, zodat de test geen channel_sites-tabel
 * nodig heeft. Geen RefreshDatabase (zie ChannelEventTest), wel de sqlite-grendel.
 */
class HomepageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->fail('Deze test mag alleen op de sqlite-wegwerpdatabase draaien.');
        }
        Cache::put('channel_network_live', [
            ['key' => 'rijschool', 'name' => 'Rijschool', 'domain' => 'jouw-rijschool.nl', 'url' => 'https://jouw-rijschool.nl'],
        ], 60);
    }

    public function test_nederlandse_homepage_toont_de_processcan_propositie(): void
    {
        $html = $this->get('/nl')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'), 'precies één H1');
        $this->assertStringContainsString('Uw bedrijf groeit.', $html);
        $this->assertStringContainsString('<title>Bedrijfsprocessen automatiseren', $html);
        $this->assertStringContainsString('<link rel="canonical" href="' . url('/nl') . '">', $html);

        // Primaire CTA naar het bestaande contactformulier, met het ProcesScan-onderwerp.
        $this->assertStringContainsString('href="/nl/contact?topic=processcan"', $html);
        $this->assertStringContainsString('data-track="homepage_process_scan_hero"', $html);
        $this->assertStringContainsString('data-track="homepage_process_scan_mid"', $html);
        $this->assertStringContainsString('data-track="homepage_process_scan_bottom"', $html);
        $this->assertStringContainsString('id="praktijkvoorbeelden"', $html);

        // De branchesites houden hun link vanaf het moederdomein.
        $this->assertStringContainsString('href="https://jouw-rijschool.nl"', $html);

        // Rekenvoorbeeld klopt: 3 x 20 min x 220 dagen x EUR 40 = EUR 8.800, niet 10.000.
        $this->assertStringContainsString('bijna € 9.000 per jaar', $html);
        $this->assertStringNotContainsString('10.000', $html);
    }

    public function test_faq_schema_is_geldige_json_ld(): void
    {
        $html = $this->get('/nl')->getContent();

        preg_match_all('#<script type="application/ld\+json">\s*(.*?)\s*</script>#s', $html, $m);
        $faq = collect($m[1])->map(fn ($j) => json_decode($j, true))->firstWhere('@type', 'FAQPage');

        $this->assertNotNull($faq, 'FAQPage-schema ontbreekt of is geen geldige JSON');
        $this->assertSame('https://schema.org', $faq['@context']);
        $this->assertCount(8, $faq['mainEntity']);
    }

    public function test_geen_gedachtestreepjes_in_de_zichtbare_tekst(): void
    {
        $html = $this->get('/nl')->getContent();
        $main = substr($html, strpos($html, '<main'), strpos($html, '</main>') - strpos($html, '<main'));

        $this->assertDoesNotMatchRegularExpression('/[\x{2014}\x{2013}]/u', strip_tags($main));
    }

    public function test_engelse_homepage_blijft_de_oude(): void
    {
        $this->get('/en')->assertOk()->assertSee('Custom websites &amp; software development', false);
    }

    public function test_processcan_staat_als_onderwerp_klaar(): void
    {
        $this->get('/nl/contact?topic=processcan')->assertOk()->assertSee('<option value="processcan" selected', false);
        $this->get('/nl/afspraak?onderwerp=processcan')->assertOk()->assertSee('Gratis ProcesScan');
    }
}
