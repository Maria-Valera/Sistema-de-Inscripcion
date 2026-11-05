<?php

namespace App\Models;

use App\Enums\EstadoLapso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lapso extends Model
{
    protected $table = 'lapsos';

    // Valor por defecto tambien en el modelo (asi un registro recien creado ya lo trae).
    protected $attributes = [
        'estado' => 'planificado',
    ];

        protected $fillable = [
        'anio_escolar_id',
        'calendario_academico_id',
        'numero',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'fecha_limite_carga',
        'estado',
        'cerrado_por',
        'cerrado_at',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_carga' => 'datetime',
        'estado' => EstadoLapso::class,
        'cerrado_at' => 'datetime',
    ];


        public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function calendarioAcademico(): BelongsTo
    {
        return $this->belongsTo(CalendarioAcademico::class, 'calendario_academico_id');
    }

    public function cortes(): HasMany
    {
        return $this->hasMany(Corte::class, 'lapso_id')->orderBy('numero');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }
}
