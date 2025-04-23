<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public $incrementing = false;

    protected $fillable = [
        'telegram_id',
        'name'
    ];

    protected $hidden = [];

    // Optionally disable password functionality
    public function getAuthPassword()
    {
        return null;
    }
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'telegram_id', 'role_id');
    }
    
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }
    
}
