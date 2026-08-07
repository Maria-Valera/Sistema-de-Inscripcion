<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocenteAreaGrado extends Model
{
    protected $table = 'docente_area_grados';

    protected $fillable = [
        'docente_estudio_realizado_id',
        'area_estudio_realizado_id',
        'grupo_estable_id',
        'grado_id',
        'grado_grupo_estable_id',
        'seccion_id',
        'tipo_asignacion',
        'status',
    ];

    public function scopeBuscar($query, $buscar)
    {
        if (empty($buscar)) {
            return $query;
        }

        $buscar = trim($buscar);

        return $query->where(function ($q) use ($buscar) {
            $q->whereHas('persona', function ($p) use ($buscar) {
                $p->where(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_apellido', 'LIKE', "%{$buscar}%")
                    ->orWhere('numero_documento', 'LIKE', "%{$buscar}%");
            });

            $q->orWhereHas('areaEstudios', function ($d) use ($buscar) {
                $d->whereHas('areaFormacion', function ($e) use ($buscar) {
                    $e->where('nombre_area_formacion', 'LIKE', "%{$buscar}%");
                });
            });
        });
    }

    public function detalleDocenteEstudio()
    {
        return $this->belongsTo(DetalleDocenteEstudio::class, 'docente_estudio_realizado_id');
    }

    public function areaEstudios()
    {
        return $this->belongsTo(AreaEstudioRealizado::class, 'area_estudio_realizado_id');
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'grado_id');
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'seccion_id');
    }

    public function gradoGrupoEstable()
    {
        return $this->belongsTo(Grado::class, 'grado_grupo_estable_id');
    }

    public function grupoEstable()
    {
        return $this->belongsTo(GrupoEstable::class);
    }
    
    public function horarios()
    {
        return $this->hasMany(HorarioDocenteAreaFormacion::class, 'docente_area_grado_id');
    }
}
