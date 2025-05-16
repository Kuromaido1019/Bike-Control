<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Access extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guard_id',
        'bike_id',
        'entrance_time',
        'exit_time',
        'observation'
    ];

    // Relación con el visitante
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Cambiado de guard() a guardUser()
    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }

    // Relación con la bicicleta
    public function bike()
    {
        return $this->belongsTo(Bike::class);
    }

    public function verifyRelationships(): bool
    {
        return $this->user()->exists()
            && $this->guardUser()->exists()
            && $this->bike()->exists()
            && $this->user->role === 'visitante'
            && $this->guardUser->role === 'guardia';
    }
}
