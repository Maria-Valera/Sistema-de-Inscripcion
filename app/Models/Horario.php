<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    
    protected $table = 'horario';
    
    protected $fillable = [
        'aula_id',
        'anio_escolar_id',
        'status',
    ];
    
    public function aula()
    {
        return $this->belongsTo(Aula::class);
    }
    
    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class);
    }
    
    public function bloqueHorarios()
    {
        return $this->belongsToMany(BloqueHorario::class, 'horario__bloque_hora', 'horario_id', 'bloque_hora_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }
    
    public function diasSemana()
    {
        return $this->belongsToMany(DiaSemana::class, 'horario__dias_semana', 'horario_id', 'dia_semana_id')
                    ->withPivot('status')
                    ->withTimestamps();
    }
    
    public function docentesAreaFormacion()
    {
        return $this->hasMany(Horario_Docente_AreaFormacion::class, 'horario_id');
    }
}
