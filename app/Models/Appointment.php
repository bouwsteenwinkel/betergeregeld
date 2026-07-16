<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'starts_at', 'ends_at', 'type', 'status',
        'hold_expires_at', 'google_event_id', 'meet_url', 'cancel_token', 'source_site', 'note',
        'reminder_2d_sent_at', 'reminder_day_of_sent_at', 'calendar_synced_at', 'calendar_error',
    ];

    protected $casts = [
        'starts_at'               => 'datetime',
        'ends_at'                 => 'datetime',
        'hold_expires_at'         => 'datetime',
        'reminder_2d_sent_at'     => 'datetime',
        'reminder_day_of_sent_at' => 'datetime',
        'calendar_synced_at'      => 'datetime',
    ];

    /**
     * Vertaalt de afspraak-status naar de statuswaarde die de lead-administratie kent.
     *
     * Deze twee lijsten lopen niet gelijk: een afspraak is 'booked', maar
     * WebsiteLead::APPOINTMENT_STATUSES kent alleen requested/confirmed/done/cancelled.
     * De rauwe waarde wegschrijven leverde een lead-status op die in geen enkel filter
     * of keuzemenu van de admin voorkomt, en dus als leeg veld verscheen.
     *
     * no_show valt op 'done': het moment is geweest. Dat is geen mooie vertaling, maar
     * 'cancelled' zou suggereren dat iemand had afgezegd — precies het verschil dat je
     * bij een no-show wilt kunnen zien.
     */
    public function leadAppointmentStatus(): string
    {
        return match ($this->status) {
            'held'      => 'requested',
            'cancelled' => 'cancelled',
            'completed', 'no_show' => 'done',
            default     => 'confirmed',
        };
    }

    /**
     * De persoonlijke annuleer-/verzetlink. Altijd op het HOOFDDOMEIN
     * (config('app.url')): geboekt wordt er vanaf elk channel-domein en de mail
     * kan vanuit de CLI vertrekken, dus url()/de huidige host is niet betrouwbaar.
     * Null zonder token (buiten BookingService aangemaakt), dan valt de mail terug
     * op "beantwoord deze mail".
     */
    public function cancelUrl(): ?string
    {
        return $this->cancel_token
            ? rtrim((string) config('app.url'), '/') . '/afspraak/annuleren/' . $this->cancel_token
            : null;
    }
}
