<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario_Docente_AreaFormacion extends Model
{
    protected $table = 'horario__docente__area__formacion';
    
    protected $fillable = [
        'horario_id',
        'docente_area_grado_id',
    ];
    
    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
    
    public function docenteAreaGrado()
    {
        return $this->belongsTo(DocenteAreaGrado::class);
    }
}
