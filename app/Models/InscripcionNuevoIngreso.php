<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscripcionNuevoIngreso extends Model
{
    protected $table = 'inscripcion_nuevo_ingresos';
    protected $fillable = [
        'numero_zonificacion',
        'inscripcion_id',
        'institucion_procedencia_id',
        'expresion_literaria_id',
        'anio_egreso',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'inscripcion_id', 'id');
    }

    public function expresionLiteraria()
    {
        return $this->belongsTo(ExpresionLiteraria::class, 'expresion_literaria_id', 'id');
    }

    public function institucionProcedencia()
    {
        return $this->belongsTo(InstitucionProcedencia::class, 'institucion_procedencia_id', 'id');
    }


    public static function reporteGeneralPDF(array $filtros = [])
    {
        $anioEscolarId = $filtros['anio_escolar_id'] ?? null;
        $gradoId = $filtros['grado_id'] ?? null;
        $seccionId = $filtros['seccion_id'] ?? null;
        $estatusInscripcion = $filtros['status'] ?? null;
        $buscar = $filtros['buscar'] ?? null;
        return self::query()
            ->with([
                'inscripcion.alumno.persona.tipoDocumento',
                'inscripcion.alumno.persona.genero',
                'inscripcion.alumno.persona.localidad.estado.pais',
                'inscripcion.alumno.discapacidades',
                'inscripcion.alumno.etniaIndigena',
                'inscripcion.grado',
                'inscripcion.seccionAsignada',
                'inscripcion.representanteLegal.representante.persona',
                'institucionProcedencia',
                'expresionLiteraria',
            ])
            ->whereHas('inscripcion', function ($q) use ($anioEscolarId, $gradoId, $seccionId, $estatusInscripcion) {
                if ($anioEscolarId) {
                    $q->where('inscripcions.anio_escolar_id', $anioEscolarId);
                }
                if ($gradoId) {
                    $q->where('grado_id', $gradoId);
                }
                if ($seccionId) {
                    $q->where('seccion_id', $seccionId);
                }
                if ($estatusInscripcion) {
                    $q->where('status', $estatusInscripcion);
                }
            })
            ->when($buscar, function ($query, $buscar) {
                $query->whereHas('inscripcion.alumno.persona', function ($q) use ($buscar) {
                    $q->where(function ($subQuery) use ($buscar) {
                        $subQuery->where('primer_nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('segundo_nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('tercer_nombre', 'LIKE', "%{$buscar}%")
                            ->orWhere('primer_apellido', 'LIKE', "%{$buscar}%")
                            ->orWhere('segundo_apellido', 'LIKE', "%{$buscar}%")
                            ->orWhere('numero_documento', 'LIKE', "%{$buscar}%");
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
