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
        'dia_id',
        'bloque_id',
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

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->dia_id) && !empty($model->dia)) {
                $model->dia_id = $model->dia;
            }
            if (empty($model->bloque_id) && !empty($model->bloque)) {
                $model->bloque_id = $model->bloque;
            }
        });
    }

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

    public function dia()
    {
        return $this->belongsTo(Dias_semana::class, 'dia_id');
    }

    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_id');
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
