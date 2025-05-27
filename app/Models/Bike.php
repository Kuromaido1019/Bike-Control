<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bike extends Model
{
    protected $fillable = [
        'user_id', 'brand', 'color', 'model', 'estado'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function accesses() {
        return $this->hasMany(Access::class);
    }
}
