<?php

namespace App\Http\Controllers;

use App\Enums\Dia;
use App\Support\AnioEscolarSeleccionado;
use App\Models\AreaFormacion;
use App\Models\AreaFormacionPosicion;
use App\Models\Grado;
use App\Services\ConfiguracionHorarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosicionMateriaController extends Controller
{
    public function __construct(
        protected ConfiguracionHorarioService $configuracionService
    ) {}

    /**
     * Muestra la vista unificada de configuración posicionada en la pestaña de Posiciones por Materia.
     */
    public function index(Request $request): View
    {
        $data = app(ConfiguracionHorarioController::class)->buildUnifiedViewData($request, 'posiciones');

        return view('admin.horario.configuracion', $data);
    }

    /**
     * Devuelve las posiciones configuradas para un área y grado en formato JSON.
     */
    public function consultar(Request $request): JsonResponse
    {
        $request->validate([
            'area_formacion_id' => 'required|integer|exists:area_formacions,id',
            'grado_id' => 'nullable|integer',
        ]);

        $areaId = (int) $request->area_formacion_id;
        $gradoId = $request->filled('grado_id') && $request->grado_id != '0'
            ? (int) $request->grado_id
            : null;

        $posiciones = AreaFormacionPosicion::where('area_formacion_id', $areaId)
            ->where(function ($q) use ($gradoId) {
                if ($gradoId !== null) {
                    $q->where('grado_id', $gradoId);
                } else {
                    $q->whereNull('grado_id');
                }
            })
            ->get()
            ->map(function ($p) {
                $diaVal = $p->dia instanceof Dia ? $p->dia->value : (int) $p->dia;
                return [
                    'dia' => $diaVal,
                    'bloque' => (int) $p->bloque,
                    'tipo' => $p->tipo,
                ];
            });

        return response()->json([
            'success' => true,
            'posiciones' => $posiciones,
        ]);
    }

    /**
     * Guarda las reglas de posiciones para un área y grado.
     */
    public function guardar(Request $request): JsonResponse
    {
        $request->validate([
            'area_formacion_id' => 'required|integer|exists:area_formacions,id',
            'grado_id' => 'nullable|integer',
            'posiciones' => 'array',
            'posiciones.*.dia' => 'required|integer|min:1|max:7',
            'posiciones.*.bloque' => 'required|integer|min:1|max:20',
            'posiciones.*.tipo' => 'required|string|in:disponible,no_preferente,no_preferente_fuerte,prohibido',
        ]);

        $areaId = (int) $request->area_formacion_id;
        $gradoId = $request->filled('grado_id') && $request->grado_id != '0'
            ? (int) $request->grado_id
            : null;

        $items = $request->input('posiciones', []);

        DB::transaction(function () use ($areaId, $gradoId, $items) {
            // Eliminar posiciones existentes para este ámbito
            AreaFormacionPosicion::where('area_formacion_id', $areaId)
                ->where(function ($q) use ($gradoId) {
                    if ($gradoId !== null) {
                        $q->where('grado_id', $gradoId);
                    } else {
                        $q->whereNull('grado_id');
                    }
                })
                ->delete();

            // Insertar nuevas posiciones no disponibles
            foreach ($items as $item) {
                $tipo = $item['tipo'] ?? 'disponible';
                if ($tipo === 'disponible') {
                    continue;
                }

                AreaFormacionPosicion::create([
                    'area_formacion_id' => $areaId,
                    'grado_id' => $gradoId,
                    'dia' => (int) $item['dia'],
                    'bloque' => (int) $item['bloque'],
                    'tipo' => $tipo,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'mensaje' => 'Reglas de posicionamiento pedagógico guardadas exitosamente.',
        ]);
    }
}
