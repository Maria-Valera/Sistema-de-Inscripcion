<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario_DiasSemana extends Model
{
    use HasFactory;
    
    protected $table = 'horario__dias_semana';
    
    protected $fillable = [
        'horario_id',
        'dia_semana_id',
        'status',
    ];
    
    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
    
    public function diaSemana()
    {
        return $this->belongsTo(Dias_semana::class);
    }
}
