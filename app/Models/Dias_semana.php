<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dias_semana extends Model
{
    protected $table = 'dias_semana';

    protected $fillable = [
        'nombre_dia',
    ];

    public function noDisponibilidades()
    {
        return $this->hasMany(DocenteNoDisponibilidad::class, 'dias_semana_id');
    }
}
