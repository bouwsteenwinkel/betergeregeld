<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'starts_at', 'ends_at', 'type', 'status',
        'hold_expires_at', 'google_event_id', 'meet_url', 'cancel_token', 'source_site', 'note',
        'reminder_24h_sent_at', 'reminder_1h_sent_at',
    ];

    protected $casts = [
        'starts_at'            => 'datetime',
        'ends_at'              => 'datetime',
        'hold_expires_at'      => 'datetime',
        'reminder_24h_sent_at' => 'datetime',
        'reminder_1h_sent_at'  => 'datetime',
    ];

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
