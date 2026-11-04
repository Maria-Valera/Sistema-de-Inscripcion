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

    private function obtenerAulaRegularDeSeccion($seccionId)
    {
        $seccionAula = \App\Models\seccion_aula::where('id_seccion', $seccionId)
            ->where('status', true)
            ->with('aula')
            ->first();

        if ($seccionAula && $seccionAula->aula) {
            return [
                'id_aula' => $seccionAula->aula->id_aula,
                'nombre_aula' => $seccionAula->aula->nombre_aula
            ];
        }

        return null;
    }

    public function generar(Request $request)
    {
        $validado = $request->validate([
            'anio_escolar_id' => 'nullable|integer',
            'total_dias' => 'nullable|integer',
            'total_bloques' => 'nullable|integer',
            'grado_id' => 'nullable|integer',
            'area_formacion_id' => 'nullable|integer',
        ]);

        // Si no se proporciona anio_escolar_id, usar el activo
        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        $resultado = $this->generador->generar(
            $anioEscolarId,
            $validado['total_dias'] ?? 5,
            $validado['total_bloques'] ?? 8,
            $validado['grado_id'] ?? null,
            $validado['area_formacion_id'] ?? null
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
            'area_formacion_id' => 'nullable|integer',
        ]);

        // Si no se proporciona anio_escolar_id, usar el activo
        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        $query = HorarioAsignacion::with(['docente.persona', 'materia.aula', 'seccion.grado', 'aula', 'dia', 'bloque'])
            ->where('anio_escolar_id', $anioEscolarId)
            ->where('conflicto_manual', false);

        // Filtrar por grado_id si se proporciona
        if (isset($validado['grado_id']) && $validado['grado_id']) {
            $query->whereHas('seccion', function($q) use ($validado) {
                $q->where('grado_id', $validado['grado_id']);
            });
        }

        // Filtrar por area_formacion_id si se proporciona
        if (isset($validado['area_formacion_id']) && $validado['area_formacion_id']) {
            $query->where('materia_id', $validado['area_formacion_id']);
        }

        $asignaciones = $query->get()
            ->map(function ($asignacion) {
                // Obtener información de la materia con su aula y es_especializada
                $materia = $asignacion->materia;
                $materiaData = [
                    'id' => $materia ? $materia->id : null,
                    'nombre' => $materia ? $materia->nombre_area_formacion : 'N/A',
                    'aula_id' => $materia ? $materia->aula_id : null,
                    'aula' => $materia && $materia->aula ? [
                        'id_aula' => $materia->aula->id_aula,
                        'nombre_aula' => $materia->aula->nombre_aula,
                        'tipo_aula' => $materia->aula->tipo_aula
                    ] : null,
                    'es_especializada' => $materia ? $materia->es_especializada : false
                ];

                // Obtener aula regular de la sección
                $aulaRegular = $asignacion->seccion_id ? $this->obtenerAulaRegularDeSeccion($asignacion->seccion_id) : null;

                return [
                    'id' => $asignacion->id,
                    'docente_id' => $asignacion->docente_id,
                    'docente_nombre' => $asignacion->docente && $asignacion->docente->persona 
                        ? $asignacion->docente->persona->primer_nombre . ' ' . $asignacion->docente->persona->primer_apellido 
                        : 'N/A',
                    'docente_cedula' => $asignacion->docente && $asignacion->docente->persona 
                        ? $asignacion->docente->persona->numero_documento 
                        : 'N/A',
                    'materia' => $materiaData,
                    'materia_id' => $asignacion->materia_id,
                    'materia_nombre' => $materiaData['nombre'],
                    'seccion_id' => $asignacion->seccion_id,
                    'seccion_nombre' => $asignacion->seccion ? $asignacion->seccion->nombre : 'N/A',
                    'seccion' => $asignacion->seccion ? [
                        'id' => $asignacion->seccion->id,
                        'nombre' => $asignacion->seccion->nombre,
                        'aula_regular' => $aulaRegular
                    ] : null,
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

    public function actualizarPosicion(Request $request, int $id)
    {
        $validado = $request->validate([
            'dia_id' => 'required|integer',
            'bloque_id' => 'required|integer',
        ]);

        $asignacion = HorarioAsignacion::find($id);
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }

        // Validar conflicto de docente
        $conflictoDocente = HorarioAsignacion::where('docente_id', $asignacion->docente_id)
            ->where('dia_id', $validado['dia_id'])
            ->where('bloque_id', $validado['bloque_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($conflictoDocente) {
            return response()->json(['error' => 'El docente ya tiene una asignación en ese día y bloque'], 422);
        }

        // Validar conflicto de sección
        $conflictoSeccion = HorarioAsignacion::where('seccion_id', $asignacion->seccion_id)
            ->where('dia_id', $validado['dia_id'])
            ->where('bloque_id', $validado['bloque_id'])
            ->where('id', '!=', $id)
            ->exists();

        if ($conflictoSeccion) {
            return response()->json(['error' => 'La sección ya tiene una asignación en ese día y bloque'], 422);
        }

        // Validar conflicto de aula si tiene aula asignada
        if ($asignacion->aula_id) {
            $conflictoAula = HorarioAsignacion::where('aula_id', $asignacion->aula_id)
                ->where('dia_id', $validado['dia_id'])
                ->where('bloque_id', $validado['bloque_id'])
                ->where('id', '!=', $id)
                ->exists();

            if ($conflictoAula) {
                return response()->json(['error' => 'El aula ya está ocupada en ese día y bloque'], 422);
            }
        }

        // Actualizar posición
        $asignacion->dia_id = $validado['dia_id'];
        $asignacion->bloque_id = $validado['bloque_id'];
        $asignacion->save();

        // Recargar con relaciones para devolver datos completos
        $asignacion->load(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque']);

        return response()->json(['success' => true, 'asignacion' => $asignacion]);
    }

    public function intercambiar(Request $request)
    {
        $validado = $request->validate([
            'horario_id_1' => 'required|integer',
            'horario_id_2' => 'required|integer',
        ]);

        // Cargar ambos registros con relaciones
        $asignacion1 = HorarioAsignacion::with(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque'])
            ->find($validado['horario_id_1']);
        $asignacion2 = HorarioAsignacion::with(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque'])
            ->find($validado['horario_id_2']);

        if (!$asignacion1 || !$asignacion2) {
            return response()->json(['error' => 'Una o ambas asignaciones no encontradas'], 404);
        }

        // Posiciones actuales
        $dia1 = $asignacion1->dia_id;
        $bloque1 = $asignacion1->bloque_id;
        $dia2 = $asignacion2->dia_id;
        $bloque2 = $asignacion2->bloque_id;

        $detallesConflicto = [];

        // Validar: sección del registro1 no tenga otra clase en (dia2, bloque2), excluyendo registro2
        $conflictoSeccion1 = HorarioAsignacion::where('seccion_id', $asignacion1->seccion_id)
            ->where('dia_id', $dia2)
            ->where('bloque_id', $bloque2)
            ->where('id', '!=', $asignacion1->id)
            ->where('id', '!=', $asignacion2->id)
            ->exists();

        if ($conflictoSeccion1) {
            $detallesConflicto[] = [
                'tipo' => 'seccion',
                'mensaje' => 'La sección de la primera asignación ya tiene otra clase en el horario destino'
            ];
        }

        // Validar: aula del registro1 no esté ocupada en (dia2, bloque2), excluyendo registro2
        if ($asignacion1->aula_id) {
            $conflictoAula1 = HorarioAsignacion::where('aula_id', $asignacion1->aula_id)
                ->where('dia_id', $dia2)
                ->where('bloque_id', $bloque2)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->exists();

            if ($conflictoAula1) {
                $detallesConflicto[] = [
                    'tipo' => 'aula',
                    'mensaje' => 'El aula de la primera asignación ya está ocupada en el horario destino'
                ];
            }
        }

        // Validar: sección del registro2 no tenga otra clase en (dia1, bloque1), excluyendo registro1
        $conflictoSeccion2 = HorarioAsignacion::where('seccion_id', $asignacion2->seccion_id)
            ->where('dia_id', $dia1)
            ->where('bloque_id', $bloque1)
            ->where('id', '!=', $asignacion1->id)
            ->where('id', '!=', $asignacion2->id)
            ->exists();

        if ($conflictoSeccion2) {
            $detallesConflicto[] = [
                'tipo' => 'seccion',
                'mensaje' => 'La sección de la segunda asignación ya tiene otra clase en el horario destino'
            ];
        }

        // Validar: aula del registro2 no esté ocupada en (dia1, bloque1), excluyendo registro1
        if ($asignacion2->aula_id) {
            $conflictoAula2 = HorarioAsignacion::where('aula_id', $asignacion2->aula_id)
                ->where('dia_id', $dia1)
                ->where('bloque_id', $bloque1)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->exists();

            if ($conflictoAula2) {
                $detallesConflicto[] = [
                    'tipo' => 'aula',
                    'mensaje' => 'El aula de la segunda asignación ya está ocupada en el horario destino'
                ];
            }
        }

        // Validar opcional: docentes no tengan otra clase en horario destino (en otra sección)
        $conflictoDocente1 = HorarioAsignacion::where('docente_id', $asignacion1->docente_id)
            ->where('dia_id', $dia2)
            ->where('bloque_id', $bloque2)
            ->where('seccion_id', '!=', $asignacion2->seccion_id)
            ->where('id', '!=', $asignacion1->id)
            ->exists();

        if ($conflictoDocente1) {
            $detallesConflicto[] = [
                'tipo' => 'docente',
                'mensaje' => 'El docente de la primera asignación ya tiene otra clase en ese horario'
            ];
        }

        $conflictoDocente2 = HorarioAsignacion::where('docente_id', $asignacion2->docente_id)
            ->where('dia_id', $dia1)
            ->where('bloque_id', $bloque1)
            ->where('seccion_id', '!=', $asignacion1->seccion_id)
            ->where('id', '!=', $asignacion2->id)
            ->exists();

        if ($conflictoDocente2) {
            $detallesConflicto[] = [
                'tipo' => 'docente',
                'mensaje' => 'El docente de la segunda asignación ya tiene otra clase en ese horario'
            ];
        }

        if (!empty($detallesConflicto)) {
            return response()->json([
                'error' => 'conflicto',
                'detalles' => $detallesConflicto
            ], 422);
        }

        // Realizar intercambio en transacción
        \DB::transaction(function() use ($asignacion1, $asignacion2, $dia1, $bloque1, $dia2, $bloque2) {
            // Intercambiar posiciones
            $tempDia = $asignacion1->dia_id;
            $tempBloque = $asignacion1->bloque_id;
            
            $asignacion1->dia_id = $asignacion2->dia_id;
            $asignacion1->bloque_id = $asignacion2->bloque_id;
            $asignacion1->save();
            
            $asignacion2->dia_id = $tempDia;
            $asignacion2->bloque_id = $tempBloque;
            $asignacion2->save();
        });

        // Recargar relaciones para devolver datos completos
        $asignacion1->load(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque']);
        $asignacion2->load(['docente.persona', 'materia', 'seccion.grado', 'aula', 'dia', 'bloque']);

        return response()->json([
            'success' => true,
            'asignacion1' => $asignacion1,
            'asignacion2' => $asignacion2
        ]);
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

        return \DB::transaction(function() use ($validado, $anioEscolarId) {
            // Limpiar asignaciones anteriores del mismo año escolar
            HorarioAsignacion::where('anio_escolar_id', $anioEscolarId)->delete();

            $asignacionesGuardadas = 0;
            $errores = [];

            // Precarga aulas y secciones en 2 consultas (antes eran 2 consultas por asignación)
            $coleccion = collect($validado['asignaciones']);
            $aulasPorId = \App\Models\Aula::whereIn('id_aula', $coleccion->pluck('aula_id')->filter()->unique())
                ->get()->keyBy('id_aula');
            $seccionesPorId = \App\Models\Seccion::whereIn('id', $coleccion->pluck('seccion_id')->filter()->unique())
                ->get()->keyBy('id');

            foreach ($validado['asignaciones'] as $asignacion) {
                try {
                    // Validar que aula_id no sea 0
                    $aulaId = isset($asignacion['aula_id']) && $asignacion['aula_id'] > 0 ? $asignacion['aula_id'] : null;
                    
                    // Validar IDs obligatorios
                    if (!$asignacion['docente_id'] || !$asignacion['materia_id'] || 
                        !$asignacion['seccion_id'] || !$asignacion['dia_id'] || !$asignacion['bloque_id']) {
                        $errores[] = [
                            'asignacion' => $asignacion,
                            'error' => 'IDs obligatorios faltantes (docente, materia, sección, día o bloque)'
                        ];
                        continue;
                    }

                    // Validar capacidad del aula si se asigna
                    if ($aulaId) {
                        $aula = $aulasPorId->get($aulaId);
                        $seccion = $seccionesPorId->get($asignacion['seccion_id']);
                        
                        if ($aula && $seccion && $aula->capacidad_maxima !== null) {
                            if ($seccion->cantidad_actual > $aula->capacidad_maxima) {
                                $errores[] = [
                                    'asignacion' => $asignacion,
                                    'error' => 'La sección excede la capacidad máxima del aula'
                                ];
                                continue;
                            }
                        }
                    }

                    HorarioAsignacion::create([
                        'anio_escolar_id' => $anioEscolarId,
                        'docente_id' => $asignacion['docente_id'],
                        'materia_id' => $asignacion['materia_id'],
                        'seccion_id' => $asignacion['seccion_id'],
                        'aula_id' => $aulaId,
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
        });
    }

    public function eliminarAsignacion($id)
    {
        $asignacion = HorarioAsignacion::find($id);
        
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }
        
        $asignacion->delete();
        
        return response()->json(['success' => true, 'mensaje' => 'Asignación eliminada correctamente']);
    }

    public function eliminarAsignacionesMultiples(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['error' => 'Debe proporcionar un array de ids'], 422);
        }
        
        $eliminados = HorarioAsignacion::whereIn('id', $ids)->delete();
        
        return response()->json([
            'success' => true, 
            'mensaje' => "$eliminados asignación(es) eliminada(s) correctamente",
            'cantidad_eliminada' => $eliminados
        ]);
    }
}
