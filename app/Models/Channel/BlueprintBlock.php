<?php

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eén blok in de standaard-blokkenlijst (blueprint) van een branche. Hieruit
 * worden de blokken van een nieuwe site gegenereerd. Volgorde = sort (sleepbaar).
 */
class BlueprintBlock extends Model
{
    protected $table = 'channel_blueprint_blocks';

    protected $fillable = [
        'channel_branche_id', 'type', 'sort', 'status', 'locked',
    ];

    protected $casts = [
        'locked' => 'boolean',
        'sort'   => 'integer',
    ];

    public function branche(): BelongsTo
    {
        return $this->belongsTo(Branche::class, 'channel_branche_id');
    }
}
