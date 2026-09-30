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
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{

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
                ->with(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque']);

            // Aplicar filtros
            if ($request->filled('grado_id')) {
                $query->whereHas('seccion', function($q) use ($request) {
                    $q->where('grado_id', $request->grado_id);
                });
            }

            if ($request->filled('seccion_id')) {
                $query->where('seccion_id', $request->seccion_id);
            }

            if ($request->filled('area_formacion_id')) {
                $query->whereHas('materia', function($q) use ($request) {
                    $q->where('area_formacion_id', $request->area_formacion_id);
                });
            }

            $asignaciones = $query->get();

            //agrupa asignaciones por grado y sección
            $horariosAsignados = $asignaciones->groupBy(function($asignacion) {
                return $asignacion->seccion->grado->numero_grado . '-' . $asignacion->seccion->nombre;
            })->map(function($asignacionesGrupo, $key) {
                $primeraAsignacion = $asignacionesGrupo->first();
                $grado = $primeraAsignacion->seccion->grado;
                $seccion = $primeraAsignacion->seccion;
                $areasUnicas = $asignacionesGrupo->pluck('materia.nombre_area_formacion')->unique()->values();

                return [
                    'id' => $key,
                    'grado' => $grado->numero_grado,
                    'grado_id' => $grado->id,
                    'seccion' => $seccion->nombre,
                    'seccion_id' => $seccion->id,
                    'areas' => $areasUnicas->implode(', '),
                    'total_asignaciones' => $asignacionesGrupo->count(),
                    'asignaciones' => $asignacionesGrupo
                ];
            })->values();
        }

        // Paginar los resultados
        $horariosPaginados = $this->paginateCollection($horariosAsignados, 10);

        return view('admin.horario.index', compact('grados', 'areasFormacion', 'secciones', 'bloquesHorarios', 'horariosPaginados'));
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

        //filtra aulas
        $aulasNoRegulares = Aula::where('status', true)
            ->where('tipo_aula', '!=', 'regular')
            ->orderBy('nombre_aula', 'asc')
            ->get();

        $aulasRegularesAsignadas = DB::table('aulas as a')
            ->join('seccion_aula as sa', 'a.id_aula', '=', 'sa.id_aula')
            ->join('seccions as s', 'sa.id_seccion', '=', 's.id')
            ->where('a.status', true)
            ->where('a.tipo_aula', '=', 'regular')
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
                    ->where('dag.docente_estudio_realizado_id', function($query) use ($docente) {
                        $query->select('dde.id')
                            ->from('detalle_docente_estudios as dde')
                            ->where('dde.docente_id', $docente->id)
                            ->where('dde.status', true);
                    })
                    ->where('dag.status', true)
                    ->where('s.status', true)
                    ->select('s.id', 's.nombre')
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

        $anioEscolarActivo = AnioEscolar::activos()->first();

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
                ->whereHas('seccion', function($query) use ($grado_id) {
                    $query->where('grado_id', $grado_id);
                })
                ->where('seccion_id', $seccion_id)
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
