<?php
// app/Models/AccessModification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessModification extends Model
{
    protected $table = 'access_modifications';
    protected $fillable = [
        'access_id',
        'accion',
        'datos_anteriores',
        'datos_nuevos',
        'editado_por',
        'fecha_edicion',
    ];
    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'fecha_edicion' => 'datetime',
    ];

    public function access() {
        return $this->belongsTo(Access::class);
    }
    public function editor() {
        return $this->belongsTo(User::class, 'editado_por');
    }
}
