<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Government extends Model
{
    protected $fillable = ['name'];

    public function rounds(): HasMany
    {
        return $this->hasMany(RoundGovernmentData::class);
    }
}
