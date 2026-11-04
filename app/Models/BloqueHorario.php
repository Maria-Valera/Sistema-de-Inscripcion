<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueHorario extends Model
{
    use HasFactory;
    protected $table = 'bloque_horarios';

    protected $fillable = [
        'hora_inicio',
        'hora_fin',
        'status',
    ];

    public function noDisponibilidades()
    {
        return $this->hasMany(DocenteNoDisponibilidad::class, 'id_bloque_hora');
    }
}
