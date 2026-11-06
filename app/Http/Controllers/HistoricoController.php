<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AnioEscolar;
use App\Support\AnioEscolarSeleccionado;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\Inscripcion;
use App\Models\InscripcionNuevoIngreso;
use App\Models\InscripcionProsecucion;
use App\Models\Grado;
use App\Models\Seccion;


class HistoricoController extends Controller
{
    public function index(Request $request)
    {
        $anioEscolarId = $request->has('anio_escolar_id') ? $request->anio_escolar_id : AnioEscolarSeleccionado::id();
        $tipo = $request->get('tipo', 'inscripciones');
        $modalidad = $request->get('modalidad');
        $gradoId = $request->grado_id;
        $seccionId = $request->seccion_id;

        $anios = AnioEscolar::orderBy('inicio_anio_escolar', 'desc')->get();
        $grados = Grado::orderBy('numero_grado')->get();

        $secciones = collect();

        if ($gradoId) {
            $secciones = Seccion::where('grado_id', $gradoId)
                ->orderBy('nombre')
                ->get();
        }


        if ($tipo === 'docentes') {

            $docentes = Docente::with([
                'persona',
                'anioEscolar',
                'asignacionesAreas.grado',
                'asignacionesAreas.seccion',
                'asignacionesAreas.areaEstudios.areaFormacion'
            ])
                ->when(
                    $anioEscolarId,
                    fn($q) =>
                    $q->where('docentes.anio_escolar_id', $anioEscolarId)
                )

                ->when($gradoId, function ($q) use ($gradoId) {
                    $q->whereHas('asignacionesAreas', function ($sub) use ($gradoId) {
                        $sub->where('grado_id', $gradoId);
                    });
                })

                ->when($seccionId, function ($q) use ($seccionId) {
                    $q->whereHas('asignacionesAreas', function ($sub) use ($seccionId) {
                        $sub->where('seccion_id', $seccionId);
                    });
                })

                ->paginate(10)
                ->withQueryString();

            return view('admin.historico.index', compact(
                'docentes',
                'anios',
                'anioEscolarId',
                'tipo',
                'modalidad',
                'grados',
                'secciones',
                'gradoId'
            ));
        }


        if ($modalidad === 'nuevo_ingreso') {
            $inscripciones = Inscripcion::with([
                'anioEscolar',
                'alumno.persona',
                'grado',
                'seccion',
                'seccionAsignada',
                'nuevoIngreso.institucionProcedencia',
                'nuevoIngreso.expresionLiteraria'
            ])
                ->whereHas('nuevoIngreso')
                ->when($anioEscolarId, fn($q) => $q->where('inscripcions.anio_escolar_id', $anioEscolarId))
                ->when($gradoId, fn($q) => $q->where('grado_id', $gradoId))
                ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))
                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('admin.historico.index', compact(
                'inscripciones',
                'anios',
                'anioEscolarId',
                'tipo',
                'modalidad',
                'grados',
                'secciones',
                'gradoId'
            ));
        }

        if ($modalidad === 'prosecucion') {
            $inscripciones = InscripcionProsecucion::with([
                'inscripcion.anioEscolar',
                'inscripcion.alumno.persona',
                'inscripcion.grado',
                'inscripcion.seccion',
                'anioEscolar',
                'grado',
                'seccion'
            ])
                ->where('status', 'Activo')
                ->when($anioEscolarId, fn($q) => $q->where('inscripcion_prosecucions.anio_escolar_id', $anioEscolarId))
                ->when(
                    $gradoId,
                    fn($q) =>
                    $q->whereHas('inscripcion', fn($i) => $i->where('grado_id', $gradoId))
                )
                ->when(
                    $seccionId,
                    fn($q) =>
                    $q->whereHas('inscripcion', fn($i) => $i->where('seccion_id', $seccionId))
                )

                ->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('admin.historico.index', compact(
                'inscripciones',
                'anios',
                'anioEscolarId',
                'tipo',
                'modalidad',
                'grados',
                'secciones',
                'gradoId'
            ));
        }

        $inscripciones = Inscripcion::with([
            'anioEscolar',
            'alumno.persona',
            'grado',
            'seccion',
            'seccionAsignada',
            'nuevoIngreso',
            'prosecucion.grado',
            'prosecucion.seccion'
        ])
            ->whereNull('deleted_at')
            ->when($anioEscolarId, fn($q) => $q->where('inscripcions.anio_escolar_id', $anioEscolarId))
            ->when($gradoId, fn($q) => $q->where('grado_id', $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))

            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.historico.index', compact(
            'inscripciones',
            'anios',
            'anioEscolarId',
            'tipo',
            'modalidad',
            'grados',
            'secciones',
            'gradoId'
        ));
    }

    public function seccionesPorGrado($gradoId)
    {
        $secciones = Seccion::where('grado_id', $gradoId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($secciones);
    }
}
