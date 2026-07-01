<?php

namespace App\Models\Channel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Branche-sjabloon: levert de standaard-blokkenlijst (blueprint), het thema en
 * de header-opzet waaruit een nieuwe channel-site wordt gegenereerd.
 */
class Branche extends Model
{
    protected $table = 'channel_branches';

    protected $fillable = [
        'key', 'name', 'lead_branche',
        'theme', 'header', 'blueprint', 'intake', 'active',
    ];

    protected $casts = [
        'theme'     => 'array',
        'header'    => 'array',
        'blueprint' => 'array',
        'intake'    => 'array',
        'active'    => 'boolean',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'channel_branche_id');
    }

    /** Standaard-blokkenlijst (gesorteerd) — bron voor het genereren van sites. */
    public function blueprintBlocks(): HasMany
    {
        return $this->hasMany(BlueprintBlock::class, 'channel_branche_id')->orderBy('sort');
    }
}
