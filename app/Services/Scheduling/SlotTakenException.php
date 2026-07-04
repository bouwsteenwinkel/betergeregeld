<?php

namespace App\Services\Scheduling;

use RuntimeException;

/** Gegooid als een slot bij het vastleggen net bezet blijkt. */
class SlotTakenException extends RuntimeException
{
}
