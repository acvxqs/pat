<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundTick extends Model
{
    protected $fillable = [
        'number',
        'round_number',
    ];
    
    protected $casts = [
        'number' => 'integer',
        'round_number' => 'integer',
    ];
    
    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'number');
    }
}
