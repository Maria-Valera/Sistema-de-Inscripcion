<?php

namespace App\Models;

use App\Enums\EstadoCalendario;
use App\Enums\OrigenCalendario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarioAcademico extends Model
{
    //

    protected $fillable = [
        'anio_escolar_id',
        'origen',
        'pdf_original',
        'estado',
        'fecha_confirmacion',
    ];

    protected $casts = [
        'origen' => OrigenCalendario::class,
        'estado' => EstadoCalendario::class,
        'fecha_confirmacion' => 'date',
    ];

    /*
    cada calendario academico pertenece a un unico año escolar
    */
    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    /*
    un calendario academico tiene muchos dias asociados
    sin importar si vinieron del camino pdf o de registro manual
    */

    public function dias():HasMany{
        return $this->hasMany(CalendarioDia::class, 'calendario_id');
    }

    /*
    atajo para solo dias ya confirmados de este calendario
    es lo que el resto del sistema (como inasistencias, procesos administrativos , etc) deberia consultar siempre, nunca los candidatos sin confirmar
    */
    public function diasConfirmados():HasMany{
        return $this->dias()->where('confirmado', true);
    }

    public function esManual(): bool
    {
        return $this->origen === OrigenCalendario::Manual;
    }

    public function esPdf(): bool
    {
        return $this->origen === OrigenCalendario::Pdf;
    }

    public function estaConfirmado(): bool
    {
        return $this->estado === EstadoCalendario::Confirmado;
    }

}
