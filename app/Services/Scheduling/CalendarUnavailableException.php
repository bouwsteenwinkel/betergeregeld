<?php

namespace App\Services\Scheduling;

use RuntimeException;

/**
 * De externe agenda kon niet gelezen worden (geen token, storing, time-out).
 *
 * Bestaat om "er staat niets in de agenda" te scheiden van "ik weet niet wat er in
 * de agenda staat". Die twee zagen er eerder identiek uit (allebei een lege lijst),
 * waardoor een Google-storing elk bezet uur als vrij aanbood en bezoekers over
 * bestaande afspraken heen boekten.
 */
class CalendarUnavailableException extends RuntimeException
{
}
