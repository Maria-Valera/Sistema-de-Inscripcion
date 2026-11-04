<?php

namespace App\Models;

use App\Enums\Dia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteIndisponibilidad extends Model
{
    use HasFactory;

    protected $table = 'docente_indisponibilidad';

    protected $fillable = [
        'docente_id',
        'anio_escolar_id',
        'dia',
        'bloque',
        'tipo',
        'estado',
        'motivo',
    ];

    protected $casts = [
        'dia' => Dia::class,
        'bloque' => 'integer',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }
}
