<?php

namespace App\Http\Controllers;

use App\Services\GeneradorHorarioService;
use App\Services\ValidadorHorarioService;
use App\Models\HorarioAsignacion;
use App\Models\AnioEscolar;
use App\Models\AreaFormacion;
use App\Models\CargaAcademica;
use App\Models\BloqueReservado;
use App\Models\DocenteIndisponibilidad;
use App\Enums\Dia;
use Illuminate\Http\Request;

class GeneradorHorarioController extends Controller
{
    protected GeneradorHorarioService $generador;
    protected ValidadorHorarioService $validador;

    public function __construct(GeneradorHorarioService $generador, ValidadorHorarioService $validador)
    {
        $this->generador = $generador;
        $this->validador = $validador;
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

    public function validar(Request $request)
    {
        $validado = $request->validate([
            'anio_escolar_id' => 'nullable|integer',
            'grado_id' => 'nullable|integer',
        ]);

        $anioEscolarId = $validado['anio_escolar_id'] ?? $this->getAnioEscolarActivo()?->id;

        if (!$anioEscolarId) {
            return response()->json(['error' => 'No hay año escolar activo'], 400);
        }

        $resultado = $this->validador->validar(
            (int) $anioEscolarId,
            isset($validado['grado_id']) && $validado['grado_id'] !== '' ? (int) $validado['grado_id'] : null
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

        $query = HorarioAsignacion::with([
            'cargaAcademica.docente.persona',
            'cargaAcademica.areaFormacion.aula',
            'cargaAcademica.seccion.grado',
            'aula'
        ])
        ->where('anio_escolar_id', $anioEscolarId)
        ->where('conflicto_manual', false);

        // Filtrar por grado_id si se proporciona
        if (isset($validado['grado_id']) && $validado['grado_id']) {
            $query->whereHas('cargaAcademica.seccion', function($q) use ($validado) {
                $q->where('grado_id', $validado['grado_id']);
            });
        }

        // Filtrar por area_formacion_id si se proporciona
        if (isset($validado['area_formacion_id']) && $validado['area_formacion_id']) {
            $query->whereHas('cargaAcademica', function($q) use ($validado) {
                $q->where('area_formacion_id', $validado['area_formacion_id']);
            });
        }

        $asignaciones = $query->get()
            ->map(function ($asignacion) {
                $carga = $asignacion->cargaAcademica;
                $docente = $carga?->docente;
                $persona = $docente?->persona;
                $materia = $carga?->areaFormacion;
                $seccion = $carga?->seccion;
                $grado = $seccion?->grado;

                $diaVal = (int) ($asignacion->dia ?? $asignacion->dia_id);
                $bloqueVal = (int) ($asignacion->bloque ?? $asignacion->bloque_id);
                $diaEnum = Dia::tryFrom($diaVal);

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

                $aulaRegular = $seccion ? $this->obtenerAulaRegularDeSeccion($seccion->id) : null;

                return [
                    'id' => $asignacion->id,
                    'carga_academica_id' => $asignacion->carga_academica_id,
                    'dia' => $diaVal,
                    'dia_id' => $diaVal,
                    'dia_nombre' => $diaEnum?->label() ?? ('Día ' . $diaVal),
                    'bloque' => $bloqueVal,
                    'bloque_id' => $bloqueVal,
                    'bloque_nombre' => 'Bloque ' . $bloqueVal,
                    'fijada' => (bool) $asignacion->fijada,
                    'docente_id' => $docente?->id,
                    'docente_nombre' => $persona 
                        ? trim($persona->primer_nombre . ' ' . $persona->primer_apellido) 
                        : 'N/A',
                    'docente_cedula' => $persona 
                        ? $persona->numero_documento 
                        : 'N/A',
                    'materia' => $materiaData,
                    'materia_id' => $materia?->id,
                    'materia_nombre' => $materiaData['nombre'],
                    'seccion_id' => $seccion?->id,
                    'seccion_nombre' => $seccion ? $seccion->nombre : 'N/A',
                    'seccion' => $seccion ? [
                        'id' => $seccion->id,
                        'nombre' => $seccion->nombre,
                        'aula_regular' => $aulaRegular
                    ] : null,
                    'grado_id' => $grado?->id,
                    'grado_nombre' => $grado 
                        ? $grado->numero_grado . '° Grado' 
                        : 'N/A',
                    'aula_id' => $asignacion->aula_id,
                    'aula_nombre' => $asignacion->aula ? $asignacion->aula->nombre_aula : 'N/A',
                    'aula_tipo' => $asignacion->aula ? $asignacion->aula->tipo_aula : 'N/A',
                    'cargaAcademica' => [
                        'id' => $carga?->id,
                        'docente_id' => $docente?->id,
                        'docente' => $docente ? [
                            'id' => $docente->id,
                            'nombre' => $persona ? trim($persona->primer_nombre . ' ' . $persona->primer_apellido) : 'N/A',
                        ] : null,
                        'materia' => $materiaData,
                        'seccion' => $seccion ? [
                            'id' => $seccion->id,
                            'nombre' => $seccion->nombre,
                        ] : null,
                    ],
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
                    ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $validado['docente_id'])->where('seccion_id', $validado['seccion_id']))
                    ->where(fn($q) => $q->where('dia', $validado['dia_actual'])->orWhere('dia_id', $validado['dia_actual']))
                    ->where(fn($q) => $q->where('bloque', $validado['bloque_actual'])->orWhere('bloque_id', $validado['bloque_actual']))
                    ->first();

                if ($asignacion) {
                    $nuevoDia = $resultado['nueva_posicion']['dia'] ?? $resultado['nueva_posicion']['dia_id'];
                    $nuevoBloque = $resultado['nueva_posicion']['bloque'] ?? $resultado['nueva_posicion']['bloque_id'];
                    $asignacion->update([
                        'dia' => $nuevoDia,
                        'bloque' => $nuevoBloque,
                        'dia_id' => $nuevoDia,
                        'bloque_id' => $nuevoBloque,
                        'aula_id' => $resultado['nueva_posicion']['aula_id'],
                    ]);
                }
            } elseif ($resultado['nivel'] == 2) {
                // Nivel 2: Intercambio
                $claseOriginal = $resultado['detalles_intercambio']['clase_original'];
                $claseIntercambio = $resultado['detalles_intercambio']['clase_intercambio'];

                // Actualizar clase original
                $asignacionOriginal = HorarioAsignacion::where('anio_escolar_id', $validado['anio_escolar_id'])
                    ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $claseOriginal['docente_id'])->where('seccion_id', $claseOriginal['seccion_id']))
                    ->where(fn($q) => $q->where('dia', $claseOriginal['dia_origen'])->orWhere('dia_id', $claseOriginal['dia_origen']))
                    ->where(fn($q) => $q->where('bloque', $claseOriginal['bloque_origen'])->orWhere('bloque_id', $claseOriginal['bloque_origen']))
                    ->first();

                if ($asignacionOriginal) {
                    $origDiaDest = $claseOriginal['dia_destino'];
                    $origBloqueDest = $claseOriginal['bloque_destino'];
                    $asignacionOriginal->update([
                        'dia' => $origDiaDest,
                        'bloque' => $origBloqueDest,
                        'dia_id' => $origDiaDest,
                        'bloque_id' => $origBloqueDest,
                        'aula_id' => $claseOriginal['aula_destino'],
                    ]);
                }

                // Actualizar clase intercambio
                $asignacionIntercambio = HorarioAsignacion::where('anio_escolar_id', $validado['anio_escolar_id'])
                    ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $claseIntercambio['docente_id'])->where('seccion_id', $claseIntercambio['seccion_id']))
                    ->where(fn($q) => $q->where('dia', $claseIntercambio['dia_origen'])->orWhere('dia_id', $claseIntercambio['dia_origen']))
                    ->where(fn($q) => $q->where('bloque', $claseIntercambio['bloque_origen'])->orWhere('bloque_id', $claseIntercambio['bloque_origen']))
                    ->first();

                if ($asignacionIntercambio) {
                    $interDiaDest = $claseIntercambio['dia_destino'];
                    $interBloqueDest = $claseIntercambio['bloque_destino'];
                    $asignacionIntercambio->update([
                        'dia' => $interDiaDest,
                        'bloque' => $interBloqueDest,
                        'dia_id' => $interDiaDest,
                        'bloque_id' => $interBloqueDest,
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

        $asignacion = HorarioAsignacion::with('cargaAcademica')->find($id);
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }

        $aula_id = $validado['aula_id'];
        $seccionId = $asignacion->cargaAcademica?->seccion_id ?? $asignacion->seccion_id;

        // Validar capacidad del aula
        if ($seccionId && !$this->generador->validarCapacidadAula($aula_id, $seccionId)) {
            return response()->json(['error' => 'El aula no tiene capacidad suficiente para esta sección'], 422);
        }

        // Verificar que el aula no esté ocupada en el mismo día y bloque
        $asigDia = $asignacion->dia ?? $asignacion->dia_id;
        $asigBloque = $asignacion->bloque ?? $asignacion->bloque_id;
        $ocupada = HorarioAsignacion::where('aula_id', $aula_id)
            ->where('anio_escolar_id', $asignacion->anio_escolar_id)
            ->where(fn($q) => $q->where('dia', $asigDia)->orWhere('dia_id', $asigDia))
            ->where(fn($q) => $q->where('bloque', $asigBloque)->orWhere('bloque_id', $asigBloque))
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
            'dia_id' => 'nullable|integer',
            'bloque_id' => 'nullable|integer',
            'dia' => 'nullable|integer',
            'bloque' => 'nullable|integer',
        ]);

        $dia = (int) ($validado['dia'] ?? $validado['dia_id'] ?? 0);
        $bloque = (int) ($validado['bloque'] ?? $validado['bloque_id'] ?? 0);

        if ($dia <= 0 || $bloque <= 0) {
            return response()->json(['error' => 'Día y bloque son requeridos y deben ser válidos'], 422);
        }

        $asignacion = HorarioAsignacion::with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula'])->find($id);
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }

        // 1. Validar si la clase está fijada
        if ($asignacion->fijada) {
            return response()->json(['error' => 'No se puede mover una clase fijada (candado activo).'], 422);
        }

        // 2. Validar si la celda está reservada
        $esReservada = BloqueReservado::where('anio_escolar_id', $asignacion->anio_escolar_id)
            ->where('dia', $dia)
            ->where('bloque', $bloque)
            ->exists();

        if ($esReservada) {
            return response()->json(['error' => 'El bloque seleccionado está reservado institucionalmente.'], 422);
        }

        // 3. Validar si el docente tiene indisponibilidad prohibida
        $docenteId = $asignacion->cargaAcademica?->docente_id;
        if ($docenteId) {
            $esProhibido = DocenteIndisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $asignacion->anio_escolar_id)
                ->where('dia', $dia)
                ->where('bloque', $bloque)
                ->where('estado', 'aprobada')
                ->where('tipo', 'prohibido')
                ->exists();

            if ($esProhibido) {
                return response()->json(['error' => 'El docente no está disponible en este día y bloque (indisponibilidad aprobada).'], 422);
            }
        }

        // 4. Validar unicidad de carga académica (misma clase en el mismo bloque)
        $cargaId = $asignacion->carga_academica_id;
        if ($cargaId) {
            $conflictoCarga = HorarioAsignacion::where('anio_escolar_id', $asignacion->anio_escolar_id)
                ->where('id', '!=', $id)
                ->where('carga_academica_id', $cargaId)
                ->where(fn($q) => $q->where('dia', $dia)->orWhere('dia_id', $dia))
                ->where(fn($q) => $q->where('bloque', $bloque)->orWhere('bloque_id', $bloque))
                ->exists();

            if ($conflictoCarga) {
                return response()->json(['error' => 'La clase ya está asignada en ese día y bloque'], 422);
            }
        }

        // 5. Validar conflicto de docente
        if ($docenteId) {
            $conflictoDocente = HorarioAsignacion::where('anio_escolar_id', $asignacion->anio_escolar_id)
                ->where('id', '!=', $id)
                ->where(fn($q) => $q->where('dia', $dia)->orWhere('dia_id', $dia))
                ->where(fn($q) => $q->where('bloque', $bloque)->orWhere('bloque_id', $bloque))
                ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $docenteId))
                ->exists();

            if ($conflictoDocente) {
                return response()->json(['error' => 'El docente ya tiene clase en ese bloque'], 422);
            }
        }

        // 6. Validar conflicto de sección
        $seccionId = $asignacion->cargaAcademica?->seccion_id;
        if ($seccionId) {
            $conflictoSeccion = HorarioAsignacion::where('anio_escolar_id', $asignacion->anio_escolar_id)
                ->where('id', '!=', $id)
                ->where(fn($q) => $q->where('dia', $dia)->orWhere('dia_id', $dia))
                ->where(fn($q) => $q->where('bloque', $bloque)->orWhere('bloque_id', $bloque))
                ->whereHas('cargaAcademica', fn($q) => $q->where('seccion_id', $seccionId))
                ->exists();

            if ($conflictoSeccion) {
                return response()->json(['error' => 'La sección ya tiene clase en ese bloque'], 422);
            }
        }

        // 6. Validar conflicto de aula si tiene aula asignada
        if ($asignacion->aula_id) {
            $conflictoAula = HorarioAsignacion::where('anio_escolar_id', $asignacion->anio_escolar_id)
                ->where('aula_id', $asignacion->aula_id)
                ->where(fn($q) => $q->where('dia', $dia)->orWhere('dia_id', $dia))
                ->where(fn($q) => $q->where('bloque', $bloque)->orWhere('bloque_id', $bloque))
                ->where('id', '!=', $id)
                ->exists();

            if ($conflictoAula) {
                return response()->json(['error' => 'El aula ya está ocupada en ese día y bloque'], 422);
            }
        }

        // Actualizar posición con captura de excepción de unicidad
        try {
            $asignacion->dia = $dia;
            $asignacion->bloque = $bloque;
            $asignacion->dia_id = $dia;
            $asignacion->bloque_id = $bloque;
            $asignacion->save();
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'unique_carga_bloque') || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json(['error' => 'La clase ya está asignada en ese día y bloque'], 422);
            }
            throw $e;
        }

        // Recargar con relaciones para devolver datos completos
        $asignacion->load(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula']);

        return response()->json(['success' => true, 'asignacion' => $asignacion]);
    }

    public function toggleFijar(Request $request, int $id)
    {
        $asignacion = HorarioAsignacion::with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula'])->find($id);
        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no encontrada'], 404);
        }

        $nuevoEstado = $request->has('fijada') 
            ? (bool) $request->input('fijada') 
            : !$asignacion->fijada;

        $asignacion->fijada = $nuevoEstado;
        $asignacion->save();

        return response()->json([
            'success' => true,
            'fijada' => (bool) $asignacion->fijada,
            'mensaje' => $asignacion->fijada ? 'Clase fijada exitosamente' : 'Clase desbloqueada exitosamente',
            'asignacion' => $asignacion
        ]);
    }

    public function intercambiar(Request $request)
    {
        $validado = $request->validate([
            'horario_id_1' => 'required|integer',
            'horario_id_2' => 'required|integer',
        ]);

        // Cargar ambos registros con relaciones
        $asignacion1 = HorarioAsignacion::with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula'])
            ->find($validado['horario_id_1']);
        $asignacion2 = HorarioAsignacion::with(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula'])
            ->find($validado['horario_id_2']);

        if (!$asignacion1 || !$asignacion2) {
            return response()->json(['error' => 'Una o ambas asignaciones no encontradas'], 404);
        }

        // 1. Validar si alguna de las dos está fijada
        if ($asignacion1->fijada || $asignacion2->fijada) {
            return response()->json(['error' => 'No se puede intercambiar una clase fijada (candado activo).'], 422);
        }

        // Posiciones actuales
        $dia1 = (int) ($asignacion1->dia ?? $asignacion1->dia_id);
        $bloque1 = (int) ($asignacion1->bloque ?? $asignacion1->bloque_id);
        $dia2 = (int) ($asignacion2->dia ?? $asignacion2->dia_id);
        $bloque2 = (int) ($asignacion2->bloque ?? $asignacion2->bloque_id);

        // 2. Validar si alguna celda destino está reservada
        $res1 = BloqueReservado::where('anio_escolar_id', $asignacion1->anio_escolar_id)->where('dia', $dia2)->where('bloque', $bloque2)->exists();
        $res2 = BloqueReservado::where('anio_escolar_id', $asignacion2->anio_escolar_id)->where('dia', $dia1)->where('bloque', $bloque1)->exists();
        if ($res1 || $res2) {
            return response()->json(['error' => 'No se puede intercambiar hacia una celda con bloque reservado.'], 422);
        }

        // 3. Validar indisponibilidad prohibida de docentes
        $doc1 = $asignacion1->cargaAcademica?->docente_id;
        $doc2 = $asignacion2->cargaAcademica?->docente_id;

        if ($doc1) {
            $prohibido1 = DocenteIndisponibilidad::where('docente_id', $doc1)
                ->where('anio_escolar_id', $asignacion1->anio_escolar_id)
                ->where('dia', $dia2)
                ->where('bloque', $bloque2)
                ->where('estado', 'aprobada')
                ->where('tipo', 'prohibido')
                ->exists();
            if ($prohibido1) {
                return response()->json(['error' => 'El docente de la primera asignación no está disponible en el horario destino.'], 422);
            }
        }

        if ($doc2) {
            $prohibido2 = DocenteIndisponibilidad::where('docente_id', $doc2)
                ->where('anio_escolar_id', $asignacion2->anio_escolar_id)
                ->where('dia', $dia1)
                ->where('bloque', $bloque1)
                ->where('estado', 'aprobada')
                ->where('tipo', 'prohibido')
                ->exists();
            if ($prohibido2) {
                return response()->json(['error' => 'El docente de la segunda asignación no está disponible en el horario destino.'], 422);
            }
        }

        // 4. Validar conflicto de docentes en destino
        if ($doc1) {
            $conflictoDocente1 = HorarioAsignacion::where('anio_escolar_id', $asignacion1->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where(fn($q) => $q->where('dia', $dia2)->orWhere('dia_id', $dia2))
                ->where(fn($q) => $q->where('bloque', $bloque2)->orWhere('bloque_id', $bloque2))
                ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $doc1))
                ->exists();

            if ($conflictoDocente1) {
                return response()->json(['error' => 'El docente ya tiene clase en ese bloque'], 422);
            }
        }

        if ($doc2) {
            $conflictoDocente2 = HorarioAsignacion::where('anio_escolar_id', $asignacion2->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where(fn($q) => $q->where('dia', $dia1)->orWhere('dia_id', $dia1))
                ->where(fn($q) => $q->where('bloque', $bloque1)->orWhere('bloque_id', $bloque1))
                ->whereHas('cargaAcademica', fn($q) => $q->where('docente_id', $doc2))
                ->exists();

            if ($conflictoDocente2) {
                return response()->json(['error' => 'El docente ya tiene clase en ese bloque'], 422);
            }
        }

        // 5. Validar conflicto de secciones en destino
        $sec1 = $asignacion1->cargaAcademica?->seccion_id;
        if ($sec1) {
            $conflictoSeccion1 = HorarioAsignacion::where('anio_escolar_id', $asignacion1->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where(fn($q) => $q->where('dia', $dia2)->orWhere('dia_id', $dia2))
                ->where(fn($q) => $q->where('bloque', $bloque2)->orWhere('bloque_id', $bloque2))
                ->whereHas('cargaAcademica', fn($q) => $q->where('seccion_id', $sec1))
                ->exists();

            if ($conflictoSeccion1) {
                return response()->json(['error' => 'La sección ya tiene clase en ese bloque'], 422);
            }
        }

        $sec2 = $asignacion2->cargaAcademica?->seccion_id;
        if ($sec2) {
            $conflictoSeccion2 = HorarioAsignacion::where('anio_escolar_id', $asignacion2->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where(fn($q) => $q->where('dia', $dia1)->orWhere('dia_id', $dia1))
                ->where(fn($q) => $q->where('bloque', $bloque1)->orWhere('bloque_id', $bloque1))
                ->whereHas('cargaAcademica', fn($q) => $q->where('seccion_id', $sec2))
                ->exists();

            if ($conflictoSeccion2) {
                return response()->json(['error' => 'La sección ya tiene clase en ese bloque'], 422);
            }
        }

        // 5b. Validar unicidad de carga académica en destino
        $carga1 = $asignacion1->carga_academica_id;
        $carga2 = $asignacion2->carga_academica_id;
        if ($carga1) {
            $conflictoCarga1 = HorarioAsignacion::where('anio_escolar_id', $asignacion1->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where('carga_academica_id', $carga1)
                ->where(fn($q) => $q->where('dia', $dia2)->orWhere('dia_id', $dia2))
                ->where(fn($q) => $q->where('bloque', $bloque2)->orWhere('bloque_id', $bloque2))
                ->exists();

            if ($conflictoCarga1) {
                return response()->json(['error' => 'La clase ya está asignada en ese día y bloque'], 422);
            }
        }

        if ($carga2) {
            $conflictoCarga2 = HorarioAsignacion::where('anio_escolar_id', $asignacion2->anio_escolar_id)
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->where('carga_academica_id', $carga2)
                ->where(fn($q) => $q->where('dia', $dia1)->orWhere('dia_id', $dia1))
                ->where(fn($q) => $q->where('bloque', $bloque1)->orWhere('bloque_id', $bloque1))
                ->exists();

            if ($conflictoCarga2) {
                return response()->json(['error' => 'La clase ya está asignada en ese día y bloque'], 422);
            }
        }

        // 6. Validar conflicto de aulas en destino
        if ($asignacion1->aula_id) {
            $conflictoAula1 = HorarioAsignacion::where('anio_escolar_id', $asignacion1->anio_escolar_id)
                ->where('aula_id', $asignacion1->aula_id)
                ->where(fn($q) => $q->where('dia', $dia2)->orWhere('dia_id', $dia2))
                ->where(fn($q) => $q->where('bloque', $bloque2)->orWhere('bloque_id', $bloque2))
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->exists();

            if ($conflictoAula1) {
                return response()->json(['error' => 'El aula ya está ocupada en ese día y bloque'], 422);
            }
        }

        if ($asignacion2->aula_id) {
            $conflictoAula2 = HorarioAsignacion::where('anio_escolar_id', $asignacion2->anio_escolar_id)
                ->where('aula_id', $asignacion2->aula_id)
                ->where(fn($q) => $q->where('dia', $dia1)->orWhere('dia_id', $dia1))
                ->where(fn($q) => $q->where('bloque', $bloque1)->orWhere('bloque_id', $bloque1))
                ->where('id', '!=', $asignacion1->id)
                ->where('id', '!=', $asignacion2->id)
                ->exists();

            if ($conflictoAula2) {
                return response()->json(['error' => 'El aula ya está ocupada en ese día y bloque'], 422);
            }
        }

        // Realizar intercambio en transacción con captura de unicidad
        try {
            \DB::transaction(function() use ($asignacion1, $asignacion2, $dia1, $bloque1, $dia2, $bloque2) {
                $asignacion1->dia = $dia2;
                $asignacion1->bloque = $bloque2;
                $asignacion1->dia_id = $dia2;
                $asignacion1->bloque_id = $bloque2;
                $asignacion1->save();

                $asignacion2->dia = $dia1;
                $asignacion2->bloque = $bloque1;
                $asignacion2->dia_id = $dia1;
                $asignacion2->bloque_id = $bloque1;
                $asignacion2->save();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'unique_carga_bloque') || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json(['error' => 'La clase ya está asignada en ese día y bloque'], 422);
            }
            throw $e;
        }

        // Recargar relaciones para devolver datos completos
        $asignacion1->load(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula']);
        $asignacion2->load(['cargaAcademica.docente.persona', 'cargaAcademica.areaFormacion', 'cargaAcademica.seccion.grado', 'aula']);

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

                    $cargaAcademicaId = $asignacion['carga_academica_id'] ?? null;
                    $docenteId = $asignacion['docente_id'] ?? null;
                    $materiaId = $asignacion['materia_id'] ?? null;
                    $seccionId = $asignacion['seccion_id'] ?? null;
                    $diaVal = (int) ($asignacion['dia'] ?? $asignacion['dia_id'] ?? 0);
                    $bloqueVal = (int) ($asignacion['bloque'] ?? $asignacion['bloque_id'] ?? 0);

                    if (!$cargaAcademicaId && (!$docenteId || !$materiaId || !$seccionId)) {
                        $errores[] = [
                            'asignacion' => $asignacion,
                            'error' => 'Faltan datos obligatorios de carga académica (docente, materia, sección)'
                        ];
                        continue;
                    }

                    if ($diaVal <= 0 || $bloqueVal <= 0) {
                        $errores[] = [
                            'asignacion' => $asignacion,
                            'error' => 'Día o bloque inválido'
                        ];
                        continue;
                    }

                    if (!$cargaAcademicaId) {
                        $carga = CargaAcademica::firstOrCreate([
                            'anio_escolar_id' => $anioEscolarId,
                            'docente_id' => $docenteId,
                            'area_formacion_id' => $materiaId,
                            'seccion_id' => $seccionId,
                        ]);
                        $cargaAcademicaId = $carga->id;
                    }

                    // Validar capacidad del aula si se asigna
                    if ($aulaId && $seccionId) {
                        $aula = $aulasPorId->get($aulaId);
                        $seccion = $seccionesPorId->get($seccionId);

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
                        'carga_academica_id' => $cargaAcademicaId,
                        'aula_id' => $aulaId,
                        'dia' => $diaVal,
                        'bloque' => $bloqueVal,
                        'dia_id' => $diaVal,
                        'bloque_id' => $bloqueVal,
                        'fijada' => !empty($asignacion['fijada']),
                        'conflicto_manual' => false,
                    ]);
                } catch (\Illuminate\Database\QueryException $e) {
                    $msg = ($e->getCode() == 23000 || str_contains($e->getMessage(), 'unique_carga_bloque') || str_contains($e->getMessage(), 'UNIQUE constraint failed'))
                        ? 'La clase ya está asignada en ese día y bloque'
                        : $e->getMessage();
                    $errores[] = [
                        'asignacion' => $asignacion,
                        'error' => $msg
                    ];
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
