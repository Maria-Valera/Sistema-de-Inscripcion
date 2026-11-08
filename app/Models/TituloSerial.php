<?php

namespace App\Models;

use App\Enums\EstadoSerialTitulo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TituloSerial extends Model
{
    // Se pone explicito: el plural automatico de Laravel seria "titulo_serials".
    protected $table = 'titulo_seriales';

    // Valor por defecto tambien en el modelo (asi un registro recien creado ya lo trae).
    protected $attributes = [
        'estado' => 'disponible',
    ];

    protected $fillable = [
        'anio_escolar_id',
        'serial',
        'fecha_recepcion',
        'estado',
        'motivo_anulacion',
        'fecha_liquidacion',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
        'estado' => EstadoSerialTitulo::class,
        'fecha_liquidacion' => 'date',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }
}
