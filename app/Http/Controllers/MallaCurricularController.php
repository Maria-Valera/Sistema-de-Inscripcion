<?php

namespace App\Http\Controllers;

use App\Http\Requests\MallaCurricularRequest;
use App\Models\AreaFormacion;
use App\Models\Grado;
use App\Models\GradoAreaFormacion;
use App\Models\Pensum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MallaCurricularController extends Controller
{
    /**
     * Muestra el listado de la malla curricular filtrado por pensum y grado.
     */
    public function index(Request $request): View
    {
        $pensums = Pensum::orderBy('codigo', 'asc')->get();
        $grados = Grado::orderBy('numero_grado', 'asc')->get();
        $areas = AreaFormacion::where('status', true)->orderBy('nombre_area_formacion', 'asc')->get();

        $pensumId = $request->input('pensum_id', $pensums->first()?->id);
        $gradoId = $request->input('grado_id');
        $busqueda = trim((string) $request->input('buscar'));

        $query = GradoAreaFormacion::with(['pensum', 'grado', 'area_formacion']);

        if (!empty($pensumId)) {
            $query->where('pensum_id', $pensumId);
        }

        if (!empty($gradoId)) {
            $query->where('grado_id', $gradoId);
        }

        if (!empty($busqueda)) {
            $query->whereHas('area_formacion', function ($q) use ($busqueda) {
                $q->where('nombre_area_formacion', 'like', "%{$busqueda}%")
                  ->orWhere('siglas', 'like', "%{$busqueda}%");
            });
        }

        // Organizado prioritariamente por año escolar (grado) y luego por secuencia oficial
        $mallas = $query->orderBy('grado_id', 'asc')
                        ->orderBy('orden', 'asc')
                        ->orderBy('id', 'asc')
                        ->paginate(50)
                        ->withQueryString();

        $pensumSeleccionado = $pensums->firstWhere('id', $pensumId);

        return view('admin.maestros.jose.malla_curricular.index', compact(
            'mallas',
            'pensums',
            'grados',
            'areas',
            'pensumId',
            'gradoId',
            'busqueda',
            'pensumSeleccionado'
        ));
    }

    /**
     * Registra una nueva asignatura en la malla curricular.
     */
    public function store(MallaCurricularRequest $request): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        GradoAreaFormacion::create($datos);

        return redirect()->route('admin.malla-curricular.index', [
            'pensum_id' => $datos['pensum_id'],
            'grado_id'  => $datos['grado_id'],
        ])->with('success', 'Asignatura agregada a la malla curricular exitosamente.');
    }

    /**
     * Actualiza la asignatura en la malla curricular.
     */
    public function update(MallaCurricularRequest $request, GradoAreaFormacion $mallaCurricular): RedirectResponse
    {
        $datos = $request->validated();
        $datos['status'] = $request->boolean('status', true);

        $mallaCurricular->update($datos);

        return redirect()->route('admin.malla-curricular.index', [
            'pensum_id' => $datos['pensum_id'],
            'grado_id'  => $datos['grado_id'],
        ])->with('success', 'Malla curricular actualizada exitosamente.');
    }

    /**
     * Elimina o desactiva la asignatura de la malla.
     */
    public function destroy(GradoAreaFormacion $mallaCurricular): RedirectResponse
    {
        $pensumId = $mallaCurricular->pensum_id;
        $gradoId = $mallaCurricular->grado_id;

        // Regla de integridad: No borrar si está en uso por estudiantes (materias de prosecución o historial)
        $enUsoProsecucion = DB::table('prosecucion_areas')->where('grado_area_formacion_id', $mallaCurricular->id)->exists();

        if ($enUsoProsecucion) {
            $mallaCurricular->update(['status' => false]);

            return redirect()->route('admin.malla-curricular.index', [
                'pensum_id' => $pensumId,
                'grado_id'  => $gradoId,
            ])->with('warning', 'La asignatura está asociada a materias de prosecución de estudiantes. Ha sido desactivada para proteger el historial académico.');
        }

        $mallaCurricular->delete();

        return redirect()->route('admin.malla-curricular.index', [
            'pensum_id' => $pensumId,
            'grado_id'  => $gradoId,
        ])->with('success', 'Asignatura removida de la malla curricular.');
    }
}

