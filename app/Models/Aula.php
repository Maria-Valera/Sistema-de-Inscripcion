<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $primaryKey = 'id_aula';

    protected $fillable = [
        'nombre_aula',
        'tipo_aula',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Scope para traer solo activas
    public function scopeActivas($query)
    {
        return $query->where('status', true);
    }

     public function aula(){
        return $this->hasMany(seccion_aula::class, "id_aula", "id_aula");
    }
}
