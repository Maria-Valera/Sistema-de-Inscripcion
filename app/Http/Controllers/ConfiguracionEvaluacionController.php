<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfiguracionEvaluacionRequest;
use App\Models\AnioEscolar;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Lapso;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// M3. Configuración de evaluación: una por año escolar.
class ConfiguracionEvaluacionController extends Controller
{
    public function index(): View
    {
        $configuraciones = ConfiguracionEvaluacion::with('anioEscolar')
            ->join('anio_escolars', 'anio_escolars.id', '=', 'configuracion_evaluacions.anio_escolar_id')
            ->orderByDesc('anio_escolars.inicio_anio_escolar')
            ->select('configuracion_evaluacions.*')
            ->paginate(10);

        // Años que todavía no tienen configuración: los usa el modal de crear.
        // (Sin relación nueva en AnioEscolar.php, que es un modelo compartido.)
        $aniosSinConfiguracion = AnioEscolar::whereNotIn('id', ConfiguracionEvaluacion::select('anio_escolar_id'))
            ->orderByDesc('inicio_anio_escolar')
            ->get();

        return view('admin.configuracion_evaluacion.index', compact('configuraciones', 'aniosSinConfiguracion'));
    }

    public function store(ConfiguracionEvaluacionRequest $request): RedirectResponse
    {
        ConfiguracionEvaluacion::create($request->validated());

        return redirect()
            ->route('admin.configuracion_evaluacion.index')
            ->with('success', 'Configuración de evaluación creada correctamente.');
    }

    public function update(ConfiguracionEvaluacionRequest $request, ConfiguracionEvaluacion $configuracionEvaluacion): RedirectResponse
    {
        $datos = $request->validated();

        // Si el año ya tiene lapsos, no se puede bajar num_lapsos por debajo de los que existen.
        $lapsosCreados = Lapso::where('anio_escolar_id', $configuracionEvaluacion->anio_escolar_id)->count();
        if ($datos['num_lapsos'] < $lapsosCreados) {
            return back()
                ->withInput()
                ->with('error', "Ese año ya tiene {$lapsosCreados} lapsos creados: el número de lapsos no puede ser menor.");
        }

        $configuracionEvaluacion->update($datos);

        return redirect()
            ->route('admin.configuracion_evaluacion.index')
            ->with('success', 'Configuración de evaluación actualizada correctamente.');
    }

    public function destroy(ConfiguracionEvaluacion $configuracionEvaluacion): RedirectResponse
    {
        // Sin configuración, los lapsos de ese año quedarían sin reglas: se impide borrarla.
        if (Lapso::where('anio_escolar_id', $configuracionEvaluacion->anio_escolar_id)->exists()) {
            return redirect()
                ->route('admin.configuracion_evaluacion.index')
                ->with('error', 'No se puede eliminar: ese año escolar ya tiene lapsos creados.');
        }

        $configuracionEvaluacion->delete();

        return redirect()
            ->route('admin.configuracion_evaluacion.index')
            ->with('success', 'Configuración de evaluación eliminada correctamente.');
    }
}
