<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecesoHorario extends Model
{
    use HasFactory;

    protected $table = 'receso_horario';

    protected $fillable = [
        'configuracion_horario_id',
        'despues_de_bloque',
        'duracion_min',
    ];

    protected $casts = [
        'despues_de_bloque' => 'integer',
        'duracion_min' => 'integer',
    ];

    public function configuracion(): BelongsTo
    {
        return $this->belongsTo(ConfiguracionHorario::class, 'configuracion_horario_id');
    }
}
