<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Grado;
use App\Models\AreaFormacion;
use App\Models\Seccion;
use App\Models\Docente;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
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

        return view('admin.horario.create', compact('grados', 'areasFormacion', 'secciones', 'docentes'));
    }
}
