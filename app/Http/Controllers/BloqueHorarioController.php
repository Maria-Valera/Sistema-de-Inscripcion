<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use Illuminate\Http\Request;
use App\Models\AnioEscolar;

class BloqueHorarioController extends Controller
{
    private function verificarAnioEscolar()
    {
        return AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->exists();
    }

    public function index()
    {
        $bloques = BloqueHorario::where('status', true)
            ->orderBy('hora_inicio', 'asc')
            ->paginate(10);

        $anioEscolarActivo = $this->verificarAnioEscolar();

        return view('admin.bloque_horario.index', compact('bloques', 'anioEscolarActivo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $existe = BloqueHorario::where('hora_inicio', $validated['hora_inicio'])
            ->where('hora_fin', $validated['hora_fin'])
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('error', 'Ya existe un bloque horario con el mismo horario.');
        }

        try {
            $bloque = new BloqueHorario();
            $bloque->hora_inicio = $validated['hora_inicio'];
            $bloque->hora_fin = $validated['hora_fin'];
            $bloque->status = true;
            $bloque->save();

            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('success', 'El bloque horario fue creado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('error', 'Ocurrió un error al crear el bloque horario: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $bloque = BloqueHorario::findOrFail($id);

        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);

        $existe = BloqueHorario::where('hora_inicio', $validated['hora_inicio'])
            ->where('hora_fin', $validated['hora_fin'])
            ->where('id', '!=', $bloque->id)
            ->where('status', true)
            ->exists();

        if ($existe) {
            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('error', 'Ya existe un bloque horario con el mismo horario.');
        }

        try {
            $bloque->hora_inicio = $validated['hora_inicio'];
            $bloque->hora_fin = $validated['hora_fin'];
            $bloque->save();

            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('success', 'El bloque horario fue actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('error', 'Ocurrió un error al actualizar el bloque horario: ' . $e->getMessage());
        }
    }

    public function verificarExistencia(Request $request)
    {
        try {
            $request->validate([
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fin' => 'required|date_format:H:i',
            ]);

            $existe = BloqueHorario::where('hora_inicio', $request->hora_inicio)
                ->where('hora_fin', $request->hora_fin)
                ->where('status', true)
                ->exists();

            return response()->json([
                'success' => true,
                'existe' => $existe
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en verificarExistencia: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el bloque horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $bloque = BloqueHorario::find($id);

        if (!$bloque) {
            return redirect()
                ->route('admin.bloque_horario.index')
                ->with('error', 'No se encontró el bloque horario.');
        }

        $bloque->update(['status' => false]);

        return redirect()
            ->route('admin.bloque_horario.index')
            ->with('success', 'El bloque horario fue inactivado correctamente.');
    }
}
