<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'starts_at', 'ends_at', 'type', 'status',
        'hold_expires_at', 'google_event_id', 'meet_url', 'cancel_token', 'source_site', 'note',
    ];

    protected $casts = [
        'starts_at'       => 'datetime',
        'ends_at'         => 'datetime',
        'hold_expires_at' => 'datetime',
    ];
}
