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
        'dias_semana_id',
        'id_bloque_hora',
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

    public function diaSemana(): BelongsTo
    {
        return $this->belongsTo(Dias_semana::class, 'dias_semana_id');
    }

    public function bloqueHorario(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'id_bloque_hora');
    }
}
