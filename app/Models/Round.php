<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $primaryKey = 'number';
    public $incrementing = false;
    protected $casts = [
        'last_tick_happened_at' => 'datetime',
        'ticking' => 'boolean',
        'pods_die_when_capping' => 'boolean',
        'structure_killers_die' => 'boolean',
    ];
    protected $fillable = [
        'name',
        'current_tick',
        'tick_speed',
        'ticking',
        'last_tick_happened_at',
        'max_membercount',
        'members_counting_towards_alliance_score',
        'xp_per_tick_defending_universe',
        'xp_per_tick_defending_galaxy',
        'xp_landing_defense',
        'max_cap',
        'max_structures_destroyed',
        'salvage_from_attacking_ships',
        'salvage_from_defending_ships',
        'asteroid_armor',
        'construction_armor',
        'damage_done_on_primary_target',
        'damage_done_on_secondary_target',
        'damage_done_on_tertiary_target',
        'pods_die_when_capping',
        'structure_killers_die',
        'stealship_steal_die_ratio',
    ];

    public function ticks()
    {
        return $this->hasMany(RoundTick::class, 'round_number', 'number');
    }

    protected static function booted()
    {
        static::created(function ($round) {
            dispatch(new \App\Jobs\ParseRoundInitialData($round->number));
            dispatch(new \App\Jobs\ParseBotfiles($round->number));
        });

        static::updated(function ($round) {
            if ($round->wasChanged('current_tick')) {
                dispatch(new \App\Jobs\ParseBotfiles($round->number));
            }
        });
    }
}
