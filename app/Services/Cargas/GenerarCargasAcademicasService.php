<?php

namespace App\Services\Cargas;

use App\Enums\TipoAsignacionNota;
use App\Models\CalendarioAcademico;
use App\Models\CargaAcademica;
use App\Models\DocenteAreaGrado;
use App\Models\GradoSeccion;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class GenerarCargasAcademicasService
{
    /**
     * Genera las cargas académicas del calendario académico dado a partir de docente_area_grados.
     *
     * @param int $calendarioAcademicoId
     * @return array Resumen de la operación
     * @throws InvalidArgumentException|RuntimeException
     */
    public function generar(int $calendarioAcademicoId): array
    {
        $calendario = CalendarioAcademico::with('anioEscolar')->find($calendarioAcademicoId);

        if (!$calendario) {
            throw new InvalidArgumentException("El calendario académico con ID {$calendarioAcademicoId} no existe.");
        }

        // Defensa en profundidad para año de solo lectura
        $anio = $calendario->anioEscolar;
        if (!$anio || !in_array($anio->status, ['Activo', 'Extendido'])) {
            throw new RuntimeException("No se pueden generar cargas académicas en un año escolar de solo lectura o inactivo ({$anio?->status}).");
        }

        $resumen = [
            'creadas' => 0,
            'actualizadas' => 0,
            'omitidas' => 0,
            'duplicados_fusionados' => [],
            'omisiones' => [],
            'errores' => [],
        ];

        DB::transaction(function () use ($calendarioAcademicoId, &$resumen) {
            $plantillas = DocenteAreaGrado::with([
                'detalleDocenteEstudio',
                'areaEstudios',
            ])
            ->where('status', true)
            ->get();

            foreach ($plantillas as $dag) {
                // 1. Resolver docente_id
                $docenteId = $dag->detalleDocenteEstudio?->docente_id;
                if (!$docenteId) {
                    $resumen['omitidas']++;
                    $resumen['omisiones'][] = [
                        'docente_area_grado_id' => $dag->id,
                        'motivo' => "No se pudo resolver el docente_id (detalle_docente_estudios ID: {$dag->docente_estudio_realizado_id} sin docente_id).",
                    ];
                    continue;
                }

                $tipo = $dag->tipo_asignacion;

                if ($tipo === 'grupo_estable') {
                    // Validación fila por fila para grupo_estable
                    if (!$dag->grupo_estable_id) {
                        $resumen['omitidas']++;
                        $resumen['omisiones'][] = [
                            'docente_area_grado_id' => $dag->id,
                            'motivo' => "Tipo de asignación 'grupo_estable' requiere grupo_estable_id no nulo.",
                        ];
                        continue;
                    }

                    $tipoEnum = TipoAsignacionNota::GrupoEstable;
                    $areaFormacionId = null;
                    $gradoSeccionId = null;
                    $grupoEstableId = $dag->grupo_estable_id;
                } else {
                    // Asignación de tipo 'area'
                    if (!$dag->grado_id || !$dag->seccion_id) {
                        $resumen['omitidas']++;
                        $resumen['omisiones'][] = [
                            'docente_area_grado_id' => $dag->id,
                            'motivo' => "Tipo de asignación 'area' requiere grado_id y seccion_id no nulos (grado_id: " . ($dag->grado_id ?? 'NULL') . ", seccion_id: " . ($dag->seccion_id ?? 'NULL') . ").",
                        ];
                        continue;
                    }

                    $areaFormacionId = $dag->areaEstudios?->area_formacion_id;
                    if (!$areaFormacionId) {
                        $resumen['omitidas']++;
                        $resumen['omisiones'][] = [
                            'docente_area_grado_id' => $dag->id,
                            'motivo' => "No se pudo resolver el area_formacion_id (area_estudio_realizados ID: {$dag->area_estudio_realizado_id} sin area_formacion_id).",
                        ];
                        continue;
                    }

                    // firstOrCreate grado_seccion
                    $gradoSeccion = GradoSeccion::firstOrCreate(
                        [
                            'grado_id' => $dag->grado_id,
                            'seccion_id' => $dag->seccion_id,
                        ],
                        [
                            'status' => true,
                        ]
                    );

                    $tipoEnum = TipoAsignacionNota::Area;
                    $gradoSeccionId = $gradoSeccion->id;
                    $grupoEstableId = null;
                }

                // Keys generadas para cotejo
                $areaKey = $areaFormacionId ?? 0;
                $gradoSeccionKey = $gradoSeccionId ?? 0;
                $grupoKey = $grupoEstableId ?? 0;

                // 2. Verificar duplicados por la combinación de 5 columnas
                $existenteCombinacion = CargaAcademica::where('calendario_academico_id', $calendarioAcademicoId)
                    ->where('docente_id', $docenteId)
                    ->where('area_key', $areaKey)
                    ->where('grado_seccion_key', $gradoSeccionKey)
                    ->where('grupo_key', $grupoKey)
                    ->first();

                // 3. Verificar existencia por (calendario_academico_id, docente_area_grado_id)
                $existenteDag = CargaAcademica::where('calendario_academico_id', $calendarioAcademicoId)
                    ->where('docente_area_grado_id', $dag->id)
                    ->first();

                if ($existenteCombinacion && (!$existenteDag || $existenteCombinacion->id !== $existenteDag->id)) {
                    // Registrar duplicado fusionado para no violar el índice único de 5 columnas
                    $resumen['duplicados_fusionados'][] = [
                        'docente_area_grado_id' => $dag->id,
                        'carga_existente_id' => $existenteCombinacion->id,
                        'docente_id' => $docenteId,
                        'area_key' => $areaKey,
                        'grado_seccion_key' => $gradoSeccionKey,
                        'grupo_key' => $grupoKey,
                    ];

                    $existenteCombinacion->update([
                        'activa' => true,
                        'sincronizada_at' => now(),
                    ]);
                    $resumen['actualizadas']++;
                    continue;
                }

                // 4. Si no hay colisión de combinación con otro registro, hacer updateOrCreate
                $registro = CargaAcademica::updateOrCreate(
                    [
                        'calendario_academico_id' => $calendarioAcademicoId,
                        'docente_area_grado_id' => $dag->id,
                    ],
                    [
                        'docente_id' => $docenteId,
                        'tipo' => $tipoEnum,
                        'area_formacion_id' => $areaFormacionId,
                        'grado_seccion_id' => $gradoSeccionId,
                        'grupo_estable_id' => $grupoEstableId,
                        'activa' => true,
                        'sincronizada_at' => now(),
                    ]
                );

                if ($registro->wasRecentlyCreated) {
                    $resumen['creadas']++;
                } else {
                    $resumen['actualizadas']++;
                }
            }
        });

        return $resumen;
    }
}
