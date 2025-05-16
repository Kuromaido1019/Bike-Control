<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    protected $hidden = ['password'];

    // Relaciones
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function bikes()
    {
        return $this->hasMany(Bike::class);
    }

    public function accessesAsVisitor()
    {
        return $this->hasMany(Access::class, 'user_id');
    }

    public function accessesAsGuard()
    {
        return $this->hasMany(Access::class, 'guard_id');
    }

    // Helpers de roles
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuard(): bool
    {
        return $this->role === 'guardia';
    }

    public function isVisitor(): bool
    {
        return $this->role === 'visitante';
    }
}
