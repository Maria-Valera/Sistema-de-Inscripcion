<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversionReglaOrigen extends Model
{
    use HasFactory;

    protected $table = 'conversion_regla_origens';

    protected $fillable = [
        'conversion_regla_id',
        'grado_origen_id',
        'area_origen_id',
    ];

    /**
     * Regla de conversión a la que pertenece este origen.
     */
    public function regla(): BelongsTo
    {
        return $this->belongsTo(ConversionRegla::class, 'conversion_regla_id');
    }

    /**
     * Grado cursado en el plan de origen.
     */
    public function gradoOrigen(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_origen_id');
    }

    /**
     * Área cursada en el plan de origen.
     */
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(AreaFormacion::class, 'area_origen_id');
    }
}

