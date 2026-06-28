<?php

namespace App\Support\Intake;

use Carbon\Carbon;

/**
 * Genereert afspraak-slots voor de komende werkdagen.
 *
 * Bewust losgekoppeld: nu vaste blokken, later 1-op-1 te vervangen door echte
 * Google Agenda-beschikbaarheid (zelfde {value,label}-vorm terug).
 */
class AppointmentSlots
{
    /** Vaste tijdsblokken (lokale tijd). */
    private const TIMES = ['09:00', '11:00', '14:00', '16:00'];

    /** Minimale voorbereidingstijd: een slot moet minstens zoveel uur weg zijn. */
    private const MIN_LEAD_HOURS = 2;

    private const DAYS_NL = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
    private const MONTHS_NL = [1 => 'jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

    /**
     * Slots voor de komende $workdays werkdagen (ma–vr), gegroepeerd per dag.
     * @return array<int,array{date:string,label:string,slots:array<int,array{value:string,time:string}>}>
     */
    public static function upcoming(int $workdays = 2): array
    {
        $now  = Carbon::now();
        $out  = [];
        $added = 0;

        for ($i = 0; $i < 10 && $added < $workdays; $i++) {
            $day = $now->copy()->startOfDay()->addDays($i);
            if ($day->isWeekend()) {
                continue;
            }
            $daySlots = [];
            foreach (self::TIMES as $t) {
                [$h, $m] = explode(':', $t);
                $dt = $day->copy()->setTime((int) $h, (int) $m);
                if ($dt->lte($now->copy()->addHours(self::MIN_LEAD_HOURS))) {
                    continue; // te kort dag / al voorbij
                }
                $daySlots[] = ['value' => $dt->format('Y-m-d H:i'), 'time' => $t];
            }
            if ($daySlots) {
                $out[] = ['date' => $day->format('Y-m-d'), 'label' => self::dayLabel($day), 'slots' => $daySlots];
                $added++;
            }
        }

        return $out;
    }

    /** Is een gekozen slot-waarde geldig (zit in de huidige set)? */
    public static function isValid(string $value): bool
    {
        foreach (self::upcoming(3) as $day) {
            foreach ($day['slots'] as $slot) {
                if ($slot['value'] === $value) {
                    return true;
                }
            }
        }
        return false;
    }

    /** Mooie NL-label voor een datum: "do 3 jul". */
    private static function dayLabel(Carbon $d): string
    {
        return self::DAYS_NL[$d->dayOfWeekIso - 1] . ' ' . $d->day . ' ' . self::MONTHS_NL[$d->month];
    }

    /** Label voor één slot-waarde, bv. "do 3 jul · 11:00". */
    public static function labelFor(string $value): string
    {
        try {
            $dt = Carbon::createFromFormat('Y-m-d H:i', $value);
            return self::dayLabel($dt) . ' · ' . $dt->format('H:i');
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
