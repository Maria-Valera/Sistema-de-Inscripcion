<?php

namespace App\Models;

use App\Enums\Dia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaFormacionPosicion extends Model
{
    use HasFactory;

    protected $table = 'area_formacion_posiciones';

    protected $fillable = [
        'area_formacion_id',
        'grado_id',
        'dia',
        'bloque',
        'tipo',
    ];

    protected $casts = [
        'dia' => Dia::class,
        'bloque' => 'integer',
    ];

    public function areaFormacion(): BelongsTo
    {
        return $this->belongsTo(AreaFormacion::class, 'area_formacion_id');
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }
}
