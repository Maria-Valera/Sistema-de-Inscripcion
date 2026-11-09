<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EjecucionesPercentil;
use App\Models\Grado;

class Seccion extends Model
{
    use HasFactory;

    protected $table = 'seccions';

    protected $fillable = [
        'nombre',
        'cantidad_actual',
        'grado_id',
        'pensum_id',
        'mencion_id',
        'ejecucion_percentil_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function inscripcionesProsecucion()
    {
        return $this->hasMany(
            InscripcionProsecucion::class,
            'seccion_id'
        );
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /**
     * Plan de estudio (pensum) al que pertenece la sección.
     */
    public function pensum(): BelongsTo
    {
        return $this->belongsTo(Pensum::class, 'pensum_id');
    }

    /**
     * Mención oficial asignada a la sección.
     */
    public function mencion(): BelongsTo
    {
        return $this->belongsTo(Mencion::class, 'mencion_id');
    }

    /**
     * Regla oficial del MPPE:
     * La sección sin mención asignada imprime '*****' en boletines y certificados.
     */
    public function getNombreMencionBoletinAttribute(): string
    {
        return $this->mencion?->nombre ?? '*****';
    }

    /**
     * Código de ciclo oficial para el encabezado del boletín.
     */
    public function getCicloBoletinAttribute(): string
    {
        return $this->mencion?->ciclo ?? '50';
    }

    public function ejecucion(): BelongsTo
    {
        return $this->belongsTo(
            EjecucionesPercentil::class,
            'ejecucion_percentil_id'
        );
    }

    public function entradasPercentil()
    {
        return $this->hasMany(EntradasPercentil::class, 'seccion_id');
    }

    public function seccion()
    {
        return $this->belongsTo(seccion_aulas::class, "id_seccion", "id");
    }

    public function aulaFija()
    {
        return $this->hasOne(seccion_aula::class, 'id_seccion', 'id')
                    ->where('status', true)
                    ->with('aula');
    }
}
