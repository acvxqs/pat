<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RoundRaceData extends Model
{
    protected $fillable = [
        'round_number',
        'race_id',
        'description',
        'max_stealth',
        'stealth_growth_per_tick',
        'base_construction_units',
        'base_research_points',
        // Below all unsignedSmallIntegers. They represent percentage values.
        'salvage_bonus',
        'production_time_bonus',
        'universe_trade_tax',
    ];
    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'number');
    }
    
    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }
}
