<?php

namespace Tests\Feature\Blog;

use App\Support\Hreflang;
use Tests\TestCase;

/**
 * hreflang mag alleen talen noemen die er echt zijn.
 *
 * De layout zette voorheen voor élke ondersteunde taal een alternate neer, met het taalvoorvoegsel
 * blind omgewisseld in de URL. Voor de vaste pagina's klopt dat — die bestaan in beide talen — maar
 * niet voor de blog: 357 gepubliceerde artikelen bestaan alleen in het Nederlands (de
 * kanaalartikelen van bakkerij, advocaat en apotheek liften mee op de hoofdsite). Die pagina's
 * beloofden Google een Engelse versie die 410 Gone teruggeeft.
 *
 * Search Console telde daardoor 303 pagina's onder "Niet gevonden (404)" — een 410 valt in diezelfde
 * bak — en de validatie van 03-07-2026 mislukte op 11-07, want aan die URL's veranderde niets.
 */
class HreflangAlternatesTest extends TestCase
{
    public function test_zonder_opgave_blijven_alle_ondersteunde_talen_staan(): void
    {
        $this->assertSame(['nl', 'en'], Hreflang::alternates(null));
    }

    /** Het geval waar het om begonnen is: een post die alleen in het Nederlands bestaat. */
    public function test_alleen_nederlands_levert_geen_engelse_alternate(): void
    {
        $this->assertSame(['nl'], Hreflang::alternates(['nl']));
    }

    /** Een view die zich vergist mag geen taal adverteren die de site niet eens kent. */
    public function test_een_niet_ondersteunde_taal_wordt_genegeerd(): void
    {
        $this->assertSame(['nl'], Hreflang::alternates(['nl', 'de', 'fr']));
    }

    public function test_dubbelingen_leveren_geen_dubbele_regels(): void
    {
        $this->assertSame(['nl'], Hreflang::alternates(['nl', 'nl']));
    }

    public function test_x_default_is_nederlands_als_dat_bestaat(): void
    {
        $this->assertSame('nl', Hreflang::xDefault(['nl', 'en']));
    }

    /** Nooit naar een taal wijzen die we net als onbestaand hebben aangemerkt. */
    public function test_x_default_valt_terug_op_wat_er_wel_is(): void
    {
        $this->assertSame('en', Hreflang::xDefault(['en']));
    }

    public function test_zonder_talen_geen_x_default(): void
    {
        $this->assertNull(Hreflang::xDefault([]));
    }

    /** De volgorde volgt de site, niet de toevallige volgorde waarin een view ze aanlevert. */
    public function test_volgorde_is_die_van_de_site(): void
    {
        $this->assertSame(['nl', 'en'], Hreflang::alternates(['en', 'nl']));
    }
}
