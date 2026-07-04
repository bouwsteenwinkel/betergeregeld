<?php

namespace App\Services\Scheduling;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Services\Scheduling\Contracts\CalendarGateway;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private SlotEngine $engine,
        private CalendarGateway $cal,
    ) {}

    /**
     * @param array{name:string,email:string,phone?:?string,starts_at:string,note?:?string,source_site?:?string} $data
     */
    public function book(array $data): Appointment
    {
        $tz    = (string) config('scheduling.timezone', 'Europe/Amsterdam');
        $start = CarbonImmutable::parse($data['starts_at'], $tz);
        $end   = $start->addMinutes((int) config('scheduling.meeting_minutes', 30));

        $appt = DB::transaction(function () use ($data, $start, $end) {
            // Dubbel-boek-preventie: geen overlappende geboekte/gereserveerde afspraak.
            $overlap = Appointment::query()
                ->whereIn('status', ['booked', 'held'])
                ->where(fn ($q) => $q->whereNull('hold_expires_at')->orWhere('hold_expires_at', '>', now()))
                ->where('starts_at', '<', $end)
                ->where('ends_at', '>', $start)
                ->lockForUpdate()
                ->exists();

            if ($overlap || ! $this->engine->isSlotAvailable($start)) {
                throw new SlotTakenException();
            }

            return Appointment::create([
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone'        => $data['phone'] ?? null,
                'starts_at'    => $start,
                'ends_at'      => $end,
                'type'         => 'meet',
                'status'       => 'booked',
                'cancel_token' => Str::random(48),
                'source_site'  => $data['source_site'] ?? null,
                'note'         => $data['note'] ?? null,
            ]);
        });

        // Agenda-event + Meet-link (stub geeft null tot Google gekoppeld is).
        $res = $this->cal->createMeetEvent($appt);
        if (! empty($res['event_id']) || ! empty($res['meet_url'])) {
            $appt->update(['google_event_id' => $res['event_id'] ?? null, 'meet_url' => $res['meet_url'] ?? null]);
        }

        try {
            Mail::to($appt->email)->send(new AppointmentConfirmation($appt));
        } catch (\Throwable $e) {
            report($e);
        }

        return $appt;
    }

    public function cancel(Appointment $appt): void
    {
        if ($appt->google_event_id) {
            try {
                $this->cal->deleteEvent($appt->google_event_id);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $appt->update(['status' => 'cancelled']);
    }
}
