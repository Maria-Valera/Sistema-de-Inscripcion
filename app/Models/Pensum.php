<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pensum extends Model
{
    use HasFactory;

    protected $table = 'pensums';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'anio_inicio',
        'anio_fin',
        'status',
    ];

    protected $casts = [
        'anio_inicio' => 'integer',
        'anio_fin'    => 'integer',
        'status'      => 'boolean',
    ];

    /**
     * Malla curricular asociada a este plan de estudio.
     */
    public function mallas(): HasMany
    {
        return $this->hasMany(GradoAreaFormacion::class, 'pensum_id');
    }

    /**
     * Secciones que cursan este pensum.
     */
    public function secciones(): HasMany
    {
        return $this->hasMany(Seccion::class, 'pensum_id');
    }

    /**
     * Reglas de conversión donde este pensum es el plan de origen.
     */
    public function conversionesOrigen(): HasMany
    {
        return $this->hasMany(ConversionRegla::class, 'pensum_origen_id');
    }

    /**
     * Reglas de conversión donde este pensum es el plan de destino.
     */
    public function conversionesDestino(): HasMany
    {
        return $this->hasMany(ConversionRegla::class, 'pensum_destino_id');
    }

    /**
     * Indica si el plan de estudio se encuentra actualmente vigente.
     * Según la regla de negocio del MPPE: año fin vacío = vigente.
     */
    public function getEsVigenteAttribute(): bool
    {
        return $this->status && is_null($this->anio_fin);
    }

    /**
     * Scope para consultar solo pensums vigentes.
     */
    public function scopeVigentes($query)
    {
        return $query->where('status', true)->whereNull('anio_fin');
    }

    /**
     * Scope para filtrar por búsqueda (código o nombre).
     */
    public function scopeBuscar($query, ?string $termino)
    {
        if (empty($termino)) {
            return $query;
        }

        return $query->where(function ($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%");
        });
    }
}

