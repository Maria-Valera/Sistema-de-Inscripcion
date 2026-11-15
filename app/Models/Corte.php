<?php

namespace App\Models;

use App\Enums\EstadoCorte;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Corte extends Model
{

        /** Un lapso tiene como máximo 10 cortes. */
    public const MAXIMO_POR_LAPSO = 10;

    /** Lo que propone el sistema al crear los cortes de un lapso: 4 cortes de 20 puntos. */
    public const CANTIDAD_POR_DEFECTO = 4;
    public const PUNTOS_POR_DEFECTO = 20;

    protected $table = 'cortes';

    // Valor por defecto tambien en el modelo (asi un registro recien creado ya lo trae).
    protected $attributes = [
        'estado' => 'planificado',
    ];


        protected $fillable = [
        'lapso_id',
        'numero',
        'nombre',
        'peso_lapso',
        'fecha_inicio',
        'fecha_fin',
        'fecha_limite_carga',
        'estado',
        'abierto_por',
        'abierto_at',
        'cerrado_por',
        'cerrado_at',
    ];

    protected $casts = [
        'peso_lapso' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_limite_carga' => 'datetime',
        'estado' => EstadoCorte::class,
        'abierto_at' => 'datetime',
        'cerrado_at' => 'datetime',
    ];

        public function lapso(): BelongsTo
    {
        return $this->belongsTo(Lapso::class, 'lapso_id');
    }

    public function abiertoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abierto_por');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }
}
