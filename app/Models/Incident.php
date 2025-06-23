<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'guard_id',
        'rut',
        'categoria',
        'detalle',
    ];

    // Relación con el guardia (usuario)
    public function guardUser()
    {
        return $this->belongsTo(User::class, 'guard_id');
    }
}
