<?php

namespace App\Services;

use App\Models\DocenteAreaGrado;
use App\Models\DocenteNoDisponibilidad;
use App\Models\AreaFormacion;
use App\Models\seccion_aula;
use App\Models\Docente;
use Illuminate\Support\Collection;

Class GeneradorHorarioService
{
    public function PreparasDatos(): Collection
    {
        //TRAEMOS TODOS LAS RELACIONES DE DOCENTES, AREAS, GRADOS Y SECCIONES
        $asignaciones = DocenteAreaGrado::with([
            'detalleDocenteEstudio.docente.persona',
            'areaEstudios.areaFormacion', 
            'grado',    
            'seccion'
        ])->get();
        
        //Mapeamos todos los datos necesarios para el generador de horario
        return $asignaciones->map(function ($fila){
            $detalle=$fila->detalleDocenteEstudio;
            $docente= $detalle?->docente; //Variable para traer personas a travez de docente
            $area= $fila->areaEstudios;
            $grado= $fila->grado;
            $seccion= $fila->seccion;
            
            return [
                'docente_id' => $docente?->id,
                'docente_nombre' => trim($docente?->persona?->primer_nombre . ' ' . $docente?->persona?->primer_apellido),
                 'area_id' => $area?->areaFormacion?->id, 
                'area_nombre' => $area?->areaFormacion?->nombre_area_formacion,
                'area_bloque' => $area?->areaFormacion?->horas_semanales,
                'grado_id' => $grado?->id,
                'grado_nombre' => $grado?->numero_grado,
                'seccion_id' => $seccion?->id,
                'seccion_nombre' => $seccion?->nombre
            ];
        });
    
    }

    public function InicializarMatrices(int $anioEscolar): array
    {
        //Creamos los arrays vacios para guardar los datos
        $docenteOcupado = [];
        $seccionOcupado = [];
        $aulaEspecialOcupada = [];
        $bloquesPorDocente = [];

        //Obtenemos el año escolar de la no disponibilidad del docente en ese momento
        $noDisponible = DocenteNoDisponibilidad::where('anio_escolar_id', $anioEscolar)->get();
        //Recorremos todos los datos y creamos una clave compuesta tomando la variable docente
        foreach ($noDisponible as $nd) {
            $clave = $nd->docente_id . '_' . $nd->dias_semana_id . '_' . $nd->id_bloque_hora;
            $docenteOcupado[$clave] = true;
        }

        return [
            'docenteOcupado' => $docenteOcupado,
            'seccionOcupado' => $seccionOcupado,
            'aulaEspecialOcupada' => $aulaEspecialOcupada,
            'bloquesPorDocente' => $bloquesPorDocente,
        ];
    }

    public function calcularMaxBloques(int $horasAcademicasSemanales): int
    {
        return (int) floor(($horasAcademicasSemanales * 45) / 80);
    }

    public function calcularDisponibilidad(int $docenteId, array $docenteOcupado, int $totalDias, int $totalBloques): int
        {
            $totalBloquesSemana = $totalDias * $totalBloques;
            $bloquesBloqueados = 0;

            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $clave = $docenteId . '_' . $dia . '_' . $bloque;

                    if (isset($docenteOcupado[$clave])) {
                        $bloquesBloqueados++;
                    }
                }
            }

            return $totalBloquesSemana - $bloquesBloqueados;
        }
    public function ordenarClasesPendientes(Collection $clasesPendientes, array $docenteOcupado, int $totalDias, int $totalBloques): Collection
    {
        $clasesConDisponibilidad = $clasesPendientes->map(function ($clase) use ($docenteOcupado, $totalDias, $totalBloques) {
            $clase['disponibilidad_docente'] = $this->calcularDisponibilidad(
                $clase['docente_id'],
                $docenteOcupado,
                $totalDias,
                $totalBloques
            );
            return $clase;
        });

        return $clasesConDisponibilidad->sortBy([
            ['disponibilidad_docente', 'asc'],
            ['area_bloque', 'desc'],
        ])->values();
    }

    public function obtenerAulaNormal(int $seccionId): ?int
        {
            $seccionAula = seccion_aula::where('id_seccion', $seccionId)
                ->where('status', true)
                ->first();

            return $seccionAula?->id_aula;
        }

    public function verificarAulaEspecialLibre(int $aulaId, int $diaId, int $bloqueId, array $aulaEspecialOcupada): bool
    {
        $clave = $aulaId . '_' . $diaId . '_' . $bloqueId;
        return !isset($aulaEspecialOcupada[$clave]);
    }

    public function asignarClase(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente): array
        {
            $resultado = ['asignaciones' => [], 'conflicto' => null];
            $bloquesAsignados = 0;
            $docenteId = $clase['docente_id'];

            // Inicializar contador de bloques para este docente si no existe
            if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
                $matrices['bloquesPorDocente'][$docenteId] = 0;
            }

            // Determinar si esta materia necesita aula especializada
            $areaFormacion = AreaFormacion::find($clase['area_id']);
            $esEspecializada = false;

            while ($bloquesAsignados < $clase['area_bloque']) {
                $espacioEncontrado = false;

                for ($dia = 1; $dia <= $totalDias && !$espacioEncontrado; $dia++) {
                    for ($bloque = 1; $bloque <= $totalBloques && !$espacioEncontrado; $bloque++) {

                        $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                        $claveSeccion = $clase['seccion_id'] . '_' . $dia . '_' . $bloque;

                        // Verificar disponibilidad
                        if (isset($matrices['docenteOcupado'][$claveDocente])) continue;
                        if (isset($matrices['seccionOcupado'][$claveSeccion])) continue;

                        // Verificar que el docente no exceda su máximo de bloques totales
                        if ($matrices['bloquesPorDocente'][$docenteId] >= $maxBloquesDocente) continue;

                        $aulaAsignada = null;

                        if ($esEspecializada) {
                            $aulaLibre = $this->buscarAulaEspecialLibre(
                                '', $dia, $bloque, $matrices['aulaEspecialOcupada']
                            );
                            if ($aulaLibre === null) continue;
                            $aulaAsignada = $aulaLibre;
                        } else {
                            $aulaAsignada = $this->obtenerAulaNormal($clase['seccion_id']);
                            if ($aulaAsignada === null) continue;
                        }

                        // Todas las validaciones pasaron: asignar
                        $resultado['asignaciones'][] = [
                            'docente_id' => $docenteId,
                            'materia_id' => $clase['area_id'],
                            'seccion_id' => $clase['seccion_id'],
                            'dia_id' => $dia,
                            'bloque_id' => $bloque,
                            'aula_id' => $aulaAsignada,
                        ];

                        $matrices['docenteOcupado'][$claveDocente] = true;
                        $matrices['seccionOcupado'][$claveSeccion] = true;
                        $matrices['bloquesPorDocente'][$docenteId]++;

                        if ($esEspecializada) {
                            $matrices['aulaEspecialOcupada'][$aulaAsignada . '_' . $dia . '_' . $bloque] = true;
                        }

                        $bloquesAsignados++;
                        $espacioEncontrado = true;
                    }
                }

                if (!$espacioEncontrado) {
                    $resultado['conflicto'] = [
                        'docente_id' => $docenteId,
                        'materia_id' => $clase['area_id'],
                        'seccion_id' => $clase['seccion_id'],
                        'bloques_pendientes' => $clase['area_bloque'] - $bloquesAsignados,
                    ];
                    break;
                }
            }

            return $resultado;
        }

        public function generar(int $anioEscolar, int $totalDias, int $totalBloques): array
        {
            $clases = $this->PreparasDatos();
            $matrices = $this->InicializarMatrices($anioEscolar);
            $clasesOrdenadas = $this->ordenarClasesPendientes($clases, $matrices['docenteOcupado'], $totalDias, $totalBloques);

            $todasLasAsignaciones = [];
            $todosLosConflictos = [];

            foreach ($clasesOrdenadas as $clase) {
                $docente = Docente::find($clase['docente_id']);

                if (!$docente) {
                    $todosLosConflictos[] = [
                        'docente_id' => $clase['docente_id'],
                        'materia_id' => $clase['area_id'],
                        'seccion_id' => $clase['seccion_id'],
                        'error' => 'Docente no encontrado',
                    ];
                    continue;
                }

                $maxBloques = $this->calcularMaxBloques($docente->horas_academicas ?? 36);
                $claseArray = $clase;
                $resultado = $this->asignarClase($claseArray, $matrices, $totalDias, $totalBloques, $maxBloques);

                $todasLasAsignaciones = array_merge($todasLasAsignaciones, $resultado['asignaciones']);

                if ($resultado['conflicto']) {
                    $todosLosConflictos[] = $resultado['conflicto'];
                }
            }

            return [
                'asignaciones' => $todasLasAsignaciones,
                'conflictos' => $todosLosConflictos,
            ];
        }
}
