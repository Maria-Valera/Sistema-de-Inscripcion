<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioAsignacion extends Model
{
    protected $table = 'horario_asignaciones';

    protected $fillable = [
        'anio_escolar_id',
        'carga_academica_id',
        'aula_id',
        'dia',
        'bloque',
        'fijada',
        'conflicto_manual',
        'motivo_conflicto',
    ];

    protected $casts = [
        'conflicto_manual' => 'boolean',
        'fijada' => 'boolean',
        'dia' => 'integer',
        'bloque' => 'integer',
    ];

    public function cargaAcademica()
    {
        return $this->belongsTo(CargaAcademica::class, 'carga_academica_id');
    }

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class);
    }

    public function docente()
    {
        return $this->hasOneThrough(
            Docente::class,
            CargaAcademica::class,
            'id',               // Foreign key on CargaAcademica table...
            'id',               // Foreign key on Docente table...
            'carga_academica_id', // Local key on HorarioAsignacion table...
            'docente_id'        // Local key on CargaAcademica table...
        );
    }

    public function materia()
    {
        return $this->hasOneThrough(
            AreaFormacion::class,
            CargaAcademica::class,
            'id',
            'id',
            'carga_academica_id',
            'area_formacion_id'
        );
    }

    public function seccion()
    {
        return $this->hasOneThrough(
            Seccion::class,
            CargaAcademica::class,
            'id',
            'id',
            'carga_academica_id',
            'seccion_id'
        );
    }

    public function aula()
    {
        return $this->belongsTo(Aula::class, 'aula_id', 'id_aula');
    }

    // Accesores para mantener retrocompatibilidad de atributos directos
    public function getDocenteIdAttribute()
    {
        return $this->attributes['docente_id'] ?? $this->cargaAcademica?->docente_id;
    }

    public function getMateriaIdAttribute()
    {
        return $this->attributes['materia_id'] ?? $this->cargaAcademica?->area_formacion_id;
    }

    public function getSeccionIdAttribute()
    {
        return $this->attributes['seccion_id'] ?? $this->cargaAcademica?->seccion_id;
    }
}
