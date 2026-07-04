<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvailabilityException extends Model
{
    protected $fillable = ['date', 'kind', 'start_time', 'end_time', 'note'];

    protected $casts = [
        'date' => 'date',
    ];
}
