<?php

namespace App\Models;

use App\Enums\TipoAsignacionNota;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargaAcademica extends Model
{
    use HasFactory;

    protected $table = 'cargas_academicas';

    protected $fillable = [
        'calendario_academico_id',
        'docente_id',
        'tipo',
        'area_formacion_id',
        'grado_seccion_id',
        'grupo_estable_id',
        'docente_area_grado_id',
        'activa',
        'sincronizada_at',
    ];

    protected $casts = [
        'tipo' => TipoAsignacionNota::class,
        'activa' => 'boolean',
        'sincronizada_at' => 'datetime',
    ];

    public function calendarioAcademico(): BelongsTo
    {
        return $this->belongsTo(CalendarioAcademico::class, 'calendario_academico_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function areaFormacion(): BelongsTo
    {
        return $this->belongsTo(AreaFormacion::class, 'area_formacion_id');
    }

    public function gradoSeccion(): BelongsTo
    {
        return $this->belongsTo(GradoSeccion::class, 'grado_seccion_id');
    }

    public function grupoEstable(): BelongsTo
    {
        return $this->belongsTo(GrupoEstable::class, 'grupo_estable_id');
    }

    public function docenteAreaGrado(): BelongsTo
    {
        return $this->belongsTo(DocenteAreaGrado::class, 'docente_area_grado_id');
    }

    public function horarioAsignaciones(): HasMany
    {
        return $this->hasMany(HorarioAsignacion::class, 'carga_academica_id');
    }

    // Relaciones a tablas de María/José: declaradas, sin tocar sus modelos.
    public function cargaLapsos(): HasMany
    {
        return $this->hasMany(CargaLapso::class, 'carga_academica_id');
    }

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'carga_academica_id');
    }

    public function corteNotas(): HasMany
    {
        return $this->hasMany(CorteNota::class, 'carga_academica_id');
    }

    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class, 'carga_academica_id');
    }

    public function prosecucionAreas(): HasMany
    {
        return $this->hasMany(ProsecucionArea::class, 'carga_academica_id');
    }

    public function asignacionNotaMiembros(): HasMany
    {
        return $this->hasMany(AsignacionNotaMiembro::class, 'carga_academica_id');
    }
}