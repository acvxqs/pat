<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
