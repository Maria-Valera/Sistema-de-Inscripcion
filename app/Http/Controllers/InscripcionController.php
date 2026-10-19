<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Genero;
use App\Models\TipoDocumento;
use App\Models\Alumno;
use App\Models\Grado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ExpresionLiteraria;
use App\Models\InstitucionProcedencia;
use App\Models\EntradasPercentil;
use App\Models\Seccion;
use App\Models\InscripcionNuevoIngreso;
use App\Exceptions\InscripcionException;


class InscripcionController extends Controller
{

    private function verificarAnioEscolar()
    {
        return \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index(Request $request)
    {
        $anioEscolarActivo = \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->first();

        $grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();

        $grado1 = Grado::find(1);
        $infoCupos = null;
        $entradasPercentil = collect();
        $seccionesResumen = collect();

        if ($grado1) {
            $inscritos = Inscripcion::where('grado_id', 1)
                ->where('status', 'Activo')
                ->when($anioEscolarActivo, function ($q) use ($anioEscolarActivo) {
                    $q->where('anio_escolar_id', $anioEscolarActivo->id);
                })
                ->count();

            $infoCupos = [
                'nombre_grado' => $grado1->nombre ?? '1er Año',
                'total_cupos' => $grado1->capacidad_max,
                'cupos_ocupados' => $inscritos,
                'cupos_disponibles' => $grado1->capacidad_max - $inscritos,
                'porcentaje_ocupacion' => $grado1->capacidad_max > 0
                    ? round(($inscritos / $grado1->capacidad_max) * 100, 2)
                    : 0
            ];

            $entradasPercentil = EntradasPercentil::with([
                'inscripcion.alumno.persona.tipoDocumento',
                'seccion'
            ])
                ->whereHas('inscripcion', function ($q) use ($grado1, $anioEscolarActivo) {
                    $q->where('grado_id', $grado1->id);
                    if ($anioEscolarActivo) {
                        $q->where('anio_escolar_id', $anioEscolarActivo->id);
                    }
                })
                ->orderBy('seccion_id')
                ->get();

            $seccionesResumen = EntradasPercentil::select('seccion_id')
                ->with('seccion')
                ->whereHas('inscripcion', function ($q) use ($grado1, $anioEscolarActivo) {
                    $q->where('grado_id', $grado1->id);
                    if ($anioEscolarActivo) {
                        $q->where('anio_escolar_id', $anioEscolarActivo->id);
                    }
                })
                ->groupBy('seccion_id')
                ->selectRaw('count(*) as total_estudiantes, seccion_id')
                ->get();
        }

        $buscar = $request->get('buscar');
        $gradoId = $request->get('grado_id');
        $seccionId = $request->get('seccion_id');
        $tipoInscripcion = $request->get('tipo_inscripcion');
        $status = $request->get('status', 'Activo');

        $secciones = collect();
        if ($gradoId) {
            $secciones = Seccion::where('grado_id', $gradoId)
                ->where('status', true)
                ->orderBy('nombre', 'asc')
                ->get();
        }

        $user = auth()->user();
        $query = Inscripcion::with([
            'alumno.persona',
            'alumno.discapacidades',
            'grado',
            'seccionAsignada',
            'nuevoIngreso',
            'anioEscolar'
        ]);

        if ($user && $user->hasRole('Representante')) {
            $query->whereHas('alumno', fn($q) => $q->visiblesParaRepresentante($user));
        } elseif ($user && $user->hasRole('Docente')) {
            $query->whereHas('alumno', fn($q) => $q->visiblesParaDocente($user));
        }

        $inscripciones = $query
            ->whereHas('nuevoIngreso', function ($q) use ($status) {
                if ($status) {
                    $q->where('status', $status);
                }
            })

            ->whereHas('anioEscolar', function ($q) {
                $q->whereIn('status', ['Activo', 'Extendido']);
            })

            ->when($status, fn($q) => $q->where('status', $status))

            ->when($gradoId, fn($q) => $q->where('grado_id', $gradoId))
            ->when($seccionId, fn($q) => $q->where('seccion_id', $seccionId))

            ->when($buscar, function ($q) use ($buscar) {
                $q->whereHas('alumno.persona', function ($qq) use ($buscar) {
                    $qq->where('primer_nombre', 'like', "%$buscar%")
                        ->orWhere('primer_apellido', 'like', "%$buscar%")
                        ->orWhere('numero_documento', 'like', "%$buscar%");
                });
            })

            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();


        return view('admin.transacciones.inscripcion.index', [
            'anioEscolarActivo' => $anioEscolarActivo ? true : false,
            'anioEscolar' => $anioEscolarActivo,
            'inscripciones' => $inscripciones,
            'grados' => $grados,
            'secciones' => $secciones,
            'buscar' => $buscar,
            'gradoId' => $gradoId,
            'seccionId' => $seccionId,
            'tipoInscripcion' => $tipoInscripcion,
            'status' => $status,
            'infoCupos' => $infoCupos,
            'entradasPercentil' => $entradasPercentil,
            'seccionesResumen' => $seccionesResumen
        ]);
    }

    public function create()
    {
        $this->verificarAnioEscolar();
        $personas = Persona::all();
        $generos = Genero::all();
        $tipoDocumentos = TipoDocumento::all();
        $alumnos = Alumno::all();
        $grados = Grado::all();
        $expresion_literaria = ExpresionLiteraria::all();
        $institucion_procedencia = InstitucionProcedencia::all();

        return view('admin.transacciones.inscripcion.create', compact('personas', 'generos', 'tipoDocumentos', 'alumnos', 'grados'));
    }

    public function createProsecucion()
    {
        $this->verificarAnioEscolar();
        $personas = Persona::all();
        $generos = Genero::all();
        $tipoDocumentos = TipoDocumento::all();
        $alumnos = Alumno::all();
        $grados = Grado::all();
        $expresion_literaria = ExpresionLiteraria::all();
        $institucion_procedencia = InstitucionProcedencia::all();
        return view('admin.transacciones.inscripcion.createProsecucion', compact('personas', 'generos', 'tipoDocumentos', 'alumnos', 'grados'));
    }

    public function seccionesPorGrado($gradoId)
    {
        $secciones = Seccion::where('grado_id', $gradoId)
            ->where('status', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
        return response()->json($secciones);
    }

    public function createAlumno()
    {
        return redirect()->route('admin.transacciones.inscripcion.create');
    }

    public function edit($id)
    {
        $inscripcion = Inscripcion::with(['nuevoIngreso', 'alumno'])->findOrFail($id);
        if (!$inscripcion->nuevoIngreso) {
            return redirect()->route('admin.transacciones.inscripcion.index')
                ->with('error', 'Esta inscripción no es de nuevo ingreso.');
        }
        return view('admin.transacciones.inscripcion.edit', compact('inscripcion'));
    }

    public function destroy($id)
    {
        Inscripcion::inactivar($id);
        return redirect()->route('admin.transacciones.inscripcion.index')->with('success', 'Inscripción inactivada correctamente');
    }

    public function restore($id)
    {
        Inscripcion::restaurar($id);
        return redirect()->route('admin.transacciones.inscripcion.index')->with('success', 'Inscripción restaurada correctamente');
    }

    public function reporte($id)
    {
        $inscripcion = Inscripcion::with([
            'anioEscolar',
            'alumno.persona',
            'alumno.persona.localidad.estado.pais',
            'alumno.ordenNacimiento',
            'alumno.lateralidad',
            'alumno.discapacidades',
            'alumno.etniaIndigena',
            'grado',
            'seccionAsignada',
            'madre.persona.prefijo',
            'madre.ocupacion',
            'madre.estado',
            'madre.municipios',
            'madre.localidads',

            'padre.persona.prefijo',
            'padre.ocupacion',
            'padre.estado',
            'padre.municipios',
            'padre.localidads',

            'representanteLegal.representante.persona.prefijo',
            'representanteLegal.representante.ocupacion',
            'representanteLegal.representante.estado',
            'representanteLegal.representante.municipios',
            'representanteLegal.representante.localidads',
            'representanteLegal.banco',

            'nuevoIngreso.institucionProcedencia',
            'nuevoIngreso.expresionLiteraria',
        ])->findOrFail($id);
        $datosCompletos = $inscripcion->obtenerDatosCompletos();
        $anioEscolarActivo = \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->first();
        $pdf = PDF::loadview('admin.transacciones.inscripcion.reporte.ficha_inscripcion', compact('datosCompletos', 'anioEscolarActivo'));
        $pdf->setOption('isPhpEnabled', true);
        return $pdf->stream('ficha_inscripcion.pdf');
    }

    public function reporteGeneralNuevoIngresoPDF(Request $request)
    {
        $anioEscolarActivo = \App\Models\AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->first();
        $filtro = $request->all();
        if (!isset($filtro['anio_escolar_id']) && $anioEscolarActivo) {
            $filtro['anio_escolar_id'] = $anioEscolarActivo->id;
        }
        $nuevoIngresos = InscripcionNuevoIngreso::reporteGeneralPDF($filtro);
        $nuevoIngresos = $nuevoIngresos->sortBy(function ($item) {
            $primerApellido = $item->inscripcion->alumno->persona->primer_apellido ?? '';
            return strtoupper(substr($primerApellido, 0, 1));
        });
        if ($nuevoIngresos->isEmpty()) {
            return response('No se encontraron inscripciones de nuevo ingreso', 404);
        }
        $filtrosVista = [
            'anio_escolar' => $anioEscolarActivo ? ($anioEscolarActivo->nombre ?? $anioEscolarActivo->anio ?? null) : null,
        ];
        $pdf = Pdf::loadView(
            'admin.transacciones.inscripcion.reporte.reporte_general_nuevo_ingreso',
            [
                'nuevoIngresos' => $nuevoIngresos,
                'filtros' => $filtrosVista,
            ]
        );
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('isPhpEnabled', true);
        return $pdf->stream('reporte_general_nuevo_ingreso.pdf');
    }
}
