<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoundGovernmentData extends Model
{
    protected $fillable = [
        'round_number',
        'government_id',
        'description',
        // Below all unsignedSmallIntegers. They represent percentage values.
        'mining_output',
        'research',
        'construction',
        'alert',
        'stealth',
        'production_time',
        'production_cost',
    ];

    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'number');
    }
    
    public function government()
    {
        return $this->belongsTo(Government::class);
    }
}
