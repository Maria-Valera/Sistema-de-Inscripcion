<?php

namespace App\Http\Controllers;

use App\Models\AnioEscolar;
use App\Models\ConfiguracionHorario;
use App\Services\ConfiguracionHorarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionHorarioController extends Controller
{
    public function __construct(
        protected ConfiguracionHorarioService $configuracionService
    ) {}

    /**
     * Muestra la vista de configuración de bloques (marco horario).
     */
    public function index(): View|RedirectResponse
    {
        $anioActivo = AnioEscolar::activos()->orderByDesc('id')->first();

        if (!$anioActivo) {
            return redirect()->route('admin.anio_escolar.index')
                ->with('error', 'Debe existir un año escolar activo para configurar el horario.');
        }

        $configuracion = $this->configuracionService->actual($anioActivo->id);

        if (!$configuracion) {
            $configuracion = new ConfiguracionHorario([
                'anio_escolar_id' => $anioActivo->id,
                'hora_inicio' => '07:00:00',
                'numero_bloques' => 4,
                'duracion_bloque_min' => 90,
                'minutos_entre_bloques' => 0,
            ]);
            $configuracion->setRelation('recesos', collect());
        }

        $bloques = $this->configuracionService->bloques($configuracion);
        $maxBloqueOcupado = $this->configuracionService->maximoBloqueOcupado($anioActivo->id);

        $numeroBloques = (int) ($configuracion->numero_bloques ?? 4);
        $duracionBloqueMin = (int) ($configuracion->duracion_bloque_min ?? 90);
        $minutosLectivos = $numeroBloques * $duracionBloqueMin;
        $minutosReceso = (int) ($configuracion->relationLoaded('recesos') ? $configuracion->recesos->sum('duracion_min') : $configuracion->recesos()->sum('duracion_min'));

        $recesosArray = $configuracion->recesos->map(function ($r) {
            return [
                'despues_de_bloque' => (int) $r->despues_de_bloque,
                'duracion_min' => (int) $r->duracion_min,
            ];
        })->values()->all();

        return view('admin.horario.configuracion', compact(
            'anioActivo',
            'configuracion',
            'bloques',
            'maxBloqueOcupado',
            'recesosArray',
            'numeroBloques',
            'minutosLectivos',
            'minutosReceso'
        ));
    }

    /**
     * Guarda y actualiza la configuración de horario y recesos.
     */
    public function update(Request $request): RedirectResponse
    {
        $anioActivo = AnioEscolar::activos()->orderByDesc('id')->first();

        if (!$anioActivo) {
            return redirect()->back()->with('error', 'No hay un año escolar activo.');
        }

        $request->validate([
            'hora_inicio' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'numero_bloques' => ['required', 'integer', 'min:1', 'max:12'],
            'duracion_bloque_min' => ['required', 'integer', 'min:1', 'max:300'],
            'minutos_entre_bloques' => ['nullable', 'integer', 'min:0', 'max:120'],
            'recesos' => ['nullable', 'array'],
            'recesos.*.despues_de_bloque' => ['nullable', 'integer', 'min:1'],
            'recesos.*.duracion_min' => ['nullable', 'integer', 'min:1', 'max:120'],
        ], [
            'hora_inicio.required' => 'La hora de inicio es obligatoria.',
            'numero_bloques.required' => 'El número de bloques por día es obligatorio.',
            'numero_bloques.min' => 'Debe haber al menos 1 bloque por día.',
            'duracion_bloque_min.required' => 'La duración del bloque es obligatoria.',
            'duracion_bloque_min.min' => 'La duración debe ser de al menos 1 minuto.',
        ]);

        try {
            $this->configuracionService->guardar($anioActivo->id, $request->all());

            return redirect()->route('admin.horario.configuracion')
                ->with('success', 'Configuración de bloques y descansos actualizada correctamente.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error inesperado al guardar la configuración: ' . $e->getMessage());
        }
    }
}
