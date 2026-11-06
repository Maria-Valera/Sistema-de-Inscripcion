<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoridadFirmante extends Model
{
    use HasFactory;

    protected $table = 'autoridad_firmantes';

    public const CARGOS = [
        'director' => 'Director(a)',
        'subdirector_academico' => 'Subdirector(a) Académico(a)',
        'coordinador_control_estudios' => 'Coordinador(a) de Control de Estudios',
        'coordinador_evaluacion' => 'Coordinador(a) de Evaluación',
        'docente_firmante_mppe' => 'Docente Firmante MPPE',
    ];

    protected $fillable = [
        'anio_escolar_id',
        'cargo',
        'persona_id',
        'encargado',
        'gaceta',
        'desde',
        'hasta',
    ];

    protected $casts = [
        'encargado' => 'boolean',
        'desde' => 'date:Y-m-d',
        'hasta' => 'date:Y-m-d',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function getCargoNombreAttribute(): string
    {
        return self::CARGOS[$this->cargo] ?? $this->cargo;
    }
}

