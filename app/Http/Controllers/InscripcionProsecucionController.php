<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Genero;
use App\Models\TipoDocumento;
use App\Models\Alumno;
use App\Models\ExpresionLiteraria;
use App\Models\InscripcionProsecucion;
use App\Models\Grado;
use App\Models\InstitucionProcedencia;
use App\Models\Seccion;
use App\Support\AnioEscolarSeleccionado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
 
class InscripcionProsecucionController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolarSeleccionado::esActual();
    }

    public function index(Request $request)
    {
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();
        $anioId = AnioEscolarSeleccionado::id();
        $buscar = $request->buscar;
        $gradoId = $request->grado_id;
        $seccionId = $request->seccion_id;
        $status = $request->status;
        $materiasPendientes = $request->materias_pendientes;
        $grados = Grado::where('status', true)
            ->orderBy('numero_grado')
            ->get();
        $secciones = collect();
        if ($gradoId) {
            $secciones = Seccion::where('grado_id', $gradoId)
                ->where('status', true)
                ->orderBy('nombre')
                ->get();
        }
        $status = $request->get('status', 'Activo');
        $prosecuciones = InscripcionProsecucion::with([
            'prosecucionAreas',
            'inscripcion.alumno.persona',
            'inscripcion.alumno.persona.tipoDocumento',
            'inscripcion.representanteLegal.representante.persona',
            'grado',
            'seccion',
            'anioEscolar',
        ]);

        $user = auth()->user();
        if ($user && $user->hasRole('Representante')) {
            $prosecuciones->whereHas('inscripcion.alumno', fn($q) => $q->visiblesParaRepresentante($user));
        } elseif ($user && $user->hasRole('Docente')) {
            $prosecuciones->whereHas('inscripcion.alumno', fn($q) => $q->visiblesParaDocente($user));
        }

        $prosecuciones = $prosecuciones
            ->whereHas('inscripcion', function ($q) use ($anioId) {
                $q->where('tipo_inscripcion', 'prosecucion');

                if ($anioId) {
                    $q->where('inscripcions.anio_escolar_id', $anioId);
                }
            })
            ->when($gradoId, fn($q) => $q->where('grado_id', $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))
            ->when($buscar, function ($q) use ($buscar) {
                $q->whereHas('inscripcion.alumno.persona', function ($qq) use ($buscar) {
                    $qq->where('primer_nombre', 'like', "%$buscar%")
                        ->orWhere('primer_apellido', 'like', "%$buscar%")
                        ->orWhere('numero_documento', 'like', "%$buscar%");
                });
            })
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($materiasPendientes, function ($q) use ($materiasPendientes) {
                if ($materiasPendientes === 'con_pendientes') {
                    $q->whereHas(
                        'prosecucionAreas',
                        fn($subQ) =>
                        $subQ->where('status', 'pendiente')
                    );
                } elseif ($materiasPendientes === 'sin_pendientes') {
                    $q->whereDoesntHave(
                        'prosecucionAreas',
                        fn($subQ) =>
                        $subQ->where('status', 'pendiente')
                    );
                }
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        // Alumnos disponibles para prosecución (para la modal de selección)
        $alumnosDisponibles = collect();
        if ($anioEscolarActivo) {
            $anioAnterior = \App\Models\AnioEscolar::where('inicio_anio_escolar', '<', $anioEscolarActivo->inicio_anio_escolar)
                ->orderByDesc('inicio_anio_escolar')
                ->first();

            if ($anioAnterior) {
                $queryAlumnos = Alumno::whereHas('inscripciones', function ($q) use ($anioAnterior) {
                    $q->where('anio_escolar_id', $anioAnterior->id);
                })
                ->whereDoesntHave('inscripciones', function ($q) use ($anioEscolarActivo) {
                    $q->where('anio_escolar_id', $anioEscolarActivo->id);
                });

                if ($user && $user->hasRole('Representante')) {
                    $queryAlumnos->visiblesParaRepresentante($user);
                }

                $alumnosDisponibles = $queryAlumnos->with([
                    'persona.tipoDocumento',
                    'inscripciones' => function ($q) use ($anioAnterior) {
                        $q->where('anio_escolar_id', $anioAnterior->id)->with('grado');
                    },
                ])
                ->orderBy('id')
                ->get();
            }
        }

        return view('admin.transacciones.inscripcion_prosecucion.index', [
            'anioEscolarActivo' => (bool) $anioEscolarActivo,
            'anioEscolar' => $anioEscolarActivo,
            'esActual' => AnioEscolarSeleccionado::esActual(),
            'prosecuciones' => $prosecuciones,
            'alumnosDisponibles' => $alumnosDisponibles,
            'grados' => $grados,
            'secciones' => $secciones,
            'buscar' => $buscar,
            'gradoId' => $gradoId,
            'seccionId' => $seccionId,
            'status' => $status,
            'materiasPendientes' => $materiasPendientes,
        ]);
    }

    public function seccionesPorGrado($gradoId)
    {
        $secciones = Seccion::where('grado_id', $gradoId)
            ->where('status', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        return response()->json($secciones);
    }

    public function create(Request $request)
    {
        if (!AnioEscolarSeleccionado::esActual()) {
            return redirect()->route('admin.transacciones.inscripcion_prosecucion.index')
                ->with('warning', 'No puedes registrar nuevas prosecuciones en un año escolar anterior (solo lectura).');
        }

        // TAREA 4: Si no se seleccionó estudiante desde la modal, redirigir al index para evitar acceso por URL directa
        if (!$request->filled('alumno_id')) {
            return redirect()->route('admin.transacciones.inscripcion_prosecucion.index')
                ->with('warning', 'Seleccione un estudiante desde el botón "+ Registrar" para iniciar la prosecución.');
        }

        $alumnoId = $request->get('alumno_id');

        return view('admin.transacciones.inscripcion_prosecucion.create', compact('alumnoId'));
    }

    public function destroy($inscripcionId)
    {
        try {
            InscripcionProsecucion::inactivar($inscripcionId);
            return redirect()
                ->route('admin.transacciones.inscripcion_prosecucion.index')
                ->with('success', 'Inscripción por prosecución inactivada correctamente');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.transacciones.inscripcion_prosecucion.index')
                ->with('error', 'Error al inactivar la inscripción: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        InscripcionProsecucion::restaurar($id);
        return redirect()->route('admin.transacciones.inscripcion_prosecucion.index')->with('success', 'Inscripción por prosecución restaurada correctamente');
    }

    public function reporte($id)
    {
        $prosecucion = InscripcionProsecucion::with([
            'inscripcion.alumno.persona',
            'inscripcion.alumno.persona.localidad.estado.pais',
            'inscripcion.alumno.ordenNacimiento',
            'inscripcion.alumno.lateralidad',
            'inscripcion.alumno.discapacidades',
            'inscripcion.alumno.etniaIndigena',
            'inscripcion.alumno.tallaCamisa',
            'inscripcion.alumno.tallaPantalon',
            'inscripcion.grado',
            'inscripcion.seccionAsignada', 
            'inscripcion.padre.persona',
            'inscripcion.madre.persona',
            'inscripcion.representanteLegal.representante.persona',
            'inscripcion.representanteLegal.banco',
            'grado',
            'seccion',
            'anioEscolar',
            'prosecucionAreas.gradoAreaFormacion.area_formacion',
        ])->findOrFail($id);
        $datosCompletos = $prosecucion->inscripcion->obtenerDatosCompletos();
        $datosCompletos['prosecucion'] = [
            'grado_anterior' => $prosecucion->inscripcion->grado->numero_grado ?? 'N/A',
            'grado_actual' => $prosecucion->grado->numero_grado ?? 'N/A',
            'seccion' => $prosecucion->seccion->nombre ?? 'N/A',
            'promovido' => $prosecucion->promovido ? 'Sí' : 'No',
            'repite_grado' => $prosecucion->repite_grado ? 'Sí' : 'No',
            'observaciones' => $prosecucion->observaciones,
            'acepta_normas_contrato' => $prosecucion->acepta_normas_contrato ? 'Sí' : 'No',
            'materias_aprobadas' => $prosecucion->prosecucionAreas->where('status', 'aprobada'),
            'materias_pendientes' => $prosecucion->prosecucionAreas->where('status', 'pendiente'),
            'materias_reprobadas' => $prosecucion->prosecucionAreas->where('status', 'reprobada'),
        ];
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();
        $pdf = PDF::loadview('admin.transacciones.inscripcion_prosecucion.reportes.ficha_inscripcion', compact('datosCompletos', 'anioEscolarActivo'));
        $pdf->setOption('isPhpEnabled', true);
        return $pdf->stream('ficha_inscripcion_prosecucion.pdf');
    }

    public function reporteGeneralProsecucionPDF(Request $request)
    {
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();
        $filtro = $request->all();
        if (!isset($filtro['anio_escolar_id']) && $anioEscolarActivo) {
            $filtro['anio_escolar_id'] = $anioEscolarActivo->id;
        }
        $prosecuciones = InscripcionProsecucion::reporteGeneralPDF($filtro);
        $prosecuciones = $prosecuciones->sortBy(function ($item) {
            $primerApellido = $item->inscripcion->alumno->persona->primer_apellido ?? '';
            return strtoupper(substr($primerApellido, 0, 1));
        });
        if ($prosecuciones->isEmpty()) {
            return response('No se encontraron inscripciones de prosecución', 404);
        }
        $filtrosVista = [
            'anio_escolar' => $anioEscolarActivo ? ($anioEscolarActivo->nombre ?? $anioEscolarActivo->anio ?? null) : null,
        ];
        if (isset($filtro['grado_id']) && $filtro['grado_id']) {
            $grado = \App\Models\Grado::find($filtro['grado_id']);
            $filtrosVista['grado'] = $grado ? $grado->numero_grado : null;
        }
        if (isset($filtro['seccion_id']) && $filtro['seccion_id']) {
            $seccion = \App\Models\Seccion::find($filtro['seccion_id']);
            $filtrosVista['seccion'] = $seccion ? $seccion->nombre : null;
        }
        if (isset($filtro['status']) && $filtro['status'] !== '') {
            $filtrosVista['estatus'] = $filtro['status'] == '1' ? 'Activo' : 'Inactivo';
        }
        if (isset($filtro['buscar']) && $filtro['buscar']) {
            $filtrosVista['buscar'] = $filtro['buscar'];
        }
        if (isset($filtro['materias_pendientes']) && $filtro['materias_pendientes']) {
            if ($filtro['materias_pendientes'] === 'con_pendientes') {
                $filtrosVista['materias_pendientes'] = 'Con materias pendientes';
            } elseif ($filtro['materias_pendientes'] === 'sin_pendientes') {
                $filtrosVista['materias_pendientes'] = 'Sin materias pendientes';
            }
        }
        $pdf = Pdf::loadView(
            'admin.transacciones.inscripcion_prosecucion.reportes.reporte_general_prosecucion',
            [
                'prosecuciones' => $prosecuciones,
                'filtros' => $filtrosVista,
            ]
        );
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isPhpEnabled', true);
        return $pdf->stream('reporte_general_prosecucion.pdf');
    }
}
