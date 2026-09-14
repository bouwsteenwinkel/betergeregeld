<?php

namespace Tests\Feature\Channels;

use App\Services\ChannelSites\PlaceBusinessFinder;
use Tests\TestCase;

/**
 * De adressen-drempel voor plaatspagina's kan per kanaal afwijken.
 *
 * Op de branche-sites trekken plaatspagina's vooral consumenten, daar blijft 10.000. Op
 * jouw-bedrijfswebsite.nl komt juist uit dorpen het doelgroepverkeer ("website laten maken
 * zelhem"); de algemene drempel zette daar 82% van de plaatsvertoningen op noindex.
 */
class PlaatsDrempelPerSiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'channel_places.index_min_addresses' => 10000,
            'channel_places.index_min_addresses_per_site' => ['bedrijfswebsite' => 1000],
        ]);
    }

    public function test_een_kanaal_met_uitzondering_krijgt_zijn_eigen_drempel(): void
    {
        $this->assertSame(1000, app(PlaceBusinessFinder::class)->minAdressen('bedrijfswebsite'));
    }

    public function test_een_kanaal_zonder_uitzondering_houdt_de_algemene_drempel(): void
    {
        $this->assertSame(10000, app(PlaceBusinessFinder::class)->minAdressen('loodgieter'));
    }

    public function test_zonder_kanaal_geldt_de_algemene_drempel(): void
    {
        $this->assertSame(10000, app(PlaceBusinessFinder::class)->minAdressen());
    }

    /** 0 als uitzondering betekent "geen grens", niet "val terug op de algemene". */
    public function test_nul_als_uitzondering_zet_de_grens_uit(): void
    {
        config(['channel_places.index_min_addresses_per_site' => ['bedrijfswebsite' => 0]]);

        $this->assertSame(0, app(PlaceBusinessFinder::class)->minAdressen('bedrijfswebsite'));
    }
}
