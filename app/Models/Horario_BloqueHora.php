<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario_BloqueHora extends Model
{
    protected $table = 'horario__bloque_hora';
    
    protected $fillable = [
        'horario_id',
        'bloque_hora_id',
        'status',
    ];
    
    protected $casts = [
        'status' => 'boolean',
    ];
    
    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
    
    public function bloqueHora()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_hora_id');
    }
}
