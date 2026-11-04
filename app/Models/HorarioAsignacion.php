<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioAsignacion extends Model
{
    protected $table = 'horario_asignaciones';

    protected $fillable = [
        'anio_escolar_id',
        'docente_id',
        'materia_id',
        'seccion_id',
        'aula_id',
        'dia_id',
        'bloque_id',
        'conflicto_manual',
        'motivo_conflicto',
    ];

    protected $casts = [
        'conflicto_manual' => 'boolean',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class);
    }

    public function materia()
    {
        return $this->belongsTo(AreaFormacion::class, 'materia_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id', 'id_aula');
    }

    public function dia()
    {
        return $this->belongsTo(Dias_semana::class, 'dia_id');
    }

    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_id');
    }
}
