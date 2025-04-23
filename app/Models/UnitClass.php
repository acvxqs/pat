<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitClass extends Model
{
    protected $fillable = [
        'name'
    ];

    public function eta(): BelongsTo
    {
        return $this->belongsTo(Eta::class);
    }
}

