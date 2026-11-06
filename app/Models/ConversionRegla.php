<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConversionRegla extends Model
{
    use HasFactory;

    protected $table = 'conversion_reglas';

    protected $fillable = [
        'pensum_origen_id',
        'pensum_destino_id',
        'grado_destino_id',
        'area_destino_id',
        'tipo_regla',
        'observacion',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Plan de estudio de origen (ej. 31059).
     */
    public function pensumOrigen(): BelongsTo
    {
        return $this->belongsTo(Pensum::class, 'pensum_origen_id');
    }

    /**
     * Plan de estudio de destino (ej. 31060).
     */
    public function pensumDestino(): BelongsTo
    {
        return $this->belongsTo(Pensum::class, 'pensum_destino_id');
    }

    /**
     * Grado o año escolar en el plan destino.
     */
    public function gradoDestino(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_destino_id');
    }

    /**
     * Área de formación resultante en el plan destino.
     */
    public function areaDestino(): BelongsTo
    {
        return $this->belongsTo(AreaFormacion::class, 'area_destino_id');
    }

    /**
     * Áreas de origen que nutren esta regla de conversión.
     */
    public function origenes(): HasMany
    {
        return $this->hasMany(ConversionReglaOrigen::class, 'conversion_regla_id');
    }

    /**
     * Nombre descriptivo del tipo de regla según la Circular 01.
     */
    public function getNombreTipoReglaAttribute(): string
    {
        return match ($this->tipo_regla) {
            'directa'   => 'Homologación Directa',
            'promedio'  => 'Promedio de Asignaturas',
            'exonerada' => 'Exonerada (EX)',
            'no_cuenta' => 'No se toma en cuenta',
            default     => ucfirst($this->tipo_regla),
        };
    }

    /**
     * Scope para filtrar por transición de planes.
     */
    public function scopeEntrePlanes($query, $origenId, $destinoId)
    {
        return $query->where('pensum_origen_id', $origenId)
                     ->where('pensum_destino_id', $destinoId);
    }
}

