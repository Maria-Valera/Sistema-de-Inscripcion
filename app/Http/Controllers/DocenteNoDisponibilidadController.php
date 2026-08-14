<?php

namespace App\Http\Controllers;

use App\Models\DocenteNoDisponibilidad;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\AnioEscolar;
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

            $query = DocenteNoDisponibilidad::with(['diaSemana', 'bloqueHorario']);

            if ($request->has('cedula_persona') && !empty($request->cedula_persona)) {
                $query->whereHas('docente.persona', function ($q) use ($request) {
                    $q->where('numero_documento', $request->cedula_persona);
                });
            }

            if ($request->has('anio_escolar') && !empty($request->anio_escolar)) {
                $query->where('anio_escolar_id', $request->anio_escolar);
            }

            $noDisponibilidades = $query->get();

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
                'id_dias_semana' => 'required|integer|exists:dias_semana,id',
                'id_bloque' => 'required|integer|exists:bloque_horarios,id',
                'anio_escolar' => 'required|integer|exists:anio_escolars,id',
                'motivo' => 'nullable|string|max:255',
            ]);

            $persona = Persona::where('numero_documento', $validated['cedula_persona'])->first();
            $docente = Docente::where('persona_id', $persona->id)->first();

            if (!$docente) {
                return response()->json(['error' => 'Docente no encontrado'], 404);
            }

            $noDisponibilidad = DocenteNoDisponibilidad::create([
                'docente_id' => $docente->id,
                'anio_escolar_id' => $validated['anio_escolar'],
                'dias_semana_id' => $validated['id_dias_semana'],
                'id_bloque_hora' => $validated['id_bloque'],
                'motivo' => $validated['motivo'] ?? null,
            ]);

            return response()->json($noDisponibilidad, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $noDisponibilidad = DocenteNoDisponibilidad::findOrFail($id);
            $noDisponibilidad->delete();

            return response()->json(['message' => 'Registro eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}