<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocenteNoDisponibilidad extends Model
{
    protected $table = '_docente__no_disponibilidad';

    protected $fillable = [
        'docente_id',
        'anio_escolar_id',
        'dias_semana_id',
        'id_bloque_hora',
        'motivo',
    ];

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function diaSemana()
    {
        return $this->belongsTo(Dias_semana::class, 'dias_semana_id');
    }

    public function bloqueHorario()
    {
        return $this->belongsTo(BloqueHorario::class, 'id_bloque_hora');
    }
}
