<?php

namespace App\Http\Controllers;

use App\Enums\Dia;
use App\Http\Requests\ConfiguracionHorarioRequest;
use App\Models\AreaFormacion;
use App\Models\AreaFormacionPosicion;
use App\Models\BloqueReservado;
use App\Models\ConfiguracionHorario;
use App\Models\Grado;
use App\Services\ConfiguracionHorarioService;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfiguracionHorarioController extends Controller
{
    public function __construct(
        protected ConfiguracionHorarioService $configuracionService
    ) {}

    /**
     * Muestra la vista unificada de configuración de horarios (Bloques/Recesos + Posiciones por Materia).
     */
    public function index(Request $request): View
    {
        $requestedTab = $request->query('tab', 'bloques');
        $activeTab = in_array($requestedTab, ['bloques', 'posiciones'], true) ? $requestedTab : 'bloques';

        return view('admin.horario.configuracion', $this->buildUnifiedViewData($request, $activeTab));
    }

    /**
     * Construye los datos compartidos para la vista unificada con pestañas.
     */
    public function buildUnifiedViewData(Request $request, string $activeTab = 'bloques'): array
    {
        $anioActivo = AnioEscolarSeleccionado::modelo();
        $configuracion = $this->configuracionService->actual();
        $bloques = $this->configuracionService->bloques($configuracion);
        $maxBloqueOcupado = $this->configuracionService->maximoBloqueOcupado($anioActivo?->id);

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

        $reservados = $anioActivo
            ? BloqueReservado::where('anio_escolar_id', $anioActivo->id)
                ->orderBy('dia')
                ->orderBy('bloque')
                ->get()
            : collect();

        $reservadosArray = $reservados->map(function ($r) {
            $bloqueVal = (int) $r->bloque;
            return [
                'dia' => $r->dia instanceof Dia ? $r->dia->value : (int) $r->dia,
                'desde' => $bloqueVal,
                'hasta' => $bloqueVal,
                'bloque' => $bloqueVal,
            ];
        })->values()->all();

        $dias = Dia::cases();

        // Datos para la pestaña de Posiciones Pedagógicas por Materia
        $areas = AreaFormacion::where('status', true)
            ->orderBy('nombre_area_formacion', 'asc')
            ->get();

        $grados = Grado::where('status', true)
            ->orderBy('numero_grado', 'asc')
            ->get();

        $areaId = $request->query('area_id', $areas->first()?->id);
        $gradoId = $request->query('grado_id');
        if ($gradoId === '' || $gradoId === '0') {
            $gradoId = null;
        }

        $posicionesMap = [];
        if ($areaId) {
            $posiciones = AreaFormacionPosicion::where('area_formacion_id', $areaId)
                ->where(function ($q) use ($gradoId) {
                    if ($gradoId !== null) {
                        $q->where('grado_id', $gradoId);
                    } else {
                        $q->whereNull('grado_id');
                    }
                })
                ->get();

            foreach ($posiciones as $pos) {
                $diaVal = $pos->dia instanceof Dia ? $pos->dia->value : (int) $pos->dia;
                $posicionesMap[$diaVal . '_' . $pos->bloque] = $pos->tipo;
            }
        }

        return compact(
            'anioActivo',
            'configuracion',
            'bloques',
            'maxBloqueOcupado',
            'recesosArray',
            'numeroBloques',
            'minutosLectivos',
            'minutosReceso',
            'reservados',
            'reservadosArray',
            'dias',
            'areas',
            'grados',
            'areaId',
            'gradoId',
            'posicionesMap',
            'activeTab'
        );
    }

    /**
     * Guarda y actualiza la configuración global de horario, recesos y bloques reservados.
     */
    public function update(ConfiguracionHorarioRequest $request): RedirectResponse
    {
        try {
            $anioActivo = AnioEscolarSeleccionado::modelo();
            $this->configuracionService->guardar($request->all(), $anioActivo?->id);

            return redirect()->route('admin.horario.configuracion')
                ->with('success', 'Configuración de horario, recesos y bloques reservados actualizada correctamente.');
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
