<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\AreaFormacion;
use App\Models\Seccion;
use App\Models\Docente;
use App\Models\HorarioAsignacion;
use App\Models\Aula;
use App\Support\AnioEscolarSeleccionado;
use App\Models\ConfiguracionHorario;
use App\Models\BloqueReservado;
use App\Models\CargaAcademica;
use App\Enums\Dia;
use App\Services\ValidadorHorarioService;
use App\Services\ConfiguracionHorarioService;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    protected ValidadorHorarioService $validador;
    protected ConfiguracionHorarioService $configuracionHorarioService;

    public function __construct(ValidadorHorarioService $validador, ConfiguracionHorarioService $configuracionHorarioService)
    {
        $this->validador = $validador;
        $this->configuracionHorarioService = $configuracionHorarioService;
    }

    //muestra horarios en formato API
    public function IndexApi()
    {
        $anio = AnioEscolarSeleccionado::modelo();
        if (!$anio) {
            return response()->json([]);
        }

        $asignaciones = HorarioAsignacion::where('anio_escolar_id', $anio->id)
            ->with([
                'cargaAcademica.docente.persona',
                'cargaAcademica.areaFormacion',
                'cargaAcademica.seccion.grado',
                'aula'
            ])
            ->get();

        $resultado = $asignaciones->map(function ($a) {
            $diaVal = $a->dia instanceof Dia ? $a->dia->value : (int) $a->dia;
            $diaEnum = Dia::tryFrom($diaVal);
            $docente = $a->cargaAcademica?->docente;
            $materia = $a->cargaAcademica?->areaFormacion;
            $seccion = $a->cargaAcademica?->seccion;

            return [
                'id' => $a->id,
                'dia' => [
                    'id' => $diaVal,
                    'nombre_dia' => $diaEnum?->label() ?? ('Día ' . $diaVal),
                ],
                'bloque' => [
                    'id' => (int) $a->bloque,
                    'bloque' => (int) $a->bloque,
                ],
                'docente' => $docente ? [
                    'id' => $docente->id,
                    'nombre' => $docente->nombre_completo
                ] : null,
                'materia' => $materia ? [
                    'id' => $materia->id,
                    'nombre' => $materia->nombre_area_formacion,
                    'tipo_aula_requerida' => $materia->tipo_aula_requerida ?? 'normal'
                ] : null,
                'aula' => $a->aula,
                'seccion' => $seccion,
                'seccion_id' => $seccion?->id,
                'seccion_nombre' => $seccion?->nombre ?? 'Sin Sección',
                'grado' => $seccion?->grado,
                'cargaAcademica' => [
                    'id' => $a->carga_academica_id,
                    'docente' => $docente ? [
                        'id' => $docente->id,
                        'nombre' => $docente->nombre_completo
                    ] : null,
                    'areaFormacion' => $materia ? [
                        'id' => $materia->id,
                        'nombre' => $materia->nombre_area_formacion
                    ] : null,
                    'materia' => $materia ? [
                        'id' => $materia->id,
                        'nombre' => $materia->nombre_area_formacion
                    ] : null,
                    'seccion' => $seccion ? [
                        'id' => $seccion->id,
                        'nombre' => $seccion->nombre
                    ] : null,
                ],
            ];
        });

        return response()->json($resultado);
    }

    //devuelve todas las asignaciones (HorarioAsignacion) de una sección para el modal "Ver Horario"
    public function showApi($grado_id, $seccion_id)
    {
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

        if (!$anioEscolarActivo) {
            return response()->json(['message' => 'No hay año escolar seleccionado.'], 404);
        }

        $seccion = Seccion::with('grado')
            ->where('id', $seccion_id)
            ->where('grado_id', $grado_id)
            ->first();

        if (!$seccion) {
            return response()->json(['message' => 'La sección no pertenece al grado indicado.'], 404);
        }

        $config = ConfiguracionHorario::where('anio_escolar_id', $anioEscolarActivo->id)->first();
        $numeroBloques = $config ? (int) $config->numero_bloques : 8;
        $bloquesCalculados = $config ? $this->configuracionHorarioService->bloques($config) : collect();

        $asignaciones = HorarioAsignacion::where('anio_escolar_id', $anioEscolarActivo->id)
            ->whereHas('cargaAcademica', function ($q) use ($seccion, $grado_id) {
                $q->where('seccion_id', $seccion->id);
            })
            ->with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'aula'])
            ->orderBy('dia')
            ->orderBy('bloque')
            ->get()
            ->map(function ($a) use ($seccion) {
                $carga = $a->cargaAcademica;
                $docente = $carga?->docente;
                $persona = $docente?->persona;
                $materia = $carga?->areaFormacion;
                $diaVal = $a->dia instanceof Dia ? $a->dia->value : (int) $a->dia;
                $bloqueVal = (int) $a->bloque;
                $diaEnum = Dia::tryFrom($diaVal);
                $docenteNombre = $persona ? trim($persona->primer_nombre . ' ' . $persona->primer_apellido) : null;
                $materiaNombre = $materia?->nombre_area_formacion;
                $aulaNombre = $a->aula?->nombre_aula;

                return [
                    'id' => $a->id,
                    'carga_academica_id' => $a->carga_academica_id,
                    'dia' => $diaVal,
                    'dia_nombre' => $diaEnum?->label() ?? ('Día ' . $diaVal),
                    'bloque' => $bloqueVal,
                    'fijada' => (bool) $a->fijada,
                    'docente_id' => $docente?->id,
                    'docente' => $docenteNombre,
                    'docente_nombre' => $docenteNombre,
                    'materia_id' => $materia?->id,
                    'materia' => $materiaNombre,
                    'materia_nombre' => $materiaNombre,
                    'seccion_id' => $seccion->id,
                    'seccion_nombre' => $seccion->nombre,
                    'aula_id' => $a->aula_id,
                    'aula' => $aulaNombre,
                    'aula_nombre' => $aulaNombre,
                    'conflicto_manual' => (bool) $a->conflicto_manual,
                    'cargaAcademica' => [
                        'id' => $carga?->id,
                        'docente_id' => $docente?->id,
                        'docente' => $docente ? [
                            'id' => $docente->id,
                            'nombre' => $docenteNombre,
                        ] : null,
                        'materia' => $materia ? [
                            'id' => $materia->id,
                            'nombre' => $materiaNombre,
                        ] : null,
                        'areaFormacion' => $materia ? [
                            'id' => $materia->id,
                            'nombre' => $materiaNombre,
                        ] : null,
                        'seccion' => [
                            'id' => $seccion->id,
                            'nombre' => $seccion->nombre,
                        ],
                    ],
                ];
            })
            ->values();

        // Consultar docentes asignados a la sección mediante CargaAcademica
        $cargasSeccion = CargaAcademica::where('anio_escolar_id', $anioEscolarActivo->id)
            ->where('seccion_id', $seccion->id)
            ->with(['docente.persona', 'areaFormacion'])
            ->get();

        $docentesMap = [];
        foreach ($cargasSeccion as $carga) {
            $docente = $carga->docente;
            if (!$docente) continue;
            $docId = $docente->id;
            $persona = $docente->persona;
            $nombre = $persona ? trim($persona->primer_nombre . ' ' . $persona->primer_apellido) : ('Docente #' . $docId);
            $materiaNombre = $carga->areaFormacion?->nombre_area_formacion;

            if (!isset($docentesMap[$docId])) {
                $docentesMap[$docId] = [
                    'id' => $docId,
                    'nombre' => $nombre,
                    'materias' => [],
                ];
            }
            if ($materiaNombre && !in_array($materiaNombre, $docentesMap[$docId]['materias'], true)) {
                $docentesMap[$docId]['materias'][] = $materiaNombre;
            }
        }
        $docentes = array_values($docentesMap);

        $dias = collect(Dia::cases())->map(fn(Dia $d) => [
            'id' => $d->value,
            'dia' => $d->value,
            'nombre_dia' => $d->label(),
            'nombre' => $d->label(),
        ])->values();

        $bloques = [];
        for ($b = 1; $b <= $numeroBloques; $b++) {
            $info = $bloquesCalculados->get($b, ['inicio' => '07:00', 'fin' => '07:45']);
            $bloques[] = [
                'id' => $b,
                'bloque' => $b,
                'hora_inicio' => $info['inicio'],
                'hora_fin' => $info['fin'],
            ];
        }

        $reservados = BloqueReservado::where('anio_escolar_id', $anioEscolarActivo->id)
            ->get(['dia', 'bloque'])
            ->map(fn($r) => [
                'dia' => $r->dia instanceof Dia ? $r->dia->value : (is_object($r->dia) && isset($r->dia->value) ? $r->dia->value : (int) $r->dia),
                'bloque' => (int) $r->bloque,
                'motivo' => 'Reservado',
            ])
            ->values();

        return response()->json([
            'grado' => [
                'id' => $seccion->grado?->id,
                'numero_grado' => $seccion->grado?->numero_grado,
                'nombre' => $seccion->grado ? ($seccion->grado->numero_grado . '° Año') : null,
            ],
            'seccion' => [
                'id' => $seccion->id,
                'nombre' => $seccion->nombre,
            ],
            'anio_escolar' => $anioEscolarActivo->nombre_periodo ?? ($anioEscolarActivo->inicio_anio . '-' . $anioEscolarActivo->fin_anio),
            'anio_escolar_id' => $anioEscolarActivo->id,
            'numero_bloques' => $numeroBloques,
            'dias' => $dias,
            'bloques' => $bloques,
            'reservados' => $reservados,
            'docentes' => $docentes,
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

        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

        $numeroBloques = 8;
        if ($anioEscolarActivo) {
            $config = ConfiguracionHorario::where('anio_escolar_id', $anioEscolarActivo->id)->first();
            if ($config) {
                $numeroBloques = $config->numero_bloques;
            }
        }
        $bloquesHorarios = collect(range(1, $numeroBloques))->map(fn($b) => (object)[
            'id' => $b,
            'bloque' => $b,
            'nombre' => "Bloque {$b}",
        ]);

        $horariosAsignados = collect();
        if ($anioEscolarActivo) {
            $query = HorarioAsignacion::where('anio_escolar_id', $anioEscolarActivo->id)
                ->with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula']);

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
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

        if (!$anioEscolarActivo) {
            return redirect()->route('admin.horario.index')
                ->with('error', 'No hay un año escolar seleccionado.');
        }

        if (!AnioEscolarSeleccionado::esActual()) {
            return redirect()->route('admin.horario.index')
                ->with('warning', 'No puedes crear horarios en un año escolar anterior (solo lectura).');
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

        $todasLasSecciones = Seccion::where('status', true)
            ->orderBy('nombre', 'asc')
            ->get(['id', 'nombre', 'grado_id']);

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

        //carga docentes activos con sus areas y secciones (priorizando cargasAcademicas)
        $docentesCollection = Docente::with(['persona', 'cargasAcademicas.areaFormacion', 'cargasAcademicas.seccion'])
            ->where('status', true)
            ->whereHas('persona', function($query) {
                $query->where('status', true);
            })
            ->get();

        $docentes = $docentesCollection->map(function($docente) {
            $cargas = $docente->cargasAcademicas;

            if ($cargas && $cargas->isNotEmpty()) {
                $areas = $cargas->pluck('areaFormacion.nombre_area_formacion')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $secciones = $cargas->map(function($c) {
                    return $c->seccion ? [
                        'id' => $c->seccion->id,
                        'nombre' => $c->seccion->nombre,
                    ] : null;
                })->filter()->unique('id')->values()->all();
            } else {
                // Fallback histórico para registros sin cargas académicas migradas
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
            }

            $rawNombre = preg_replace('/\s+/', ' ', trim($docente->nombre_completo));
            if (empty($rawNombre) && $docente->persona) {
                $rawNombre = preg_replace('/\s+/', ' ', trim(
                    ($docente->persona->primer_nombre ?? '') . ' ' . ($docente->persona->primer_apellido ?? '')
                ));
            }

            return [
                'id' => $docente->id,
                'nombre' => $rawNombre,
                'nombre_completo' => $rawNombre,
                'area' => !empty($areas) ? implode(', ', $areas) : 'Sin área asignada',
                'areas' => $areas,
                'secciones' => $secciones,
                'horas_academicas' => (int) ($docente->horas_academicas ?? 0),
                'cedula' => $docente->persona?->numero_documento,
            ];
        });

        //detecta modo de edición desde parametros
        $modoEdicion = $request->has('modo_edicion') && $request->modo_edicion === 'true';
        $editGradoId = $request->input('grado_id');
        $editSeccionId = $request->input('seccion_id');

        $numeroBloques = $configuracion ? (int) $configuracion->numero_bloques : 8;
        $bloquesCalculados = $configuracion ? $this->configuracionHorarioService->bloques($configuracion) : collect();
        $diasCases = Dia::cases();
        $bloquesReservados = BloqueReservado::where('anio_escolar_id', $anioEscolarActivo->id)->get(['dia', 'bloque']);

        return view('admin.horario.create', compact(
            'grados', 
            'areasFormacion', 
            'secciones', 
            'docentes', 
            'aulas', 
            'anioEscolarActivo',
            'modoEdicion',
            'editGradoId',
            'editSeccionId',
            'configuracion',
            'numeroBloques',
            'bloquesCalculados',
            'diasCases',
            'bloquesReservados',
            'todasLasSecciones'
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
            $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

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
