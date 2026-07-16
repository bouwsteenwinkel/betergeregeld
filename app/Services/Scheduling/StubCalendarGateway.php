<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Services\Scheduling\Contracts\CalendarGateway;
use Carbon\CarbonInterface;

/**
 * Placeholder-agenda voor fase 1a: geen echte Google-koppeling. Geeft geen
 * bezette periodes en geen Meet-link terug (die volgt zodra Google is gekoppeld).
 *
 * De lege bezetting is hier eerlijk en niet fail-open zoals bij een storing: er ís
 * geen externe agenda om rekening mee te houden. isConnected() = false is het signaal
 * waaraan BookingService ziet dat een ontbrekend event verwacht gedrag is en geen
 * storing om de eigenaar voor wakker te maken.
 */
class StubCalendarGateway implements CalendarGateway
{
    public function isConnected(): bool
    {
        return false;
    }

    public function busyPeriods(CarbonInterface $from, CarbonInterface $to): array
    {
        return [];
    }

    public function createMeetEvent(Appointment $appointment): array
    {
        return ['event_id' => null, 'meet_url' => null];
    }

    public function deleteEvent(string $eventId): void
    {
        // no-op
    }
}
