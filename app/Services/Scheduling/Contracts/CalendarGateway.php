<?php

namespace App\Services\Scheduling\Contracts;

use App\Models\Appointment;
use Carbon\CarbonInterface;

/**
 * Abstractie over de agenda-provider. Fase 1a gebruikt een stub; fase 1b een
 * echte Google Calendar-implementatie (event + Meet-link + free/busy).
 */
interface CalendarGateway
{
    /** Is er een werkende agenda-koppeling? (stub = false) */
    public function isConnected(): bool;

    /**
     * Bezette periodes in de agenda tussen $from en $to.
     *
     * Een lege lijst betekent "de agenda is aantoonbaar vrij", nooit "ik kon het niet
     * ophalen": dat tweede geval hoort te gooien, anders wordt een bezet uur als vrij
     * aangeboden.
     *
     * @return array<int,array{start:CarbonInterface,end:CarbonInterface}>
     *
     * @throws \App\Services\Scheduling\CalendarUnavailableException
     */
    public function busyPeriods(CarbonInterface $from, CarbonInterface $to): array;

    /**
     * Maak een agenda-event met Google Meet voor de afspraak.
     *
     * @return array{event_id:?string,meet_url:?string}
     *
     * @throws \App\Services\Scheduling\CalendarSyncException
     */
    public function createMeetEvent(Appointment $appointment): array;

    /**
     * Verwijder een eerder gemaakt event (bij annuleren). Idempotent: een event dat
     * al weg is, is geen fout.
     *
     * @throws \App\Services\Scheduling\CalendarSyncException
     */
    public function deleteEvent(string $eventId): void;
}
