<?php

namespace Tests\Feature\Monitor;

use App\Services\Monitor\CheckRunner;
use Tests\TestCase;

/**
 * Het doorlaatbewijs voor een WAF mag alleen naar hosts die we zelf beheren.
 *
 * De monitor controleert ook sites van klanten. Stuurt hij het geheim overal mee, dan geeft hij
 * zijn sleutel weg aan elke partij die hij aanroept — en aan iedereen die hun logs kan lezen.
 * Dat is de reden dat deze klasse bestaat en dat dit bestand hem bewaakt: de dag dat iemand de
 * allowlist "even" weghaalt om een check groen te krijgen, valt deze test om.
 */
class BypassHeaderTest extends TestCase
{
    private function runner(?string $secret, string $hosts = 'bouwsteenwinkel.nl'): CheckRunner
    {
        config()->set('monitor.bypass.secret', $secret ?? '');
        config()->set('monitor.bypass.header', 'X-BG-Monitor');
        config()->set('monitor.bypass.hosts', array_values(array_filter(explode(',', $hosts))));

        return new CheckRunner();
    }

    public function test_stuurt_de_header_naar_een_eigen_host(): void
    {
        $this->assertSame(
            ['X-BG-Monitor' => 'geheim'],
            $this->runner('geheim')->bypassHeaderFor('https://bouwsteenwinkel.nl/')
        );
    }

    public function test_subdomein_valt_er_ook_onder(): void
    {
        $this->assertSame(
            ['X-BG-Monitor' => 'geheim'],
            $this->runner('geheim')->bypassHeaderFor('https://www.bouwsteenwinkel.nl/pad?x=1')
        );
    }

    public function test_stuurt_niets_naar_een_klantsite(): void
    {
        $this->assertSame([], $this->runner('geheim')->bypassHeaderFor('https://bonen-koffie.nl/'));
    }

    /** Een host die alleen maar eindigt op onze naam is niet van ons. */
    public function test_een_lookalike_host_krijgt_niets(): void
    {
        $this->assertSame([], $this->runner('geheim')->bypassHeaderFor('https://nietbouwsteenwinkel.nl/'));
    }

    public function test_zonder_geheim_gaat_er_niets_mee(): void
    {
        $this->assertSame([], $this->runner('')->bypassHeaderFor('https://bouwsteenwinkel.nl/'));
    }

    public function test_zonder_hosts_gaat_er_niets_mee(): void
    {
        $this->assertSame([], $this->runner('geheim', '')->bypassHeaderFor('https://bouwsteenwinkel.nl/'));
    }
}
