<?php

namespace App\Models;

use App\Enums\ReglaRedondeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionEvaluacion extends Model
{
    protected $table = 'configuracion_evaluacions';

    protected $fillable = [
        'anio_escolar_id',
        'nota_minima',
        'nota_maxima',
        'nota_aprobatoria',
        'regla_redondeo',
        'num_cortes',
        'puntos_corte',
        'num_lapsos',
        'momentos_quedada',
        'max_areas_pendientes',
    ];

    protected $casts = [
        'regla_redondeo' => ReglaRedondeo::class,
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }
}
