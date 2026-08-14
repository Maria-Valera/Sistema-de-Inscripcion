<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\AreaFormacion;
use App\Models\Seccion;
use App\Models\Docente;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\AnioEscolar;
use App\Models\BloqueHorario;
use App\Models\Dias_semana;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{

    public function IndexApi()
{
    $horarios = Horario::where('status', true)
        ->with([
            'aula',
            'bloqueHorarios',   // se queda igual, nombre plural, sin tocar el modelo
            'diasSemana',       // se queda igual
            'docentesAreaFormacion.docenteAreaGrado.detalleDocenteEstudio.docente.persona',
            'docentesAreaFormacion.docenteAreaGrado.areaEstudios.areaFormacion',
            'secciones.aulaFija',
            'secciones.grado'
        ])
        ->get();

    $resultado = $horarios->map(function ($horario) {
        // Tomamos el PRIMERO de cada relación, ya que en la práctica
        // cada horario solo debería tener uno de cada
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

        return view('admin.horario.index', compact('grados', 'areasFormacion', 'secciones', 'bloquesHorarios'));
    }
/*
    public function apiIndex(
       $resultado=Horario::where('status', true)->get();

       return response()->json($resultado);
    )*/

    public function create()
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

        // Filtrar aulas: mostrar todas las no regulares + las regulares asignadas a secciones
        $aulasNoRegulares = Aula::where('status', true)
            ->where('tipo_aula', '!=', 'regular')
            ->orderBy('nombre_aula', 'asc')
            ->get();

        // Obtener aulas regulares que están asignadas a alguna sección
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

        // Combinar ambas colecciones
        $aulas = $aulasNoRegulares->concat($aulasRegularesAsignadas);

        // Cargar docentes activos con sus áreas de formación
        $docentes = Docente::with(['persona', 'detalleDocenteEstudio.estudiosRealizado'])
            ->where('status', true)
            ->whereHas('detalleDocenteEstudio', function($query) {
                $query->where('status', true);
            })
            ->get()
            ->map(function($docente) {
                // Obtener áreas de formación del docente
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

                return [
                    'id' => $docente->id,
                    'nombre' => $docente->nombre_completo,
                    'area' => !empty($areas) ? implode(', ', $areas) : 'Sin área asignada',
                    'areas' => $areas
                ];
            });

        return view('admin.horario.create', compact('grados', 'areasFormacion', 'secciones', 'docentes', 'aulas'));
    }
}
