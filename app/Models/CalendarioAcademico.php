<?php

namespace App\Models;

use App\Enums\EstadoCalendario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarioAcademico extends Model
{
    /**
     * Nombre real de la tabla en la base de datos. Se especifica explícitamente
     * porque la migración la creó como "calendario_academico" (singular), y el
     * plural automático de Eloquent ("calendario_academicos") no coincide.
     */
    protected $table = 'calendario_academico';

    protected $fillable = [
        'anio_escolar_id',
        'estado',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'estado' => EstadoCalendario::class,
        'fecha_confirmacion' => 'date',
    ];

    /**
     * Cada calendario académico pertenece a un único año escolar.
     * (relación inversa de ANIO_ESCOLARS ||--o| CALENDARIOS_ACADEMICOS del ERD)
     */
    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    /**
     * Un calendario académico tiene muchos días asociados,
     * sin importar si vinieron de PDF o de registro manual.
     */
    public function dias(): HasMany
    {
        return $this->hasMany(CalendarioDia::class, 'calendario_id');
    }

    /**
     * Atajo para solo los días ya confirmados de este calendario.
     * Es lo que el resto del sistema (inasistencias, procesos administrativos)
     * debería consultar siempre, nunca los candidatos sin confirmar.
     */
    public function diasConfirmados(): HasMany
    {
        return $this->dias()->where('confirmado', true);
    }

    public function estaConfirmado(): bool
    {
        return $this->estado === EstadoCalendario::Confirmado;
    }
}