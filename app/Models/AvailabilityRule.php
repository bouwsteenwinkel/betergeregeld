<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityRule extends Model
{
    protected $fillable = ['weekday', 'start_time', 'end_time', 'active'];

    protected $casts = [
        'weekday' => 'integer',
        'active'  => 'boolean',
    ];
}
