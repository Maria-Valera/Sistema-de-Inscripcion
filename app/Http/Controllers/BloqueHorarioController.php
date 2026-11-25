<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\AnioEscolarSeleccionado;
use App\Models\ConfiguracionHorario;
use App\Services\ConfiguracionHorarioService;

class BloqueHorarioController extends Controller
{
    public function apiIndex()
    {
        $bloquesService = app(ConfiguracionHorarioService::class);
        $config = $bloquesService->actual();
        $calculados = $bloquesService->bloques($config);
        $bloques = [];
        for ($b = 1; $b <= $config->numero_bloques; $b++) {
            $info = $calculados->get($b, ['inicio' => '07:00', 'fin' => '07:45']);
            $bloques[] = [
                'id' => $b,
                'bloque' => $b,
                'hora_inicio' => $info['inicio'],
                'hora_fin' => $info['fin'],
            ];
        }
        return response()->json($bloques);
    }

    public function index()
    {
        return redirect()->route('horario.configuracion');
    }

    public function store(Request $request)
    {
        return redirect()->route('horario.configuracion');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('horario.configuracion');
    }

    public function verificarExistencia(Request $request)
    {
        return response()->json(['success' => true, 'existe' => false]);
    }

    public function destroy($id)
    {
        return redirect()->route('horario.configuracion');
    }
}
