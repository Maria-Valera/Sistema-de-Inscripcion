<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionHorario extends Model
{
    use HasFactory;

    protected $table = 'configuracion_horario';

    protected $fillable = [
        'anio_escolar_id',
        'hora_inicio',
        'numero_bloques',
        'duracion_bloque_min',
        'minutos_entre_bloques',
    ];

    protected $casts = [
        'numero_bloques' => 'integer',
        'duracion_bloque_min' => 'integer',
        'minutos_entre_bloques' => 'integer',
    ];

    public function anioEscolar(): BelongsTo
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function recesos(): HasMany
    {
        return $this->hasMany(RecesoHorario::class, 'configuracion_horario_id')
            ->orderBy('despues_de_bloque');
    }
}
