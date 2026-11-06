<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscalaLiteral extends Model
{
    use HasFactory;

    protected $table = 'escala_literals';

    protected $fillable = [
        'anio_escolar_id',
        'letra',
        'descripcion',
        'nota_desde',
        'nota_hasta',
    ];

    protected $casts = [
        'nota_desde' => 'integer',
        'nota_hasta' => 'integer',
    ];

    /**
     * Relación con el Año Escolar.
     */
    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    /**
     * Scope para filtrar por año escolar.
     */
    public function scopeDelAnio($query, $anioId)
    {
        return $query->where('anio_escolar_id', $anioId);
    }
}

