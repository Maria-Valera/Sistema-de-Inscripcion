<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\ConvierteAMayusculas;

class Docente extends Model
{
    use HasFactory;
    use ConvierteAMayusculas;

    protected $table = 'docentes';

    protected $fillable = [
        'anio_escolar_id',
        'codigo',
        'dependencia',
        'horas_academicas',
        'status',
        'persona_id',
    ];

    protected $mayusculas = [
        'dependencia'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeBuscar($query, $buscar)
    {
        if (!empty($buscar)) {
            $query->whereHas('persona', function ($q) use ($buscar) {
                $q->where(DB::raw("CONCAT(primer_nombre, ' ', primer_apellido)"), 'LIKE', "%{$buscar}%")
                    ->orWhere('numero_documento', 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('primer_apellido', 'LIKE', "%{$buscar}%")
                    ->orWhere('codigo', 'LIKE', "%{$buscar}%");
            });
        }

        return $query;
    }

    /**
     * Relación con DetalleDocenteEstudio
     */
    public function detalleDocenteEstudio()
    {
        return $this->hasMany(DetalleDocenteEstudio::class, 'docente_id', 'id');
    }

    public function anioEscolar()
    {
        return $this->belongsTo(AnioEscolar::class, 'anio_escolar_id');
    }

    /**
     * Relación directa con estudios realizados (many-to-many)
     */
    public function estudiosRealizados()
    {
        return $this->belongsToMany(
            EstudiosRealizado::class,
            'detalle_docente_estudios',
            'docente_id',
            'estudios_id'
        );
    }

    public function asignacionesAreas()
    {
        return $this->hasManyThrough(
            DocenteAreaGrado::class,
            DetalleDocenteEstudio::class,
            'docente_id',                    
            'docente_estudio_realizado_id',  
            'id',                            
            'id'                            
        );
    }

    public function asignacionesAreasActivas()
    {
        return $this->asignacionesAreas()->where('docente_area_grados.status', true);
    }

    public function docenteAreaGrado()
    {
        return $this->hasManyThrough(
            DocenteAreaGrado::class,
            DetalleDocenteEstudio::class,
            'docente_id',
            'docente_estudio_realizado_id',
            'id',
            'id'
        );
    }



    public function detalleEstudios()
    {
        return $this->hasMany(DetalleDocenteEstudio::class, 'docente_id', 'id')->where('status', true);
    }

    /**
     * Relación con Persona
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * Relación con DocenteNoDisponibilidad
     */
    public function noDisponibilidades()
    {
        return $this->hasMany(DocenteNoDisponibilidad::class, 'docente_id');
    }


    /**
     * Accessor para obtener el nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        if (!$this->persona) {
            return '';
        }

        return trim(
            $this->persona->primer_nombre . ' ' .
                ($this->persona->segundo_nombre ?? '') . ' ' .
                ($this->persona->tercer_nombre ?? '') . ' ' .
                $this->persona->primer_apellido . ' ' .
                ($this->persona->segundo_apellido ?? '')
        );
    }


    public function reportePDF($id)
    {
        $docente = Docente::with([
            'persona.tipoDocumento',
            'persona.genero',
            'detalleDocenteEstudio.estudiosRealizado',
            'detalleDocenteEstudio.docenteAreaGrados.areaEstudioRealizado.areaFormacion',
            'detalleDocenteEstudio.docenteAreaGrados.grado'
        ])
            ->leftJoin('personas', 'docentes.persona_id', '=', 'personas.id')
            ->leftJoin('tipo_documentos', 'personas.tipo_documento_id', '=', 'tipo_documentos.id')
            ->leftJoin('generos', 'personas.genero_id', '=', 'generos.id')
            ->leftJoin('detalle_docente_estudios', 'docentes.id', '=', 'detalle_docente_estudios.docente_id')
            ->leftJoin('estudios_realizados', 'detalle_docente_estudios.estudios_id', '=', 'estudios_realizados.id')
            ->leftJoin('docente_area_grados', 'docente_area_grados.docente_estudio_realizado_id', '=', 'detalle_docente_estudios.id')
            ->leftJoin('area_estudio_realizados', 'docente_area_grados.area_estudio_realizado_id', '=', 'area_estudio_realizados.id')
            ->leftJoin('area_formacions', 'area_estudio_realizados.area_formacion_id', '=', 'area_formacions.id')
            ->leftJoin('grado_area_formacions', 'docente_area_grados.grado_id', '=', 'grado_area_formacions.id')
            ->find($id);

        if (!$docente) {
            return response('No se encontró el docente solicitado', 404);
        }

        // Verificar si se cargaron los datos de la persona
        if ($docente->persona) {
            // Mapear los datos de la persona al objeto docente
            $docente->tipo_documento = $docente->tipo_documento_abreviatura ?? 'N/A';
            $docente->numero_documento = $docente->persona->numero_documento ?? 'N/A';
            $docente->primer_nombre = $docente->persona->primer_nombre ?? 'N/A';
            $docente->segundo_nombre = $docente->persona->segundo_nombre ?? 'N/A';
            $docente->tercer_nombre = $docente->persona->tercer_nombre ?? 'N/A';
            $docente->primer_apellido = $docente->persona->primer_apellido ?? 'N/A';
            $docente->segundo_apellido = $docente->persona->segundo_apellido ?? 'N/A';
            $docente->fecha_nacimiento = $docente->persona->fecha_nacimiento ?? 'N/A';
            $docente->genero = $docente->persona->genero->nombre ?? 'N/A';
            $docente->email = $docente->persona->email ?? 'N/A';
            $docente->direccion = $docente->persona->direccion ?? 'N/A';
            $docente->telefono = $docente->persona->primer_telefono ?? $docente->persona->segundo_telefono ?? 'N/A';
            $docente->telefono_dos = $docente->persona->telefono_dos ?? 'N/A';
        }

        // Obtener estudios realizados
        $estudios = DB::table('estudios_realizados')
            ->where('status', true)
            ->get();

        // Obtener las materias (áreas de formación) del docente
        $materias = DB::table('docentes as d')
            ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
            ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
            ->join('area_estudio_realizados as aer', 'dag.area_estudio_realizado_id', '=', 'aer.id')
            ->join('area_formacions as af', 'aer.area_formacion_id', '=', 'af.id')
            ->join('grados as g', 'dag.grado_id', '=', 'g.id')
            ->where('d.id', $id)
            ->where('dde.status', true)
            ->select(
                'af.nombre_area_formacion as materia',
                'g.nombre as grado'
            )
            ->get();

        // Obtener estudiantes asignados al docente
        $estudiantes = DB::table('docentes as d')
            ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
            ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
            ->join('inscripcions as i', function($join) {
                $join->on('i.grado_id', '=', 'dag.grado_id')
                     ->where('i.status', true);
            })
            ->join('alumnos as a', 'i.alumno_id', '=', 'a.id')
            ->join('personas as p', 'a.persona_id', '=', 'p.id')
            ->join('grados as g', 'i.grado_id', '=', 'g.id')
            ->join('seccions as s', 'i.seccion_id', '=', 's.id')
            ->where('d.id', $id)
            ->where('dde.status', true)
            ->where('dag.status', true)
            ->where('a.status', true)
            ->distinct()
            ->select(
                'p.primer_nombre',
                'p.segundo_nombre',
                'p.tercer_nombre',
                'p.primer_apellido',
                'p.segundo_apellido',
                'p.numero_documento',
                'g.nombre as grado',
                's.nombre as seccion'
            )
            ->orderBy('p.primer_apellido')
            ->orderBy('p.primer_nombre')
            ->get();

        // Obtener grados y secciones asignados al docente
        $gradosSecciones = DB::table('docentes as d')
            ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
            ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
            ->join('grados as g', 'dag.grado_id', '=', 'g.id')
            ->join('seccions as s', 'g.id', '=', 's.grado_id')
            ->where('d.id', $id)
            ->where('dde.status', true)
            ->where('dag.status', true)
            ->where('s.status', true)
            ->distinct()
            ->select(
                'g.nombre as grado',
                's.nombre as seccion'
            )
            ->orderBy('g.nombre')
            ->orderBy('s.nombre')
            ->get();

        $pdf = PDF::loadView('admin.docente.reportes.individual_PDF', [
            'docente' => $docente,
            'estudiantes' => $estudiantes,
            'materias' => $materias,
            'gradosSecciones' => $gradosSecciones
        ]);

        return $pdf->stream('docente_' . ($docente->numero_documento ?? $docente->id) . '.pdf');
    }
}
