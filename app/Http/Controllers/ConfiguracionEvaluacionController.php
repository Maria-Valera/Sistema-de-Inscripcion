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

                // Solo se crea configuración para el año activo, y solo si todavía no tiene una
        // (normalmente ya la creó el asistente de año nuevo).
        $anioActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();
        $puedeCrear = $anioActivo
            && ! ConfiguracionEvaluacion::where('anio_escolar_id', $anioActivo->id)->exists();

        return view('admin.configuracion_evaluacion.index', compact('configuraciones', 'anioActivo', 'puedeCrear'));
    }

    public function store(ConfiguracionEvaluacionRequest $request): RedirectResponse
    {
        ConfiguracionEvaluacion::create($request->validated());

        return redirect()
            ->route('admin.configuracion_evaluacion.index')
            ->with('success', 'Configuración de evaluación creada correctamente.');
    }

        /**
     * Solo la configuración del año activo se edita o elimina. La de un año anterior queda como se usó:
     * sus notas se calcularon con esas reglas. Devuelve el motivo si no se puede, o null si se puede.
     */
    private function motivoSoloConsulta(ConfiguracionEvaluacion $configuracion): ?string
    {
        $anio = $configuracion->anioEscolar;

        if ($anio && in_array($anio->status, ['Activo', 'Extendido'], true)) {
            return null;
        }

        $nombre = $anio ? $anio->inicio_anio_escolar->format('Y') . ' - ' . $anio->cierre_anio_escolar->format('Y') : '';

        return "La configuración del año {$nombre} es de un año anterior: solo se puede consultar.";
    }

    public function update(ConfiguracionEvaluacionRequest $request, ConfiguracionEvaluacion $configuracionEvaluacion): RedirectResponse
    {
                if ($motivo = $this->motivoSoloConsulta($configuracionEvaluacion)) {
            return redirect()->route('admin.configuracion_evaluacion.index')->with('error', $motivo);
        }

        $datos = $request->validated();

                // El máximo no puede quedar por debajo de los lapsos que el año ya tiene.
        $lapsosCreados = Lapso::where('anio_escolar_id', $configuracionEvaluacion->anio_escolar_id)->count();
        if ($datos['max_lapsos'] < $lapsosCreados) {
            return back()
                ->withInput()
                ->with('error', "Ese año ya tiene {$lapsosCreados} lapsos creados: el máximo de lapsos no puede ser menor.");
        }

        $configuracionEvaluacion->update($datos);

        return redirect()
            ->route('admin.configuracion_evaluacion.index')
            ->with('success', 'Configuración de evaluación actualizada correctamente.');
    }

    public function destroy(ConfiguracionEvaluacion $configuracionEvaluacion): RedirectResponse
    {

            if ($motivo = $this->motivoSoloConsulta($configuracionEvaluacion)) {
            return redirect()->route('admin.configuracion_evaluacion.index')->with('error', $motivo);
        }
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
