<?php

namespace App\Http\Controllers;

use App\Enums\EstadoLapso;
use App\Http\Requests\LapsoRequest;
use App\Models\CalendarioAcademico;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Lapso;
use App\Support\AnioEscolarSeleccionado;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

// M4. Lapsos del año escolar seleccionado (los cortes de cada lapso están en CorteController).
class LapsoController extends Controller
{
    public function index(): View
    {
        $anio = AnioEscolarSeleccionado::modelo();
        $config = $anio ? ConfiguracionEvaluacion::where('anio_escolar_id', $anio->id)->first() : null;

        $lapsos = $anio
            ? Lapso::where('anio_escolar_id', $anio->id)
                ->withCount('cortes')
                ->withSum('cortes', 'peso_lapso')
                ->withMin('cortes', 'fecha_inicio')
                ->withMax('cortes', 'fecha_fin')
                ->orderBy('numero')
                ->get()
            : collect();

        // Se puede crear otro lapso si hay configuración y todavía no se llegó a num_lapsos.
        $puedeCrear = $config && $lapsos->count() < $config->num_lapsos;

        // El primer número libre, para proponerlo en el modal de crear.
        $siguienteNumero = $config
            ? collect(range(1, $config->num_lapsos))->diff($lapsos->pluck('numero'))->first()
            : null;

        return view('admin.lapsos.index', compact('anio', 'config', 'lapsos', 'puedeCrear', 'siguienteNumero'));
    }

    public function store(LapsoRequest $request): RedirectResponse
    {
        $anio = $request->anioEscolar();
        $datos = $this->prepararDatos($request->validated(), null);
        $datos['anio_escolar_id'] = $anio->id;
        $datos['calendario_academico_id'] = CalendarioAcademico::where('anio_escolar_id', $anio->id)->value('id');

        Lapso::create($datos);

        return redirect()->route('admin.lapsos.index')->with('success', 'Lapso creado correctamente.');
    }

    public function update(LapsoRequest $request, Lapso $lapso): RedirectResponse
    {
        $lapso->update($this->prepararDatos($request->validated(), $lapso));

        return redirect()->route('admin.lapsos.index')->with('success', 'Lapso actualizado correctamente.');
    }

    public function destroy(Lapso $lapso): RedirectResponse
    {
        // Solo se borra un lapso que todavía no empezó: abierto o cerrado ya puede tener notas.
        if ($lapso->estado !== EstadoLapso::Planificado) {
            return redirect()
                ->route('admin.lapsos.index')
                ->with('error', "No se puede eliminar {$lapso->nombre}: solo se eliminan lapsos en estado Planificado.");
        }

        $totalCortes = $lapso->cortes()->count();
        $lapso->delete(); // sus cortes se borran en cascada

        return redirect()
            ->route('admin.lapsos.index')
            ->with('success', "Lapso eliminado junto con sus {$totalCortes} cortes.");
    }

    /** La fecha límite es hasta el final del día, y el cierre guarda quién y cuándo. */
    private function prepararDatos(array $datos, ?Lapso $lapso): array
    {
        $datos['fecha_limite_carga'] = Carbon::parse($datos['fecha_limite_carga'])->endOfDay();

        if ($datos['estado'] === EstadoLapso::Cerrado->value) {
            $datos['cerrado_por'] = $lapso?->cerrado_por ?? auth()->id();
            $datos['cerrado_at'] = $lapso?->cerrado_at ?? now();
        } else {
            $datos['cerrado_por'] = null;
            $datos['cerrado_at'] = null;
        }

        return $datos;
    }
}
