<?php

namespace App\Services\Scheduling;

use RuntimeException;

/**
 * Een agenda-schrijfactie (event aanmaken of verwijderen) is mislukt.
 *
 * Wordt door BookingService gevangen: de afspraak zelf blijft staan (de klant heeft
 * zijn moment), maar de fout wordt op de afspraak vastgelegd én naar de eigenaar
 * gemeld, zodat er een mens naar kijkt in plaats van dat het gat pas op de dag zelf
 * opvalt.
 */
class CalendarSyncException extends RuntimeException
{
}
