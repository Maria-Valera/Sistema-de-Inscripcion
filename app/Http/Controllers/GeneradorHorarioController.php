<?php

namespace App\Http\Controllers;

use App\Services\GeneradorHorarioService;
use App\Models\HorarioAsignacion;
use App\Models\AnioEscolar;
use App\Models\AreaFormacion;
use Illuminate\Http\Request;

class GeneradorHorarioController extends Controller
{
    protected GeneradorHorarioService $generador;

    public function __construct(GeneradorHorarioService $generador)
    {
        $this->generador = $generador;
    }

    private function getAnioEscolarActivo()
    {
        return AnioEscolar::activos()->first();
    }

    public function generar(Request $request)
    {
        $validado = $request->validate([
            'anio_escolar_id' => 'nullable|integer',
            'total_dias' => 'nullable|integer',
            'total_bloques' => 'nullable|integer',
        ]);

        // Si no se proporciona anio_escolar_id, usar el activo
        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        $resultado = $this->generador->generar(
            $anioEscolarId,
            $validado['total_dias'] ?? 5,
            $validado['total_bloques'] ?? 8
        );

        return response()->json($resultado);
    }

    public function getAnioEscolarActivoAPI()
    {
        $anioEscolar = $this->getAnioEscolarActivo();
        if (!$anioEscolar) {
            return response()->json(['error' => 'No hay año escolar activo'], 404);
        }
        return response()->json($anioEscolar);
    }

    public function obtenerAsignaciones(Request $request)
    {
        $validado = $request->validate([
            'anio_escolar_id' => 'nullable|integer',
            'grado_id' => 'nullable|integer',
        ]);

        // Si no se proporciona anio_escolar_id, usar el activo
        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        $query = HorarioAsignacion::with(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque'])
            ->where('anio_escolar_id', $anioEscolarId)
            ->where('conflicto_manual', false);

        // Filtrar por grado_id si se proporciona
        if (isset($validado['grado_id']) && $validado['grado_id']) {
            $query->whereHas('seccion', function($q) use ($validado) {
                $q->where('grado_id', $validado['grado_id']);
            });
        }

        $asignaciones = $query->get()
            ->map(function ($asignacion) {
                return [
                    'id' => $asignacion->id,
                    'docente_id' => $asignacion->docente_id,
                    'docente_nombre' => $asignacion->docente && $asignacion->docente->persona 
                        ? $asignacion->docente->persona->primer_nombre . ' ' . $asignacion->docente->persona->primer_apellido 
                        : 'N/A',
                    'materia_id' => $asignacion->materia_id,
                    'materia_nombre' => $asignacion->materia ? $asignacion->materia->nombre_area_formacion : 'N/A',
                    'seccion_id' => $asignacion->seccion_id,
                    'seccion_nombre' => $asignacion->seccion ? $asignacion->seccion->nombre : 'N/A',
                    'grado_id' => $asignacion->seccion ? $asignacion->seccion->grado_id : null,
                    'grado_nombre' => $asignacion->seccion && $asignacion->seccion->grado 
                        ? $asignacion->seccion->grado->numero_grado . '° Grado' 
                        : 'N/A',
                    'aula_id' => $asignacion->aula_id,
                    'aula_nombre' => $asignacion->aula ? $asignacion->aula->nombre_aula : 'N/A',
                    'aula_tipo' => $asignacion->aula ? $asignacion->aula->tipo_aula : 'N/A',
                    'dia_id' => $asignacion->dia_id,
                    'dia_nombre' => $asignacion->dia ? $asignacion->dia->nombre_dia : $asignacion->dia_id,
                    'bloque_id' => $asignacion->bloque_id,
                    'bloque_nombre' => $asignacion->bloque ? 'Bloque ' . $asignacion->bloque_id : $asignacion->bloque_id,
                ];
            });

        return response()->json($asignaciones);
    }

    public function reacomodar(Request $request)
    {
        $validado = $request->validate([
            'docente_id' => 'required|integer',
            'seccion_id' => 'required|integer',
            'aula_id' => 'required|integer',
            'dia_actual' => 'required|integer',
            'bloque_actual' => 'required|integer',
            'asignaciones_existentes' => 'required|array',
            'anio_escolar_id' => 'required|integer',
            'total_dias' => 'nullable|integer',
            'total_bloques' => 'nullable|integer',
        ]);

        $resultado = $this->generador->reacomodarClase(
            $validado['docente_id'],
            $validado['seccion_id'],
            $validado['aula_id'],
            $validado['dia_actual'],
            $validado['bloque_actual'],
            $validado['asignaciones_existentes'],
            $validado['anio_escolar_id'],
            $validado['total_dias'] ?? 5,
            $validado['total_bloques'] ?? 8
        );

        // Si el reacomodo fue exitoso, actualizar en la base de datos
        if ($resultado['exito']) {
            if ($resultado['nivel'] == 1) {
                // Nivel 1: Mover la asignación
                $asignacion = HorarioAsignacion::where('anio_escolar_id', $validado['anio_escolar_id'])
                    ->where('docente_id', $validado['docente_id'])
                    ->where('seccion_id', $validado['seccion_id'])
                    ->where('dia_id', $validado['dia_actual'])
                    ->where('bloque_id', $validado['bloque_actual'])
                    ->first();

                if ($asignacion) {
                    $asignacion->update([
                        'dia_id' => $resultado['nueva_posicion']['dia_id'],
                        'bloque_id' => $resultado['nueva_posicion']['bloque_id'],
                        'aula_id' => $resultado['nueva_posicion']['aula_id'],
                    ]);
                }
            } elseif ($resultado['nivel'] == 2) {
                // Nivel 2: Intercambio
                $claseOriginal = $resultado['detalles_intercambio']['clase_original'];
                $claseIntercambio = $resultado['detalles_intercambio']['clase_intercambio'];

                // Actualizar clase original
                $asignacionOriginal = HorarioAsignacion::where('anio_escolar_id', $validado['anio_escolar_id'])
                    ->where('docente_id', $claseOriginal['docente_id'])
                    ->where('seccion_id', $claseOriginal['seccion_id'])
                    ->where('dia_id', $claseOriginal['dia_origen'])
                    ->where('bloque_id', $claseOriginal['bloque_origen'])
                    ->first();

                if ($asignacionOriginal) {
                    $asignacionOriginal->update([
                        'dia_id' => $claseOriginal['dia_destino'],
                        'bloque_id' => $claseOriginal['bloque_destino'],
                        'aula_id' => $claseOriginal['aula_destino'],
                    ]);
                }

                // Actualizar clase intercambio
                $asignacionIntercambio = HorarioAsignacion::where('anio_escolar_id', $validado['anio_escolar_id'])
                    ->where('docente_id', $claseIntercambio['docente_id'])
                    ->where('seccion_id', $claseIntercambio['seccion_id'])
                    ->where('dia_id', $claseIntercambio['dia_origen'])
                    ->where('bloque_id', $claseIntercambio['bloque_origen'])
                    ->first();

                if ($asignacionIntercambio) {
                    $asignacionIntercambio->update([
                        'dia_id' => $claseIntercambio['dia_destino'],
                        'bloque_id' => $claseIntercambio['bloque_destino'],
                        'aula_id' => $claseIntercambio['aula_destino'],
                    ]);
                }
            }
        }

        return response()->json($resultado);
    }

    public function actualizarAula(Request $request, int $id)
    {
        $validado = $request->validate([
            'aula_id' => 'required|integer',
        ]);

        $asignacion = HorarioAsignacion::find($id);
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }

        $aula_id = $validado['aula_id'];

        // Validar capacidad del aula
        if (!$this->generador->validarCapacidadAula($aula_id, $asignacion->seccion_id)) {
            return response()->json(['error' => 'El aula no tiene capacidad suficiente para esta sección'], 422);
        }

        // Verificar que el aula no esté ocupada en el mismo día y bloque
        $ocupada = HorarioAsignacion::where('aula_id', $aula_id)
            ->where('dia_id', $asignacion->dia_id)
            ->where('bloque_id', $asignacion->bloque_id)
            ->where('id', '!=', $id)
            ->exists();

        if ($ocupada) {
            return response()->json(['error' => 'El aula ya está ocupada en ese día y bloque'], 422);
        }

        // Actualizar el aula
        $asignacion->aula_id = $aula_id;
        $asignacion->save();

        return response()->json(['success' => true, 'asignacion' => $asignacion]);
    }

    public function obtenerAreasPorGrado(Request $request)
    {
        $validado = $request->validate([
            'grado_id' => 'required|integer',
        ]);

        $areas = AreaFormacion::where('status', true)
            ->whereHas('grados', function($query) use ($validado) {
                $query->where('grado_id', $validado['grado_id'])
                    ->where('grado_area_formacions.status', true);
            })
            ->get()
            ->map(function($area) {
                return [
                    'id' => $area->id,
                    'nombre' => $area->nombre_area_formacion,
                ];
            });

        return response()->json($areas);
    }

    public function guardarAsignaciones(Request $request)
    {
        $validado = $request->validate([
            'asignaciones' => 'required|array',
            'anio_escolar_id' => 'nullable|integer',
        ]);

        // Si no se proporciona anio_escolar_id, usar el activo
        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        // Limpiar asignaciones anteriores del mismo año escolar
        HorarioAsignacion::where('anio_escolar_id', $anioEscolarId)->delete();

        $asignacionesGuardadas = 0;
        $errores = [];

        foreach ($validado['asignaciones'] as $asignacion) {
            try {
                HorarioAsignacion::create([
                    'anio_escolar_id' => $anioEscolarId,
                    'docente_id' => $asignacion['docente_id'],
                    'materia_id' => $asignacion['materia_id'],
                    'seccion_id' => $asignacion['seccion_id'],
                    'aula_id' => $asignacion['aula_id'],
                    'dia_id' => $asignacion['dia_id'],
                    'bloque_id' => $asignacion['bloque_id'],
                    'conflicto_manual' => false,
                ]);
                $asignacionesGuardadas++;
            } catch (\Exception $e) {
                $errores[] = [
                    'asignacion' => $asignacion,
                    'error' => $e->getMessage()
                ];
            }
        }

        if (count($errores) > 0) {
            return response()->json([
                'success' => false,
                'guardadas' => $asignacionesGuardadas,
                'errores' => $errores,
                'mensaje' => "Se guardaron {$asignacionesGuardadas} asignaciones con " . count($errores) . " errores"
            ], 207); // 207 Multi-Status
        }

        return response()->json([
            'success' => true,
            'guardadas' => $asignacionesGuardadas,
            'mensaje' => "Se guardaron {$asignacionesGuardadas} asignaciones exitosamente"
        ]);
    }
}
