<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EjecucionesPercentil;
use App\Models\Grado;

class Seccion extends Model
{
    use HasFactory;
    protected $table = 'seccions';
    protected $fillable = [
        'nombre',
        'cantidad_actual',
        'grado_id',
        'ejecucion_percentil_id',
        'status',
    ];

    public function inscripcionesProsecucion()
    {
        return $this->hasMany(
            InscripcionProsecucion::class,
            'seccion_id'
        );
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function ejecucion()
    {
        return $this->belongsTo(
            EjecucionesPercentil::class,
            'ejecucion_percentil_id'
        );
    }

    public function entradasPercentil()
    {
        return $this->hasMany(EntradasPercentil::class, 'seccion_id');
    }

    public function seccion(){
        return $this->belongsTo(seccion_aulas::class, "id_seccion", "id");
    }
}
