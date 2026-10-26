<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargaAcademica extends Model
{
    use HasFactory;

    protected $table = 'cargas_academicas';

    protected $fillable = [
        'anio_escolar_id',
        'docente_id',
        'area_formacion_id',
        'seccion_id',
    ];

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'docente_id');
    }

    public function areaFormacion()
    {
        return $this->belongsTo(AreaFormacion::class, 'area_formacion_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function horarioAsignaciones()
    {
        return $this->hasMany(HorarioAsignacion::class, 'carga_academica_id');
    }
}
