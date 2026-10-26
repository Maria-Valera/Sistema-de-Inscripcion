<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\AreaFormacion;
use App\Models\Seccion;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\HorarioAsignacion;
use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Models\BloqueHorario;
use App\Models\Dias_semana;
use App\Models\ConfiguracionHorario;
use App\Services\ValidadorHorarioService;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    protected ValidadorHorarioService $validador;

    public function __construct(ValidadorHorarioService $validador)
    {
        $this->validador = $validador;
    }

    //muestra horarios en formato API
    public function IndexApi()
{
    $horarios = Horario::where('status', true)
        ->with([
            'aula',
            'bloqueHorarios',
            'diasSemana',
            'docentesAreaFormacion.docenteAreaGrado.detalleDocenteEstudio.docente.persona',
            'docentesAreaFormacion.docenteAreaGrado.areaEstudios.areaFormacion',
            'secciones.aulaFija',
            'secciones.grado'
        ])
        ->get();

    $resultado = $horarios->map(function ($horario) {
        $dia = $horario->diasSemana->first();
        $bloque = $horario->bloqueHorarios->first();
        $asignacion = $horario->docentesAreaFormacion->first();

        $docenteAreaGrado = $asignacion?->docenteAreaGrado;
        $detalleDocente = $docenteAreaGrado?->detalleDocenteEstudio;
        $areaEstudios = $docenteAreaGrado?->areaEstudios;

        return [
            'id' => $horario->id,
            'dia' => $dia,
            'bloque' => $bloque,
            'docente' => $detalleDocente?->docente ? [
                'id' => $detalleDocente->docente->id,
                'nombre' => $detalleDocente->docente->nombre_completo
            ] : null,
            'materia' => $areaEstudios?->areaFormacion ? [
                'id' => $areaEstudios->areaFormacion->id,
                'nombre' => $areaEstudios->areaFormacion->nombre_area_formacion,
                'tipo_aula_requerida' => $areaEstudios->areaFormacion->tipo_aula_requerida ?? 'normal'
            ] : null,
            'aula' => $horario->aula,
            'seccion' => $horario->secciones->first(),
            'aula_fija' => $horario->secciones->first()?->aulaFija,
            'grado' => $horario->secciones->first()?->grado,
        ];
    });

    return response()->json($resultado);
}

    //devuelve todas las asignaciones (HorarioAsignacion) de una sección para el modal "Ver Horario"
    public function showApi($grado_id, $seccion_id)
    {
        $anioEscolarActivo = AnioEscolar::activos()->first();

        if (!$anioEscolarActivo) {
            return response()->json(['message' => 'No hay año escolar activo.'], 404);
        }

        $seccion = Seccion::with('grado')
            ->where('id', $seccion_id)
            ->where('grado_id', $grado_id)
            ->first();

        if (!$seccion) {
            return response()->json(['message' => 'La sección no pertenece al grado indicado.'], 404);
        }

        $asignaciones = HorarioAsignacion::where('anio_escolar_id', $anioEscolarActivo->id)
            ->whereHas('cargaAcademica', function ($q) use ($seccion, $grado_id) {
                $q->where('seccion_id', $seccion->id);
            })
            ->with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'aula', 'dia', 'bloque'])
            ->orderBy('dia_id')
            ->orderBy('bloque_id')
            ->get()
            ->map(function ($a) {
                $carga = $a->cargaAcademica;
                $docente = $carga?->docente;
                $persona = $docente?->persona;
                $materia = $carga?->areaFormacion;
                return [
                    'id' => $a->id,
                    'dia_id' => (int) $a->dia_id,
                    'dia_nombre' => $a->dia?->nombre_dia,
                    'bloque_id' => (int) $a->bloque_id,
                    'docente_id' => $docente?->id,
                    'docente_nombre' => $persona ? trim($persona->primer_nombre . ' ' . $persona->primer_apellido) : null,
                    'materia_id' => $materia?->id,
                    'materia_nombre' => $materia?->nombre_area_formacion,
                    'aula_id' => $a->aula_id,
                    'aula_nombre' => $a->aula?->nombre_aula,
                    'conflicto_manual' => (bool) $a->conflicto_manual,
                ];
            })
            ->values();

        $dias = Dias_semana::whereBetween('id', [1, 5])->orderBy('id')->get(['id', 'nombre_dia']);

        $bloques = BloqueHorario::where('status', true)
            ->orderBy('hora_inicio', 'asc')
            ->get(['id', 'hora_inicio', 'hora_fin']);

        return response()->json([
            'grado' => ['id' => $seccion->grado?->id, 'numero_grado' => $seccion->grado?->numero_grado],
            'seccion' => ['id' => $seccion->id, 'nombre' => $seccion->nombre],
            'anio_escolar_id' => $anioEscolarActivo->id,
            'dias' => $dias,
            'bloques' => $bloques,
            'asignaciones' => $asignaciones,
        ]);
    }

    //muestra vista principal con grados, areas y horarios asignados
    public function index(Request $request)
    {
        $grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();

        $areasFormacion = AreaFormacion::where('status', true)
            ->orderBy('nombre_area_formacion', 'asc')
            ->get();

        $secciones = Seccion::where('status', true)
            ->orderBy('nombre', 'asc')
            ->get()
            ->unique('nombre');

        $bloquesHorarios = \App\Models\BloqueHorario::where('status', true)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $anioEscolarActivo = AnioEscolar::activos()->first();

        $horariosAsignados = collect();
        if ($anioEscolarActivo) {
            $query = HorarioAsignacion::where('anio_escolar_id', $anioEscolarActivo->id)
                ->with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula', 'dia', 'bloque']);

            // Aplicar filtros
            if ($request->filled('grado_id')) {
                $query->whereHas('cargaAcademica.seccion', function($q) use ($request) {
                    $q->where('grado_id', $request->grado_id);
                });
            }

            // El select de secciones viene con ->unique('nombre'), así que el id recibido es solo
            // representativo: se filtra por el NOMBRE de la sección para abarcar todos los grados.
            if ($request->filled('seccion_id')) {
                $nombreSeccion = Seccion::whereKey($request->seccion_id)->value('nombre');
                $query->whereHas('cargaAcademica.seccion', function($q) use ($nombreSeccion) {
                    $q->where('nombre', $nombreSeccion);
                });
            }

            // El filtro de área solo decide QUÉ secciones mostrar; el conteo y las áreas
            // se calculan con todas las asignaciones de esas secciones.
            if ($request->filled('area_formacion_id')) {
                $seccionesConArea = \App\Models\CargaAcademica::where('anio_escolar_id', $anioEscolarActivo->id)
                    ->where('area_formacion_id', $request->area_formacion_id)
                    ->whereHas('horarioAsignaciones')
                    ->distinct()
                    ->pluck('seccion_id');

                $query->whereHas('cargaAcademica', function($q) use ($seccionesConArea) {
                    $q->whereIn('seccion_id', $seccionesConArea);
                });
            }

            $asignaciones = $query->get();

            //agrupa asignaciones por grado y sección
            $horariosAsignados = $asignaciones->groupBy(function($asignacion) {
                $seccion = $asignacion->cargaAcademica?->seccion;
                return ($seccion?->grado?->numero_grado ?? '') . '-' . ($seccion?->nombre ?? '');
            })->map(function($asignacionesGrupo, $key) {
                $primeraAsignacion = $asignacionesGrupo->first();
                $seccion = $primeraAsignacion->cargaAcademica?->seccion;
                $grado = $seccion?->grado;
                $areasUnicas = $asignacionesGrupo->map(fn($a) => $a->cargaAcademica?->areaFormacion?->nombre_area_formacion)->filter()->unique()->values();

                return [
                    'id' => $key,
                    'grado' => $grado?->numero_grado,
                    'grado_id' => $grado?->id,
                    'seccion' => $seccion?->nombre,
                    'seccion_id' => $seccion?->id,
                    'areas' => $areasUnicas->implode(', '),
                    'total_asignaciones' => $asignacionesGrupo->count(),
                    'asignaciones' => $asignacionesGrupo
                ];
            })->values();
        }

        // Paginar los resultados
        $horariosPaginados = $this->paginateCollection($horariosAsignados, 10);

        // Verificar si existen errores críticos que impidan la creación de horarios
        $hayErroresCriticos = false;
        $erroresCriticos = [];

        if ($anioEscolarActivo) {
            $configuracion = ConfiguracionHorario::where('anio_escolar_id', $anioEscolarActivo->id)->first();
            if (!$configuracion) {
                $hayErroresCriticos = true;
                $erroresCriticos = [
                    [
                        'mensaje' => 'Configuración de marco horario pendiente',
                        'detalle' => 'Debes configurar el marco horario (bloques y recesos) antes de crear un horario.'
                    ]
                ];
            } else {
                $diagnostico = $this->validador->validar($anioEscolarActivo->id);
                if (!empty($diagnostico['errores'])) {
                    $hayErroresCriticos = true;
                    $erroresCriticos = $diagnostico['errores'];
                }
            }
        } else {
            $hayErroresCriticos = true;
            $erroresCriticos = [
                [
                    'mensaje' => 'Sin año escolar activo',
                    'detalle' => 'Debe existir un año escolar con estado Activo para crear y gestionar horarios.'
                ]
            ];
        }

        return view('admin.horario.index', compact(
            'grados',
            'areasFormacion',
            'secciones',
            'bloquesHorarios',
            'horariosPaginados',
            'hayErroresCriticos',
            'erroresCriticos'
        ));
    }

    // Método auxiliar para paginar colecciones
    private function paginateCollection($collection, $perPage)
    {
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $items = $collection->slice($offset, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    //muestra la vista para crear horario con docentes, aulas y datos
    public function create(Request $request)
    {
        $anioEscolarActivo = AnioEscolar::activos()->first();

        if (!$anioEscolarActivo) {
            return redirect()->route('admin.horario.index')
                ->with('error', 'No hay un año escolar activo.');
        }

        // 1. Verificar si existe configuración de horario para el año activo
        $configuracion = ConfiguracionHorario::where('anio_escolar_id', $anioEscolarActivo->id)->first();
        if (!$configuracion) {
            return redirect()->route('admin.horario.configuracion')
                ->with('warning', 'Debes configurar el marco horario (bloques y recesos) antes de crear un horario.');
        }

        // 2. Ejecutar el validador previo para el año activo
        $diagnostico = $this->validador->validar($anioEscolarActivo->id);
        if (!empty($diagnostico['errores'])) {
            return redirect()->route('admin.horario.index')
                ->with('warning', 'Hay errores en la carga académica que impiden generar el horario. Por favor, revisa el diagnóstico.')
                ->with('errores_validacion', $diagnostico['errores']);
        }

        $grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();

        $areasFormacion = AreaFormacion::where('status', true)
            ->orderBy('nombre_area_formacion', 'asc')
            ->get();

        $secciones = Seccion::where('status', true)
            ->orderBy('nombre', 'asc')
            ->get()
            ->unique('nombre');

        //filtra aulas (tipo_aula real en BD: Aula::TIPO_REGULAR = 'Aula Regular')
        $aulasNoRegulares = Aula::where('status', true)
            ->whereRaw('LOWER(TRIM(tipo_aula)) != ?', [mb_strtolower(Aula::TIPO_REGULAR)])
            ->orderBy('nombre_aula', 'asc')
            ->get();

        $aulasRegularesAsignadas = DB::table('aulas as a')
            ->join('seccion_aula as sa', 'a.id_aula', '=', 'sa.id_aula')
            ->join('seccions as s', 'sa.id_seccion', '=', 's.id')
            ->where('a.status', true)
            ->whereRaw('LOWER(TRIM(a.tipo_aula)) = ?', [mb_strtolower(Aula::TIPO_REGULAR)])
            ->where('s.status', true)
            ->select('a.*', 's.id as seccion_id', 's.nombre as seccion_nombre')
            ->get()
            ->map(function($aula) {
                return [
                    'id_aula' => $aula->id_aula,
                    'nombre_aula' => $aula->nombre_aula . ' (Sección: ' . $aula->seccion_nombre . ')',
                    'tipo_aula' => $aula->tipo_aula,
                    'seccion_id' => $aula->seccion_id
                ];
            });


        $aulas = $aulasNoRegulares->concat($aulasRegularesAsignadas);

        //carga docentes activos con sus areas
        $docentes = Docente::with(['persona', 'detalleDocenteEstudio.estudiosRealizado'])
            ->where('status', true)
            ->whereHas('detalleDocenteEstudio', function($query) {
                $query->where('status', true);
            })
            ->get()
            ->map(function($docente) {
                //obtiene areas del docente
                $areas = DB::table('docentes as d')
                    ->join('detalle_docente_estudios as dde', 'd.id', '=', 'dde.docente_id')
                    ->join('docente_area_grados as dag', 'dde.id', '=', 'dag.docente_estudio_realizado_id')
                    ->join('area_estudio_realizados as aer', 'dag.area_estudio_realizado_id', '=', 'aer.id')
                    ->join('area_formacions as af', 'aer.area_formacion_id', '=', 'af.id')
                    ->where('d.id', $docente->id)
                    ->where('dde.status', true)
                    ->where('dag.status', true)
                    ->where('af.status', true)
                    ->pluck('af.nombre_area_formacion')
                    ->toArray();

                //obtiene secciones del docente
                $secciones = DB::table('docente_area_grados as dag')
                    ->join('seccions as s', 'dag.seccion_id', '=', 's.id')
                    ->whereIn('dag.docente_estudio_realizado_id', function($query) use ($docente) {
                        $query->select('dde.id')
                            ->from('detalle_docente_estudios as dde')
                            ->where('dde.docente_id', $docente->id)
                            ->where('dde.status', true);
                    })
                    ->where('dag.status', true)
                    ->where('s.status', true)
                    ->select('s.id', 's.nombre')
                    ->distinct()
                    ->get()
                    ->toArray();

                return [
                    'id' => $docente->id,
                    'nombre' => $docente->nombre_completo,
                    'area' => !empty($areas) ? implode(', ', $areas) : 'Sin área asignada',
                    'areas' => $areas,
                    'secciones' => $secciones
                ];
            });

        //detecta modo de edición desde parametros
        $modoEdicion = $request->has('modo_edicion') && $request->modo_edicion === 'true';
        $editGradoId = $request->input('grado_id');
        $editSeccionId = $request->input('seccion_id');

        return view('admin.horario.create', compact(
            'grados', 
            'areasFormacion', 
            'secciones', 
            'docentes', 
            'aulas', 
            'anioEscolarActivo',
            'modoEdicion',
            'editGradoId',
            'editSeccionId'
        ));
    }

    //redirige a la vista de crear con modo edicion activado
    public function edit($grado_id, $seccion_id)
    {
        return redirect()->route('admin.horario.create', [
            'grado_id' => $grado_id,
            'seccion_id' => $seccion_id,
            'modo_edicion' => 'true'
        ]);
    }

    //actualiza horario (se maneja en la vista crear)
    public function update(Request $request, $grado_id, $seccion_id)
    {
        return redirect()->route('admin.horario.index')
            ->with('success', 'Horario actualizado exitosamente');
    }

    //elimina horario de un grado y sección especificos
    public function destroy(Request $request, $grado_id, $seccion_id)
    {
        try {
            $anioEscolarActivo = AnioEscolar::activos()->first();

            if (!$anioEscolarActivo) {
                return redirect()->back()->with('error', 'No hay año escolar activo. No se puede eliminar el horario.');
            }

            //elimina asignaciones de horario para el grado y sección
            $asignacionesEliminadas = HorarioAsignacion::where('anio_escolar_id', $anioEscolarActivo->id)
                ->whereHas('cargaAcademica', function($query) use ($grado_id, $seccion_id) {
                    $query->where('seccion_id', $seccion_id)
                          ->whereHas('seccion', fn($sq) => $sq->where('grado_id', $grado_id));
                })
                ->delete();

            if ($asignacionesEliminadas > 0) {
                return redirect()->route('admin.horario.index')
                    ->with('success', "Horario eliminado exitosamente. Se eliminaron {$asignacionesEliminadas} asignaciones.");
            } else {
                return redirect()->back()
                    ->with('error', 'No se encontraron asignaciones de horario para eliminar.');
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el horario: ' . $e->getMessage());
        }
    }
}
