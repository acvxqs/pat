<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tick extends Model
{
    protected $fillable = [
        'number',
        'round_number',
    ];

    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'number');
    }
}
