<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = ['name', 'abbreviation'];

    public function rounds(): HasMany
    {
        return $this->hasMany(RoundRaceData::class);
    }
}
