<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundTick extends Model
{
    protected $fillable = [
        'tick',
        'round_number',
    ];
    
    protected $casts = [
        'tick' => 'integer',
        'round_number' => 'integer',
    ];
    
    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'tick');
    }
}
