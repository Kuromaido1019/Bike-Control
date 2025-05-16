<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'phone', 'alt_phone', 'rut', 'birth_date', 'career'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
