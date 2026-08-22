<?php

namespace App\Services;

use App\Models\DocenteAreaGrado;
use App\Models\DocenteNoDisponibilidad;
use App\Models\AreaFormacion;
use App\Models\seccion_aula;
use App\Models\Docente;
use App\Models\HorarioAsignacion;
use App\Models\Aula;
use App\Models\Seccion;
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

    public function buscarAulaEspecialLibre(string $tipoAula, int $diaId, int $bloqueId, array $aulaEspecialOcupada): ?int
    {
        $aulas = Aula::where('tipo_aula', $tipoAula)
            ->where('status', true)
            ->get();

        foreach ($aulas as $aula) {
            $clave = $aula->id_aula . '_' . $diaId . '_' . $bloqueId;
            if (!isset($aulaEspecialOcupada[$clave])) {
                return $aula->id_aula;
            }
        }

        return null;
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
            $aulaFijaMateria = $areaFormacion?->aula;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';

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
                                $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada']
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

        public function buscarHuecoLibre(
            int $docenteId,
            int $seccionId,
            int $aulaId,
            int $diaActual,
            int $bloqueActual,
            array $asignacionesExistentes,
            int $maxBloquesDocente,
            int $totalDias,
            int $totalBloques
        ): ?array {
            // Construir matrices de ocupación desde las asignaciones existentes
            $docenteOcupado = [];
            $seccionOcupado = [];
            $bloquesPorDocente = [];

            foreach ($asignacionesExistentes as $asignacion) {
                // Excluir la asignación actual del conteo
                if ($asignacion['docente_id'] == $docenteId &&
                    $asignacion['seccion_id'] == $seccionId &&
                    $asignacion['dia_id'] == $diaActual &&
                    $asignacion['bloque_id'] == $bloqueActual) {
                    continue;
                }

                $claveDocente = $asignacion['docente_id'] . '_' . $asignacion['dia_id'] . '_' . $asignacion['bloque_id'];
                $claveSeccion = $asignacion['seccion_id'] . '_' . $asignacion['dia_id'] . '_' . $asignacion['bloque_id'];

                $docenteOcupado[$claveDocente] = true;
                $seccionOcupado[$claveSeccion] = true;

                // Contar bloques por docente
                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            // Obtener disponibilidad actual del docente (no disponibilidad)
            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)->get();
            foreach ($noDisponible as $nd) {
                $clave = $nd->docente_id . '_' . $nd->dias_semana_id . '_' . $nd->id_bloque_hora;
                $docenteOcupado[$clave] = true;
            }

            // Buscar hueco libre diferente al actual
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    // Saltar la posición actual
                    if ($dia == $diaActual && $bloque == $bloqueActual) {
                        continue;
                    }

                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;

                    // Verificar disponibilidad
                    if (isset($docenteOcupado[$claveDocente])) continue;
                    if (isset($seccionOcupado[$claveSeccion])) continue;

                    // Verificar que el docente no exceda su máximo de bloques
                    $bloquesActuales = $bloquesPorDocente[$docenteId] ?? 0;
                    if ($bloquesActuales >= $maxBloquesDocente) continue;

                    // Hueco libre encontrado
                    return [
                        'dia_id' => $dia,
                        'bloque_id' => $bloque,
                        'aula_id' => $aulaId,
                    ];
                }
            }

            // No se encontró hueco libre
            return null;
        }

        public function intercambiarConOtraClase(
            int $docenteId,
            int $seccionId,
            int $aulaId,
            int $diaActual,
            int $bloqueActual,
            array $asignacionesExistentes,
            int $maxBloquesDocente,
            int $totalDias,
            int $totalBloques
        ): ?array {
            // Construir matrices de ocupación desde las asignaciones existentes
            $docenteOcupado = [];
            $seccionOcupado = [];
            $bloquesPorDocente = [];

            foreach ($asignacionesExistentes as $asignacion) {
                // Excluir la asignación actual del conteo
                if ($asignacion['docente_id'] == $docenteId &&
                    $asignacion['seccion_id'] == $seccionId &&
                    $asignacion['dia_id'] == $diaActual &&
                    $asignacion['bloque_id'] == $bloqueActual) {
                    continue;
                }

                $claveDocente = $asignacion['docente_id'] . '_' . $asignacion['dia_id'] . '_' . $asignacion['bloque_id'];
                $claveSeccion = $asignacion['seccion_id'] . '_' . $asignacion['dia_id'] . '_' . $asignacion['bloque_id'];

                $docenteOcupado[$claveDocente] = true;
                $seccionOcupado[$claveSeccion] = true;

                // Contar bloques por docente
                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            // Obtener disponibilidad actual del docente (no disponibilidad)
            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)->get();
            foreach ($noDisponible as $nd) {
                $clave = $nd->docente_id . '_' . $nd->dias_semana_id . '_' . $nd->id_bloque_hora;
                $docenteOcupado[$clave] = true;
            }

            // Buscar otra clase para intercambiar
            foreach ($asignacionesExistentes as $index => $otraAsignacion) {
                // No intercambiar consigo mismo
                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId &&
                    $otraAsignacion['dia_id'] == $diaActual &&
                    $otraAsignacion['bloque_id'] == $bloqueActual) {
                    continue;
                }

                // Verificar que la otra clase sea intercambiable
                // (diferente docente y/o diferente sección)
                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId) {
                    continue; // Mismo docente y misma sección, no tiene sentido intercambiar
                }

                $otroDocenteId = $otraAsignacion['docente_id'];
                $otraSeccionId = $otraAsignacion['seccion_id'];
                $otroDia = $otraAsignacion['dia_id'];
                $otroBloque = $otraAsignacion['bloque_id'];
                $otraAula = $otraAsignacion['aula_id'];

                // Verificar disponibilidad del otro docente
                $otroDocente = Docente::find($otroDocenteId);
                if (!$otroDocente) continue;
                $maxBloquesOtroDocente = $this->calcularMaxBloques($otroDocente->horas_academicas ?? 36);

                // Verificar disponibilidad del otro docente en la posición actual
                $claveDocenteActual = $otroDocenteId . '_' . $diaActual . '_' . $bloqueActual;
                if (isset($docenteOcupado[$claveDocenteActual])) continue;

                // Verificar disponibilidad de la otra sección en la posición actual
                $claveSeccionActual = $otraSeccionId . '_' . $diaActual . '_' . $bloqueActual;
                if (isset($seccionOcupado[$claveSeccionActual])) continue;

                // Verificar disponibilidad del docente actual en la posición de la otra clase
                $claveDocenteOtro = $docenteId . '_' . $otroDia . '_' . $otroBloque;
                if (isset($docenteOcupado[$claveDocenteOtro])) continue;

                // Verificar disponibilidad de la sección actual en la posición de la otra clase
                $claveSeccionOtro = $seccionId . '_' . $otroDia . '_' . $otroBloque;
                if (isset($seccionOcupado[$claveSeccionOtro])) continue;

                // Verificar que ambos docentes no excedan su máximo de bloques
                $bloquesDocenteActual = $bloquesPorDocente[$docenteId] ?? 0;
                $bloquesOtroDocente = $bloquesPorDocente[$otroDocenteId] ?? 0;

                if ($bloquesDocenteActual >= $maxBloquesDocente) continue;
                if ($bloquesOtroDocente >= $maxBloquesOtroDocente) continue;

                // Intercambio válido encontrado
                return [
                    'intercambio_valido' => true,
                    'clase_original' => [
                        'docente_id' => $docenteId,
                        'seccion_id' => $seccionId,
                        'aula_id' => $aulaId,
                        'dia_origen' => $diaActual,
                        'bloque_origen' => $bloqueActual,
                        'dia_destino' => $otroDia,
                        'bloque_destino' => $otroBloque,
                        'aula_destino' => $otraAula,
                    ],
                    'clase_intercambio' => [
                        'docente_id' => $otroDocenteId,
                        'seccion_id' => $otraSeccionId,
                        'aula_id' => $otraAula,
                        'dia_origen' => $otroDia,
                        'bloque_origen' => $otroBloque,
                        'dia_destino' => $diaActual,
                        'bloque_destino' => $bloqueActual,
                        'aula_destino' => $aulaId,
                    ],
                ];
            }

            // No se encontró intercambio válido
            return null;
        }

        public function reacomodarClase(
            int $docenteId,
            int $seccionId,
            int $aulaId,
            int $diaActual,
            int $bloqueActual,
            array $asignacionesExistentes,
            int $anioEscolar,
            int $totalDias,
            int $totalBloques
        ): array {
            // Obtener el docente para calcular max bloques
            $docente = Docente::find($docenteId);
            if (!$docente) {
                return [
                    'exito' => false,
                    'nivel' => 'error',
                    'mensaje' => 'Docente no encontrado',
                    'conflicto_manual' => true,
                ];
            }

            $maxBloquesDocente = $this->calcularMaxBloques($docente->horas_academicas ?? 36);

            // NIVEL 1: Buscar hueco libre
            $huecoLibre = $this->buscarHuecoLibre(
                $docenteId,
                $seccionId,
                $aulaId,
                $diaActual,
                $bloqueActual,
                $asignacionesExistentes,
                $maxBloquesDocente,
                $totalDias,
                $totalBloques
            );

            if ($huecoLibre) {
                return [
                    'exito' => true,
                    'nivel' => 1,
                    'mensaje' => 'Hueco libre encontrado',
                    'accion' => 'mover',
                    'nueva_posicion' => $huecoLibre,
                    'conflicto_manual' => false,
                ];
            }

            // NIVEL 2: Intercambio con otra clase
            $intercambio = $this->intercambiarConOtraClase(
                $docenteId,
                $seccionId,
                $aulaId,
                $diaActual,
                $bloqueActual,
                $asignacionesExistentes,
                $maxBloquesDocente,
                $totalDias,
                $totalBloques
            );

            if ($intercambio) {
                return [
                    'exito' => true,
                    'nivel' => 2,
                    'mensaje' => 'Intercambio con otra clase encontrado',
                    'accion' => 'intercambiar',
                    'detalles_intercambio' => $intercambio,
                    'conflicto_manual' => false,
                ];
            }

            // NIVEL 3: Conflicto manual
            return [
                'exito' => false,
                'nivel' => 3,
                'mensaje' => 'No se encontró solución automática - requiere resolución manual',
                'accion' => 'conflicto_manual',
                'conflicto_manual' => true,
                'informacion_conflicto' => [
                    'docente_id' => $docenteId,
                    'seccion_id' => $seccionId,
                    'aula_id' => $aulaId,
                    'dia_actual' => $diaActual,
                    'bloque_actual' => $bloqueActual,
                    'motivo' => 'No hay huecos libres ni intercambios posibles',
                ],
            ];
        }

        public function generar(int $anioEscolar, int $totalDias, int $totalBloques): array
        {
            // Limpiar asignaciones anteriores del mismo año escolar
            HorarioAsignacion::where('anio_escolar_id', $anioEscolar)->delete();

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

                // Guardar asignaciones en la base de datos
                foreach ($resultado['asignaciones'] as $asignacion) {
                    HorarioAsignacion::create([
                        'anio_escolar_id' => $anioEscolar,
                        'docente_id' => $asignacion['docente_id'],
                        'materia_id' => $asignacion['materia_id'],
                        'seccion_id' => $asignacion['seccion_id'],
                        'aula_id' => $asignacion['aula_id'],
                        'dia_id' => $asignacion['dia_id'],
                        'bloque_id' => $asignacion['bloque_id'],
                        'conflicto_manual' => false,
                    ]);
                }

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

        public function validarCapacidadAula(int $aulaId, int $seccionId): bool
        {
            $aula = Aula::find($aulaId);
            $seccion = Seccion::find($seccionId);

            if (!$aula || !$seccion) {
                return false;
            }

            if ($aula->capacidad_maxima === null) {
                return true;
            }

            return $seccion->cantidad_actual <= $aula->capacidad_maxima;
        }
}
