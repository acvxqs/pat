<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoundShipData extends Model
{
    protected $fillable = [
        'round_number',
        'ship_id',
        'races_id',
        'unit_class_id',
        'eta_id',
        'target1_id',
        'target2_id',
        'target3_id',
        'weapon_type_id',
        'cloaked',
        'initiative',
        'guns',
        'armor',
        'damage',
        'empres',
        'cost_m',
        'cost_c',
        'cost_e',
        'armorcost',
        'damagecost',
    ];

    public function round()
    {
        return $this->belongsTo(Round::class, 'round_number', 'number');
    }

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function unitClass(): BelongsTo
    {
        return $this->belongsTo(UnitClass::class);
    }

    public function eta(): BelongsTo
    {
        return $this->belongsTo(Eta::class);
    }

    public function target1(): BelongsTo
    {
        return $this->belongsTo(UnitClass::class, 'target1_id');
    }

    public function target2(): BelongsTo
    {
        return $this->belongsTo(UnitClass::class, 'target2_id');
    }

    public function target3(): BelongsTo
    {
        return $this->belongsTo(UnitClass::class, 'target3_id');
    }

    public function weaponType(): BelongsTo
    {
        return $this->belongsTo(WeaponType::class);
    }
}

