<?php

namespace App\Services\Scheduling;

use App\Models\Appointment;
use App\Models\AvailabilityException;
use App\Models\AvailabilityRule;
use App\Services\Scheduling\Contracts\CalendarGateway;
use Carbon\CarbonImmutable;

/**
 * Berekent vrije afspraaksloten: wekelijks sjabloon (of default_hours) minus
 * uitzonderingen, geboekte/gereserveerde afspraken en externe agenda-bezetting.
 * Alles in de ingestelde tijdzone (Europe/Amsterdam), met voorlooptijd en horizon.
 */
class SlotEngine
{
    public function __construct(private CalendarGateway $cal) {}

    private function tz(): string
    {
        return (string) config('scheduling.timezone', 'Europe/Amsterdam');
    }

    /**
     * @return array<string,array<int,string>>  ['2026-07-07' => ['09:00','09:30', ...], ...]
     */
    public function slots(?CarbonImmutable $from = null, ?CarbonImmutable $to = null): array
    {
        $tz       = $this->tz();
        $now      = CarbonImmutable::now($tz);
        $earliest = $now->addHours((int) config('scheduling.min_notice_hours', 4));
        $latest   = $now->addDays((int) config('scheduling.horizon_days', 21))->endOfDay();

        $from = ($from ? $from->setTimezone($tz) : $now);
        $to   = ($to ? $to->setTimezone($tz) : $latest);
        if ($from->lt($now)) {
            $from = $now;
        }
        if ($to->gt($latest)) {
            $to = $latest;
        }
        if ($to->lt($from)) {
            return [];
        }

        $slotMin = (int) config('scheduling.slot_minutes', 30);
        $meetMin = (int) config('scheduling.meeting_minutes', 30);
        $buffer  = (int) config('scheduling.buffer_minutes', 0);

        $rules      = AvailabilityRule::where('active', true)->get()->groupBy('weekday');
        $useDefault = $rules->isEmpty();
        $default    = (array) config('scheduling.default_hours', []);

        $exceptions = AvailabilityException::whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->get()->groupBy(fn ($e) => $e->date->toDateString());

        // Bezet = geboekte + nog-geldige holds + externe agenda.
        $busy = $this->internalBusy($from->startOfDay(), $to->endOfDay());
        foreach ($this->cal->busyPeriods($from->startOfDay(), $to->endOfDay()) as $b) {
            $busy[] = [$b['start'], $b['end']];
        }

        $out = [];
        for ($day = $from->startOfDay(); $day->lte($to); $day = $day->addDay()) {
            $dateStr = $day->toDateString();
            $iso     = $day->dayOfWeekIso;

            $windows = [];
            if ($useDefault) {
                foreach (($default[$iso] ?? []) as $w) {
                    $windows[] = $w;
                }
            } else {
                foreach (($rules[$iso] ?? collect()) as $r) {
                    $windows[] = [substr((string) $r->start_time, 0, 5), substr((string) $r->end_time, 0, 5)];
                }
            }

            $blocks = [];
            foreach (($exceptions[$dateStr] ?? collect()) as $ex) {
                if ($ex->kind === 'extra' && $ex->start_time && $ex->end_time) {
                    $windows[] = [substr((string) $ex->start_time, 0, 5), substr((string) $ex->end_time, 0, 5)];
                } elseif ($ex->kind === 'block') {
                    if ($ex->start_time && $ex->end_time) {
                        $blocks[] = [substr((string) $ex->start_time, 0, 5), substr((string) $ex->end_time, 0, 5)];
                    } else {
                        $windows = []; // hele dag geblokkeerd
                    }
                }
            }
            if (! $windows) {
                continue;
            }

            $times = [];
            foreach ($windows as [$ws, $we]) {
                $winStart = CarbonImmutable::parse("$dateStr $ws", $tz);
                $winEnd   = CarbonImmutable::parse("$dateStr $we", $tz);
                for ($t = $winStart; $t->addMinutes($meetMin)->lte($winEnd); $t = $t->addMinutes($slotMin)) {
                    $slotStart = $t;
                    $slotEnd   = $t->addMinutes($meetMin);
                    if ($slotStart->lt($earliest) || $slotStart->gt($latest)) {
                        continue;
                    }
                    if ($this->overlapsAny($slotStart, $slotEnd, $this->toRanges($blocks, $dateStr))) {
                        continue;
                    }
                    if ($this->overlapsAny($slotStart->subMinutes($buffer), $slotEnd->addMinutes($buffer), $busy)) {
                        continue;
                    }
                    $times[$slotStart->format('H:i')] = true;
                }
            }
            if ($times) {
                $keys = array_keys($times);
                sort($keys);
                $out[$dateStr] = $keys;
            }
        }

        return $out;
    }

    /** Is dit concrete slot (start) nu nog beschikbaar? Voor de her-check bij boeken. */
    public function isSlotAvailable(CarbonImmutable $start): bool
    {
        $start = $start->setTimezone($this->tz());
        $day   = $this->slots($start->startOfDay(), $start->endOfDay());
        return in_array($start->format('H:i'), $day[$start->toDateString()] ?? [], true);
    }

    /** @return array<int,array{0:CarbonImmutable,1:CarbonImmutable}> */
    private function internalBusy(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Appointment::query()
            ->whereIn('status', ['booked', 'held'])
            ->where(fn ($q) => $q->whereNull('hold_expires_at')->orWhere('hold_expires_at', '>', now()))
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->get(['starts_at', 'ends_at'])
            ->map(fn ($a) => [CarbonImmutable::parse($a->starts_at), CarbonImmutable::parse($a->ends_at)])
            ->all();
    }

    /** @return array<int,array{0:CarbonImmutable,1:CarbonImmutable}> */
    private function toRanges(array $hm, string $dateStr): array
    {
        $tz = $this->tz();
        return array_map(fn ($r) => [
            CarbonImmutable::parse("$dateStr {$r[0]}", $tz),
            CarbonImmutable::parse("$dateStr {$r[1]}", $tz),
        ], $hm);
    }

    private function overlapsAny(CarbonImmutable $start, CarbonImmutable $end, array $ranges): bool
    {
        foreach ($ranges as [$x1, $x2]) {
            if ($start->lt($x2) && $end->gt($x1)) {
                return true;
            }
        }
        return false;
    }
}
