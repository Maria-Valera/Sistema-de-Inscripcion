<?php

namespace App\Http\Controllers;

use App\Models\DocenteIndisponibilidad;
use App\Models\Docente;
use App\Models\Persona;
use App\Support\AnioEscolarSeleccionado;
use App\Enums\Dia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocenteNoDisponibilidadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cedula_persona' => 'nullable|string',
                'anio_escolar' => 'nullable|integer',
            ]);

            $query = DocenteIndisponibilidad::query();

            if ($request->has('cedula_persona') && !empty($request->cedula_persona)) {
                $query->whereHas('docente.persona', function ($q) use ($request) {
                    $q->where('numero_documento', $request->cedula_persona);
                });
            }

            $anioEscolarId = $request->input('anio_escolar', AnioEscolarSeleccionado::id());
            if ($anioEscolarId) {
                $query->where('anio_escolar_id', $anioEscolarId);
            }

            $noDisponibilidades = $query->get()->map(function($item) {
                $diaNum = $item->dia instanceof Dia ? $item->dia->value : (int) $item->dia;
                $bloqueNum = (int) $item->bloque;
                return [
                    'id' => $item->id,
                    'docente_id' => $item->docente_id,
                    'anio_escolar_id' => $item->anio_escolar_id,
                    'dia' => (int)$diaNum,
                    'bloque' => $bloqueNum,
                    'tipo' => $item->tipo ?? 'prohibido',
                    'estado' => $item->estado ?? 'aprobada',
                    'motivo' => $item->motivo
                ];
            });

            return response()->json($noDisponibilidades);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cedula_persona' => 'required|string|exists:personas,numero_documento',
                'dia' => 'required|integer',
                'bloque' => 'required|integer',
                'anio_escolar' => 'required|integer|exists:anio_escolars,id',
                'motivo' => 'nullable|string|max:255',
            ]);

            $diaVal = (int) $validated['dia'];
            $bloqueVal = (int) $validated['bloque'];

            $persona = Persona::where('numero_documento', $validated['cedula_persona'])->first();
            $docente = Docente::where('persona_id', $persona->id)->first();

            if (!$docente) {
                return response()->json(['error' => 'Docente no encontrado'], 404);
            }

            $noDisponibilidad = DocenteIndisponibilidad::updateOrCreate([
                'docente_id' => $docente->id,
                'anio_escolar_id' => $validated['anio_escolar'],
                'dia' => $diaVal,
                'bloque' => $bloqueVal,
            ], [
                'tipo' => $request->input('tipo', 'prohibido'),
                'estado' => $request->input('estado', 'aprobada'),
                'motivo' => $validated['motivo'] ?? 'Marcado por administrador',
            ]);

            return response()->json($noDisponibilidad, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $noDisponibilidad = DocenteIndisponibilidad::findOrFail($id);
            $noDisponibilidad->delete();

            return response()->json(['message' => 'Registro eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}