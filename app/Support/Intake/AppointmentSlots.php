<?php

namespace App\Support\Intake;

use App\Services\Scheduling\SlotEngine;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * Presentatielaag voor afspraak-slots: levert de {value,label}-vorm die de
 * intake-formulieren verwachten.
 *
 * Rekent zelf NIETS uit. De momenten komen uit SlotEngine, dezelfde bron waar
 * BookingService bij het boeken op toetst. Dat is een harde eis: deze klasse
 * had eigen vaste blokken en een eigen voorlooptijd, waardoor een bezoeker een
 * moment kon kiezen dat het boeken vervolgens weigerde.
 */
class AppointmentSlots
{
    private const DAYS_NL = ['ma', 'di', 'wo', 'do', 'vr', 'za', 'zo'];
    private const MONTHS_NL = [1 => 'jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

    /**
     * Slots voor de eerstvolgende $workdays dagen mét beschikbaarheid, gegroepeerd per dag.
     * @return array<int,array{date:string,label:string,slots:array<int,array{value:string,time:string}>}>
     */
    public static function upcoming(int $workdays = 2): array
    {
        $out = [];

        // SlotEngine houdt zelf al rekening met werkdagen, uitzonderingen, bezette
        // slots en voorlooptijd, dus alles wat terugkomt is boekbaar. We knippen
        // hier alleen af op het aantal dagen dat het formulier wil tonen.
        foreach (self::engine()->slots() as $date => $times) {
            if (count($out) >= $workdays) {
                break;
            }
            if (! $times) {
                continue;
            }

            $day = Carbon::parse($date, self::tz());
            $out[] = [
                'date'  => $date,
                'label' => self::dayLabel($day),
                'slots' => array_map(fn ($t) => ['value' => $date . ' ' . $t, 'time' => $t], $times),
            ];
        }

        return $out;
    }

    /** Is een gekozen slot-waarde nu nog boekbaar? */
    public static function isValid(string $value): bool
    {
        try {
            $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $value, self::tz());
        } catch (\Throwable $e) {
            return false;
        }

        // Rechtstreeks aan de engine vragen in plaats van door upcoming() te lopen:
        // die is afgeknipt op een paar dagen, terwijl de horizon verder reikt.
        return self::engine()->isSlotAvailable($start);
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

    private static function engine(): SlotEngine
    {
        return app(SlotEngine::class);
    }

    private static function tz(): string
    {
        return (string) config('scheduling.timezone', 'Europe/Amsterdam');
    }
}
