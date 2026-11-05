<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\EntradasPercentil;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\Request;
use App\Models\Inscripcion;
use App\Models\Grado;
use App\Services\SectionDistributorService;
use Illuminate\Pagination\LengthAwarePaginator;

class EntradasPercentilController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolarSeleccionado::esActual();
    }

    public function index(Request $request)
    {
        $gradoId = $request->grado_id;

        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();
        $esActual = AnioEscolarSeleccionado::esActual();

        if (!$anioEscolarActivo) {

            $entradasPercentil = new LengthAwarePaginator(
                collect(),
                0,
                10,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return view(
                'admin.transacciones.percentil.index',
                [
                    'entradasPercentil' => $entradasPercentil,
                    'seccionesResumen' => collect(),
                    'gradoId' => $gradoId,
                    'anioEscolarActivo' => null,
                ]
            );
        }

        $entradasPercentil = EntradasPercentil::with(['inscripcion.alumno', 'seccion'])
            ->whereHas('ejecucion', function ($q) use ($anioEscolarActivo) {
                $q->where('anio_escolar_id', $anioEscolarActivo->id);
            })
            ->when($gradoId, function ($q) use ($gradoId) {
                $q->whereHas('inscripcion', fn($qq) => $qq->where('grado_id', $gradoId));
            })
            ->orderBy('seccion_id')
            ->orderBy('indice_total')
            ->paginate(10);

        $seccionesResumen = EntradasPercentil::selectRaw('seccion_id, COUNT(*) as total_estudiantes')
            ->with('seccion')
            ->whereHas('ejecucion', function ($q) use ($anioEscolarActivo) {
                $q->where('anio_escolar_id', $anioEscolarActivo->id);
            })
            ->when($gradoId, function ($q) use ($gradoId) {
                $q->whereHas('inscripcion', fn($qq) => $qq->where('grado_id', $gradoId));
            })
            ->groupBy('seccion_id')
            ->get();

        return view(
            'admin.transacciones.percentil.index',
            [
                'entradasPercentil' => $entradasPercentil,
                'gradoId' => $gradoId,
                'seccionesResumen' => $seccionesResumen,
                'anioEscolarActivo' => (bool) $anioEscolarActivo,
                'anioEscolar' => $anioEscolarActivo,
                'esActual' => $esActual,
            ]
        );
    }



    public function store(Inscripcion $inscripcion)
    {
        $entrada = app(\App\Services\PercentilService::class)
            ->crearEntradaDesdeInscripcion($inscripcion);

        return response()->json($entrada);
    }

    public function ejecutar(Request $request, SectionDistributorService $distributor)
    {
        $request->validate([
            'grado_id' => 'required|exists:grados,id'
        ]);

        if (!$this->verificarAnioEscolar()) {
            return back()->with('error', 'No existe un Calendario Escolar activo.');
        }

        $grado = Grado::findOrFail($request->grado_id);
        $anioEscolarActivo = AnioEscolarSeleccionado::modelo();

        $totalEstudiantes = Inscripcion::where('grado_id', $grado->id)
            ->whereIn('status', ['Activo', 'Pendiente'])
            ->where('anio_escolar_id', $anioEscolarActivo->id)
            ->count();

        if ($totalEstudiantes < $grado->min_seccion) {
            return back()->with(
                'error',
                "No se puede ejecutar el percentil. 
                Mínimo por sección: {$grado->min_seccion}. 
                Estudiantes actuales: {$totalEstudiantes}."
            );
        }

        try {
            $resultado = $distributor->procesarGrado($grado);

            return back()->with(
                'success',
                "Percentil ejecutado correctamente.
                Estudiantes procesados: {$resultado['estudiantes_procesados']},
                Secciones creadas: {$resultado['total_secciones']}"
            );
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}
