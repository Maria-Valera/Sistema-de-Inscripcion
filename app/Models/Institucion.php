<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Institucion extends Model
{
    use HasFactory;

    protected $table = 'instituciones';

    protected $fillable = [
        'codigo_plantel',
        'nombre',
        'direccion',
        'telefono',
        'localidad_id',
        'zona_educativa',
        'director_persona_id',
        'director_encargado',
        'logo_ruta',
        'cdcee',
        'dependencia',
        'activo',
    ];

    protected $casts = [
        'director_encargado' => 'boolean',
        'activo' => 'boolean',
    ];

    /**
     * Relación con la localidad geográfica.
     */
    public function localidad(): BelongsTo
    {
        return $this->belongsTo(Localidad::class, 'localidad_id');
    }

    /**
     * Relación con la persona asignada como Director(a).
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'director_persona_id');
    }

    /**
     * Scope para consultar instituciones activas.
     */
    public function scopeActiva($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Retorna la institución actualmente activa del sistema.
     */
    public static function plantelActivo(): ?self
    {
        return static::where('activo', true)->first();
    }
}

