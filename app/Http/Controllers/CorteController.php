<?php

namespace App\Http\Controllers;

use App\Enums\EstadoCorte;
use App\Http\Requests\CorteRequest;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Corte;
use App\Models\Lapso;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// M5. Cortes de un lapso: cuánto vale cada uno (la suma debe dar la nota máxima) y sus fechas.
class CorteController extends Controller
{
    public function index(Lapso $lapso): View
    {
        $lapso->load('anioEscolar');
        $config = ConfiguracionEvaluacion::where('anio_escolar_id', $lapso->anio_escolar_id)->first();
        $cortes = $lapso->cortes()->get();

        // Total del lapso: cortes × puntos por corte (4 × 20 = 80).
        $totalLapso = $config?->puntosPorLapso() ?? 0;
        $suma = (float) $cortes->sum('peso_lapso');
        $faltan = round($totalLapso - $suma, 2);

        $puedeCrear = $config && $cortes->count() < $config->num_cortes && $faltan > 0;
        $siguienteNumero = $config
            ? collect(range(1, $config->num_cortes))->diff($cortes->pluck('numero'))->first()
            : null;

        // Puntos que se proponen al crear: los puntos por corte de la configuración (20), sin pasar de lo que falta.
        $pesoSugerido = $config ? min((float) $config->puntos_corte, $faltan) : null;

        return view('admin.cortes.index', compact('lapso', 'config', 'cortes', 'totalLapso', 'suma', 'faltan', 'puedeCrear', 'siguienteNumero', 'pesoSugerido'));
    }

    public function store(CorteRequest $request, Lapso $lapso): RedirectResponse
    {
        $datos = $this->prepararDatos($request->validated(), null);
        $lapso->cortes()->create($datos);

        return redirect()->route('admin.cortes.index', $lapso)->with('success', 'Corte creado correctamente.');
    }

    public function update(CorteRequest $request, Lapso $lapso, Corte $corte): RedirectResponse
    {
        $corte->update($this->prepararDatos($request->validated(), $corte));

        return redirect()->route('admin.cortes.index', $lapso)->with('success', 'Corte actualizado correctamente.');
    }

    public function destroy(Lapso $lapso, Corte $corte): RedirectResponse
    {
        if ($corte->estado !== EstadoCorte::Planificado) {
            return redirect()
                ->route('admin.cortes.index', $lapso)
                ->with('error', "No se puede eliminar {$corte->nombre}: solo se eliminan cortes en estado Planificado.");
        }

        $corte->delete();

        return redirect()->route('admin.cortes.index', $lapso)->with('success', 'Corte eliminado correctamente.');
    }

    /** La fecha límite es hasta el final del día; abrir y cerrar guardan quién y cuándo. */
    private function prepararDatos(array $datos, ?Corte $corte): array
    {
        $datos['fecha_limite_carga'] = Carbon::parse($datos['fecha_limite_carga'])->endOfDay();
        $estado = EstadoCorte::from($datos['estado']);

        // Abierto o cerrado: alguien lo abrió. Planificado: se borra el registro de apertura.
        if ($estado === EstadoCorte::Planificado) {
            $datos['abierto_por'] = null;
            $datos['abierto_at'] = null;
        } else {
            $datos['abierto_por'] = $corte?->abierto_por ?? auth()->id();
            $datos['abierto_at'] = $corte?->abierto_at ?? now();
        }

        if ($estado === EstadoCorte::Cerrado) {
            $datos['cerrado_por'] = $corte?->cerrado_por ?? auth()->id();
            $datos['cerrado_at'] = $corte?->cerrado_at ?? now();
        } else {
            $datos['cerrado_por'] = null;
            $datos['cerrado_at'] = null;
        }

        return $datos;
    }
}
