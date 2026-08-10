<?php

namespace App\Support;

use App\Http\Middleware\SetLocale;

/**
 * Welke taalvarianten mag een pagina bij Google aanmelden?
 *
 * WAAROM DIT BESTAAT. De layout zette voor élke ondersteunde taal een hreflang-alternate neer en
 * wisselde daarvoor blind het taalvoorvoegsel in de URL om. Voor de vaste pagina's klopt dat — die
 * bestaan in het Nederlands én het Engels — maar niet voor de blog. Daar staan 357 gepubliceerde
 * artikelen die alleen in het Nederlands bestaan (de kanaalartikelen van bakkerij, advocaat,
 * apotheek liften mee op de hoofdsite), en elk van die pagina's beloofde Google een Engelse versie
 * die 410 Gone teruggeeft.
 *
 * Search Console telde daardoor 303 pagina's onder "Niet gevonden (404)" — een 410 valt in diezelfde
 * bak — en de validatie van 03-07-2026 mislukte op 11-07, want aan die URL's was niets veranderd.
 *
 * De regel staat hier en niet in de Blade omdat een keuze die je kunt uitleggen ook te testen hoort
 * te zijn; in een template is dat niet zo.
 */
class Hreflang
{
    /**
     * De talen die deze pagina mag aanmelden.
     *
     * Niets opgegeven = alle ondersteunde talen, want voor de meeste pagina's is dat waar. Wél iets
     * opgegeven = precies die, met alles wat we niet ondersteunen eruit gefilterd; een view die zich
     * vergist kan zo geen taal adverteren die de site niet eens kent.
     *
     * @param  array<int,string>|null  $beschikbaar
     * @return array<int,string>
     */
    public static function alternates(?array $beschikbaar = null): array
    {
        if ($beschikbaar === null) {
            return SetLocale::SUPPORTED;
        }

        return array_values(array_intersect(SetLocale::SUPPORTED, array_unique($beschikbaar)));
    }

    /**
     * Waar x-default heen wijst.
     *
     * Het Nederlands als dat er is — dat is de hoofdtaal van de site. Anders de eerste taal die er
     * wél is, want naar een URL wijzen die we net als onbestaand hebben aangemerkt is precies de
     * fout die we aan het oplossen zijn. Niets beschikbaar = geen x-default.
     *
     * @param  array<int,string>  $alternates
     */
    public static function xDefault(array $alternates): ?string
    {
        if ($alternates === []) {
            return null;
        }

        return in_array('nl', $alternates, true) ? 'nl' : $alternates[0];
    }
}
