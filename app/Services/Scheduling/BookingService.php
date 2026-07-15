<?php

namespace App\Services\Scheduling;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\WebsiteLead;
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

    /**
     * Annuleert een afspraak: agenda-event weg, slot weer vrij (SlotEngine kijkt
     * alleen naar booked/held) en de lead terug in de pijplijn. Idempotent, want de
     * publieke annuleerlink uit de mail kan twee keer worden aangeklikt.
     */
    public function cancel(Appointment $appt): void
    {
        if ($appt->status === 'cancelled') {
            return;
        }

        if ($appt->google_event_id) {
            try {
                $this->cal->deleteEvent($appt->google_event_id);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $appt->update(['status' => 'cancelled']);

        $this->releaseLead($appt);
    }

    /**
     * Verzetten = het nieuwe moment BOEKEN en pas daarna het oude annuleren. Die
     * volgorde is de hele truc: is het nieuwe slot net vergeven, dan gooit book()
     * en houdt de klant zijn bestaande afspraak, in plaats van met lege handen
     * achter te blijven.
     *
     * @throws SlotTakenException
     */
    public function reschedule(Appointment $old, string $startsAt): Appointment
    {
        $lead = $this->leadFor($old);

        $new = $this->book([
            'name'        => $old->name,
            'email'       => $old->email,
            'phone'       => $old->phone,
            'starts_at'   => $startsAt,
            'note'        => $old->note,
            'source_site' => $old->source_site,
        ]);

        $this->cancel($old);

        // cancel() heeft de lead net teruggezet naar 'contacted'; er staat alleen wél
        // een nieuwe afspraak, dus meteen weer vooruit. Zelfde velden als
        // AppointmentController::recordLead(), zodat de admin één beeld houdt.
        // De refresh() is geen franje: cancel() schreef buiten dit object om, en zonder
        // verse waarden ziet Eloquent 'status' => 'appointment' als "niet gewijzigd"
        // (de stale kopie stond daar al op) en schrijft het simpelweg niet weg.
        $lead?->refresh()->forceFill([
            'status'             => 'appointment',
            'appointment_at'     => $new->starts_at,
            'appointment_type'   => $new->type,
            'appointment_status' => $new->status,
            'meet_link'          => $new->meet_url,
            'google_event_id'    => $new->google_event_id,
        ])->save();

        return $new;
    }

    /**
     * Zet de lead achter een geannuleerde afspraak terug. Van de pijplijn-statussen
     * (zie WebsiteLead::STATUSES) is 'contacted' de enige eerlijke: 'appointment' is
     * een leugen zonder afspraak, 'new' gooit weg dat we deze mensen al spraken en
     * 'lost' is te definitief, want wie afzegt wil meestal nog steeds een website.
     * Alleen terugzetten vanaf 'appointment': staat de lead al verder (offerte, klant),
     * dan is een afgezegd gesprek geen reden om hem in de pijplijn te degraderen.
     */
    private function releaseLead(Appointment $appt): void
    {
        try {
            $lead = $this->leadFor($appt);
            if (! $lead) {
                return;
            }

            if ($lead->status === 'appointment') {
                $lead->status = 'contacted';
            }
            $lead->appointment_status = 'cancelled';
            // Het agenda-event is weg, dus de Meet-link werkt niet meer: laten staan
            // zou de admin een dode knop voorschotelen.
            $lead->meet_link = null;
            $lead->save();
        } catch (\Throwable $e) {
            // Best effort: de klant is af, dat mag niet stuklopen op de lead-administratie.
            report($e);
        }
    }

    /**
     * De lead achter een afspraak. Er is geen appointment_id op website_leads, dus
     * koppelen gebeurt op e-mail + het geplande moment (zoals recordLead ze zet).
     * Het moment moet mee: iemand kan later een tweede afspraak hebben staan, en die
     * mag niet sneuvelen als hij de eerste afzegt.
     */
    private function leadFor(Appointment $appt): ?WebsiteLead
    {
        return WebsiteLead::where('email', $appt->email)
            ->where('appointment_at', $appt->starts_at)
            ->latest('id')
            ->first();
    }
}
