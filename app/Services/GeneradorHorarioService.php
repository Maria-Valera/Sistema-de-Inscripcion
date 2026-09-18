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
    public function PreparasDatos(?int $gradoId = null, ?int $areaFormacionId = null): Collection
    {
        //TRAEMOS TODOS LAS RELACIONES DE DOCENTES, AREAS, GRADOS Y SECCIONES
        $query = DocenteAreaGrado::with([
            'detalleDocenteEstudio.docente.persona',
            'areaEstudios.areaFormacion.aula', 
            'grado',    
            'seccion'
        ]);
        
         // Filtrar por grado y area de formacion
            if ($gradoId !== null) {
                $query->where('grado_id', $gradoId);
            }

            if ($areaFormacionId !== null) {
                // Filtramos a través de la relación areaEstudios (AreaEstudioRealizado)
                $query->whereHas('areaEstudios', function ($q) use ($areaFormacionId) {
                    $q->where('area_formacion_id', $areaFormacionId);
                });
            }
        
        $asignaciones = $query->get();
        
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
                'docente_horas_academicas' => $docente?->horas_academicas ?? 36,
                 'area_id' => $area?->areaFormacion?->id,
                'area_nombre' => $area?->areaFormacion?->nombre_area_formacion,
                'area_bloque' => $area?->areaFormacion?->horas_semanales,
                'bloques_max_dia' => $area?->areaFormacion?->bloques_maximos_por_dia ?? 2,
                'dias_max_semana' => $area?->areaFormacion?->dias_max_semana ?? 3,
                'area_aula' => $area?->areaFormacion?->aula,
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

    /**
     * Calcula el máximo de bloques horarios que un docente puede tener
     * 
     * Fórmula: (horas_academicas * minutos_por_hora_academica) / minutos_por_bloque
     * 
     * Valores actuales:
     * - Hora académica: 45 minutos
     * - Bloque horario: 80 minutos
     * 
     * @param int $horasAcademicasSemanales Total de horas académicas semanales del docente
     * @param int $minutosPorHoraAcademica Minutos por hora académica (default: 45)
     * @param int $minutosPorBloque Minutos por bloque horario (default: 80)
     * @return int Número máximo de bloques
     */
    public function calcularMaxBloques(int $horasAcademicasSemanales, int $minutosPorHoraAcademica = 45, int $minutosPorBloque = 80): int
    {
        return (int) floor(($horasAcademicasSemanales * $minutosPorHoraAcademica) / $minutosPorBloque);
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

            if (!$seccionAula) {
                // Si no hay aula asignada específicamente, buscar cualquier aula regular disponible
                $aulaRegular = Aula::where('tipo_aula', 'Aula Regular')
                    ->where('status', true)
                    ->first();
                
                if ($aulaRegular && !$this->validarCapacidadAula($aulaRegular->id_aula, $seccionId)) {
                    return null;
                }
                
                return $aulaRegular?->id_aula;
            }

            // Validar capacidad del aula asignada a la sección
            if ($seccionAula && !$this->validarCapacidadAula($seccionAula->id_aula, $seccionId)) {
                return null;
            }

            return $seccionAula?->id_aula;
        }

    public function verificarAulaEspecialLibre(int $aulaId, int $diaId, int $bloqueId, array $aulaEspecialOcupada): bool
    {
        $clave = $aulaId . '_' . $diaId . '_' . $bloqueId;
        return !isset($aulaEspecialOcupada[$clave]);
    }

    public function buscarAulaEspecialLibre(string $tipoAula, int $diaId, int $bloqueId, array $aulaEspecialOcupada, int $seccionId): ?int
    {
        $aulas = Aula::where('tipo_aula', $tipoAula)
            ->where('status', true)
            ->get();

        foreach ($aulas as $aula) {
            $clave = $aula->id_aula . '_' . $diaId . '_' . $bloqueId;
            if (!isset($aulaEspecialOcupada[$clave])) {
                // Validar capacidad del aula
                if ($this->validarCapacidadAula($aula->id_aula, $seccionId)) {
                    return $aula->id_aula;
                }
            }
        }

        return null;
    }

    public function asignarClase(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente, array &$bloquesPorDiaGlobal = []): array
        {
            $resultado = ['asignaciones' => [], 'conflicto' => null];
            $bloquesAsignados = 0;
            $docenteId = $clase['docente_id'];
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;

            // Inicializar contador de bloques para este docente si no existe
            if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
                $matrices['bloquesPorDocente'][$docenteId] = 0;
            }

            // Determinar si esta materia necesita aula especializada (usando datos precargados)
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';

            while ($bloquesAsignados < $clase['area_bloque']) {
                $espacioEncontrado = false;

                for ($dia = 1; $dia <= $totalDias && !$espacioEncontrado; $dia++) {
                    for ($bloque = 1; $bloque <= $totalBloques && !$espacioEncontrado; $bloque++) {

                        // Validar límite de bloques por día para esta materia (usando array de bloques usados)
                        $claveGlobal = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
                        $bloquesUsadosHoy = $bloquesPorDiaGlobal[$claveGlobal] ?? [];
                        if (count($bloquesUsadosHoy) >= $maxBloquesPorDia) continue;

                        // Si ya hay bloques asignados ese día, el nuevo bloque debe ser adyacente
                        if (!empty($bloquesUsadosHoy)) {
                            $minBloque = min($bloquesUsadosHoy);
                            $maxBloque = max($bloquesUsadosHoy);
                            if ($bloque !== $minBloque - 1 && $bloque !== $maxBloque + 1) {
                                continue; // no es adyacente, no cumple continuidad, saltar
                            }
                        }

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
                                $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $clase['seccion_id']
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

                        // Agregar bloque al array global de bloques por día para esta materia
                        $bloquesPorDiaGlobal[$claveGlobal][] = $bloque;

                        $bloquesAsignados++;
                        $espacioEncontrado = true;
                    }
                }

                if (!$espacioEncontrado) {
                    $resultado['conflicto'] = [
                        'docente_id' => $docenteId,
                        'docente_nombre' => $clase['docente_nombre'],
                        'materia_id' => $clase['area_id'],
                        'materia_nombre' => $clase['area_nombre'],
                        'seccion_id' => $clase['seccion_id'],
                        'seccion_nombre' => $clase['seccion_nombre'],
                        'bloques_pendientes' => $clase['area_bloque'] - $bloquesAsignados,
                    ];
                    break;
                }
            }

            return $resultado;
        }

        public function asignarUnBloque(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente, array &$bloquesPorDiaGlobal = []): ?array
        {
            $docenteId = $clase['docente_id'];
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

            // Contar días ya usados para esta materia+sección
            $diasUsados = 0;
            for ($d = 1; $d <= $totalDias; $d++) {
                $claveDiaCheck = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $d;
                if (!empty($bloquesPorDiaGlobal[$claveDiaCheck])) {
                    $diasUsados++;
                }
            }

            // Inicializar contador de bloques para este docente si no existe
            if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
                $matrices['bloquesPorDocente'][$docenteId] = 0;
            }

            // Determinar si esta materia necesita aula especializada (usando datos precargados)
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';

            // Buscar UN solo espacio libre
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {

                    // Validar límite de bloques por día para esta materia (usando array de bloques usados)
                    $claveGlobal = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
                    $bloquesUsadosHoy = $bloquesPorDiaGlobal[$claveGlobal] ?? [];

                    // Si este día todavía no se usó para esta materia+sección, y ya se alcanzó el máximo de días permitidos, no abrir un día nuevo
                    if (empty($bloquesUsadosHoy) && $diasUsados >= $diasMaxSemana) {
                        continue;
                    }

                    if (count($bloquesUsadosHoy) >= $maxBloquesPorDia) continue;

                    // Si ya hay bloques asignados ese día, el nuevo bloque debe ser adyacente
                    if (!empty($bloquesUsadosHoy)) {
                        $minBloque = min($bloquesUsadosHoy);
                        $maxBloque = max($bloquesUsadosHoy);
                        if ($bloque !== $minBloque - 1 && $bloque !== $maxBloque + 1) {
                            continue; // no es adyacente, no cumple continuidad, saltar
                        }
                    }

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
                            $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $clase['seccion_id']
                        );
                        if ($aulaLibre === null) continue;
                        $aulaAsignada = $aulaLibre;
                    } else {
                        $aulaAsignada = $this->obtenerAulaNormal($clase['seccion_id']);
                        if ($aulaAsignada === null) continue;
                    }

                    // Todas las validaciones pasaron: asignar UN bloque
                    $asignacion = [
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

                    // Agregar bloque al array global de bloques por día para esta materia
                    $bloquesPorDiaGlobal[$claveGlobal][] = $bloque;

                    return $asignacion;
                }
            }

            // No se encontró espacio libre
            return null;
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
            int $totalBloques,
            int $anioEscolar
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

            // Obtener disponibilidad actual del docente (no disponibilidad) filtrando por año escolar
            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->get();
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
            int $totalBloques,
            int $anioEscolar
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

            // Obtener disponibilidad actual del docente (no disponibilidad) filtrando por año escolar
            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->get();
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

                // Usar default para max bloques del otro docente (se podría mejorar pasando datos precargados)
                $maxBloquesOtroDocente = $this->calcularMaxBloques(36);

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
                // Contar bloques actuales excluyendo las asignaciones que se van a intercambiar
                $bloquesDocenteActual = 0;
                $bloquesOtroDocente = 0;
                
                foreach ($asignacionesExistentes as $asignacion) {
                    if ($asignacion['docente_id'] == $docenteId &&
                        !($asignacion['docente_id'] == $docenteId &&
                          $asignacion['seccion_id'] == $seccionId &&
                          $asignacion['dia_id'] == $diaActual &&
                          $asignacion['bloque_id'] == $bloqueActual)) {
                        $bloquesDocenteActual++;
                    }
                    if ($asignacion['docente_id'] == $otroDocenteId &&
                        !($asignacion['docente_id'] == $otroDocenteId &&
                          $asignacion['seccion_id'] == $otraSeccionId &&
                          $asignacion['dia_id'] == $otroDia &&
                          $asignacion['bloque_id'] == $otroBloque)) {
                        $bloquesOtroDocente++;
                    }
                }

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
            // Usar default para max bloques (se podría mejorar pasando datos precargados)
            $maxBloquesDocente = $this->calcularMaxBloques(36);

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
                $totalBloques,
                $anioEscolar
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
                $totalBloques,
                $anioEscolar
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

        public function generar(int $anioEscolar, int $totalDias, int $totalBloques, ?int $gradoId = null, ?int $areaFormacionId = null): array
        {
            return \DB::transaction(function() use ($anioEscolar, $totalDias, $totalBloques, $gradoId, $areaFormacionId) {
                // Limpiar asignaciones anteriores del mismo año escolar
                HorarioAsignacion::where('anio_escolar_id', $anioEscolar)->delete();

                $clases = $this->PreparasDatos($gradoId, $areaFormacionId);
                $matrices = $this->InicializarMatrices($anioEscolar);
                $clasesOrdenadas = $this->ordenarClasesPendientes($clases, $matrices['docenteOcupado'], $totalDias, $totalBloques);

                $todasLasAsignaciones = [];
                $todosLosConflictos = [];
                
                // Contador global de bloques por día para cada materia
                $bloquesPorDiaGlobal = [];

                // Convertir a array mutable y agregar campo 'bloques_restantes'
                $clasesPendientes = [];
                foreach ($clasesOrdenadas as $clase) {
                    $claseArray = $clase;
                    $claseArray['bloques_restantes'] = $clase['area_bloque'];
                    $clasesPendientes[] = $claseArray;
                }

                // Bucle round-robin: asignar un bloque por clase en cada ronda
                do {
                    $huboAvanceEnRonda = false;

                    foreach ($clasesPendientes as &$clase) {
                        // Solo procesar clases que aún necesitan bloques
                        if ($clase['bloques_restantes'] <= 0) {
                            continue;
                        }

                        // Usar datos precargados del docente
                        $maxBloques = $this->calcularMaxBloques($clase['docente_horas_academicas'] ?? 36);

                        // Intentar asignar UN bloque
                        $asignacion = $this->asignarUnBloque($clase, $matrices, $totalDias, $totalBloques, $maxBloques, $bloquesPorDiaGlobal);

                        if ($asignacion !== null) {
                            // Guardar asignación en la base de datos
                            $aulaId = $asignacion['aula_id'] > 0 ? $asignacion['aula_id'] : null;
                            
                            HorarioAsignacion::create([
                                'anio_escolar_id' => $anioEscolar,
                                'docente_id' => $asignacion['docente_id'],
                                'materia_id' => $asignacion['materia_id'],
                                'seccion_id' => $asignacion['seccion_id'],
                                'aula_id' => $aulaId,
                                'dia_id' => $asignacion['dia_id'],
                                'bloque_id' => $asignacion['bloque_id'],
                                'conflicto_manual' => false,
                            ]);

                            $todasLasAsignaciones[] = $asignacion;
                            $clase['bloques_restantes']--;
                            $huboAvanceEnRonda = true;
                        }
                    }
                    unset($clase); // Romper la referencia

                } while ($huboAvanceEnRonda && $this->hayClasesPendientes($clasesPendientes));

                // Generar conflictos para clases con bloques restantes
                foreach ($clasesPendientes as $clase) {
                    if ($clase['bloques_restantes'] > 0) {
                        // Determinar el tipo de conflicto y detalles
                        $tipoConflicto = $this->determinarTipoConflicto($clase, $matrices, $totalDias, $totalBloques, $bloquesPorDiaGlobal);
                        
                        // Identificar días/bloques específicos donde falló la asignación
                        $diasBloquesFallidos = $this->identificarEspaciosFallidos($clase, $matrices, $totalDias, $totalBloques, $bloquesPorDiaGlobal);
                        
                        $todosLosConflictos[] = [
                            'docente_id' => $clase['docente_id'],
                            'docente_nombre' => $clase['docente_nombre'],
                            'materia_id' => $clase['area_id'],
                            'materia_nombre' => $clase['area_nombre'],
                            'seccion_id' => $clase['seccion_id'],
                            'seccion_nombre' => $clase['seccion_nombre'],
                            'bloques_pendientes' => $clase['bloques_restantes'],
                            'tipo_conflicto' => $tipoConflicto['tipo'],
                            'motivo' => $tipoConflicto['motivo'],
                            'sugerencias' => $tipoConflicto['sugerencias'],
                            'dias_bloques_fallidos' => $diasBloquesFallidos,
                        ];
                    }
                }

                return [
                    'asignaciones' => $todasLasAsignaciones,
                    'conflictos' => $todosLosConflictos,
                ];
            });
        }

        private function hayClasesPendientes(array $clasesPendientes): bool
        {
            foreach ($clasesPendientes as $clase) {
                if ($clase['bloques_restantes'] > 0) {
                    return true;
                }
            }
            return false;
        }

        private function determinarTipoConflicto(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

            // Contar días ya usados para esta materia+sección
            $diasUsados = 0;
            for ($d = 1; $d <= $totalDias; $d++) {
                $claveDiaCheck = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $d;
                if (!empty($bloquesPorDiaGlobal[$claveDiaCheck])) {
                    $diasUsados++;
                }
            }

            // Contar espacios libres por tipo de restricción
            $espaciosLibresDocente = 0;
            $espaciosLibresSeccion = 0;
            $espaciosLibresAulaEspecial = 0;
            $espaciosLibresAulaRegular = 0;
            $espaciosLibresPorLimiteDia = 0;
            $espaciosLibresPorLimiteDiasSemana = 0;
            
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $claveGlobal = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
                $bloquesHoy = count($bloquesPorDiaGlobal[$claveGlobal] ?? []);
                $diaYaUsado = !empty($bloquesPorDiaGlobal[$claveGlobal]);

                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;

                    // Verificar si el espacio está libre para el docente
                    if (!isset($matrices['docenteOcupado'][$claveDocente])) {
                        $espaciosLibresDocente++;
                    }

                    // Verificar si el espacio está libre para la sección
                    if (!isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $espaciosLibresSeccion++;
                    }

                    // Verificar si el espacio respeta el límite por día
                    if ($bloquesHoy < $maxBloquesPorDia) {
                        $espaciosLibresPorLimiteDia++;
                    }

                    // Verificar si el espacio respeta el límite de días por semana
                    if (!$diaYaUsado || $diasUsados < $diasMaxSemana) {
                        $espaciosLibresPorLimiteDiasSemana++;
                    }
                    
                    // Verificar disponibilidad de aulas
                    if ($esEspecializada) {
                        $aulaLibre = $this->buscarAulaEspecialLibre(
                            $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $seccionId
                        );
                        if ($aulaLibre !== null) {
                            $espaciosLibresAulaEspecial++;
                        }
                    } else {
                        $aulaAsignada = $this->obtenerAulaNormal($seccionId);
                        if ($aulaAsignada !== null) {
                            $espaciosLibresAulaRegular++;
                        }
                    }
                }
            }
            
            // Determinar el tipo de conflicto
            if ($esEspecializada && $espaciosLibresAulaEspecial === 0) {
                return [
                    'tipo' => 'aula_especializada',
                    'motivo' => 'No hay aulas especializadas disponibles para esta materia',
                    'sugerencias' => [
                        'Considerar asignar más aulas del tipo ' . $aulaFijaMateria->tipo_aula,
                        'Reducir la frecuencia de esta materia',
                        'Programar en horarios donde estas aulas estén disponibles'
                    ]
                ];
            }
            
            if (!$esEspecializada && $espaciosLibresAulaRegular === 0) {
                return [
                    'tipo' => 'aula_regular',
                    'motivo' => 'La sección no tiene aula regular asignada',
                    'sugerencias' => [
                        'Asignar un aula regular a la sección',
                        'Verificar la configuración de sección-aula'
                    ]
                ];
            }
            
            if ($espaciosLibresPorLimiteDia === 0) {
                return [
                    'tipo' => 'limite_dia',
                    'motivo' => 'La materia excede el límite de bloques por día (' . $maxBloquesPorDia . ' bloques)',
                    'sugerencias' => [
                        'Aumentar el límite de bloques por día para esta materia',
                        'Distribuir la materia en más días diferentes'
                    ]
                ];
            }

            if ($espaciosLibresPorLimiteDiasSemana === 0) {
                return [
                    'tipo' => 'limite_dias_semana',
                    'motivo' => 'La materia excede el límite de días por semana (' . $diasMaxSemana . ' días)',
                    'sugerencias' => [
                        'Aumentar el límite de días por semana para esta materia',
                        'Reducir la frecuencia de esta materia',
                        'Revisar la distribución de bloques en días ya asignados'
                    ]
                ];
            }
            
            if ($espaciosLibresDocente === 0) {
                return [
                    'tipo' => 'docente_saturado',
                    'motivo' => 'El docente ya tiene su máximo de bloques asignados',
                    'sugerencias' => [
                        'Aumentar el límite de bloques del docente',
                        'Asignar este curso a otro docente',
                        'Reducir la carga académica de este docente'
                    ]
                ];
            }
            
            if ($espaciosLibresSeccion === 0) {
                return [
                    'tipo' => 'seccion_saturada',
                    'motivo' => 'La sección ya tiene todos sus bloques ocupados',
                    'sugerencias' => [
                        'Revisar la distribución de materias de esta sección',
                        'Considerar reducir bloques de otras materias'
                    ]
                ];
            }
            
            if ($espaciosLibresDocente < $clase['bloques_restantes']) {
                return [
                    'tipo' => 'combinacion_restricciones',
                    'motivo' => 'Combinación de restricciones que limitan la disponibilidad',
                    'sugerencias' => [
                        'Ajustar la distribución horaria',
                        'Considerar intercambios con otras asignaciones'
                    ]
                ];
            }
            
            return [
                'tipo' => 'desconocido',
                'motivo' => 'No se pudo determinar la causa específica',
                'sugerencias' => [
                    'Revisar manualmente la disponibilidad',
                    'Contactar al administrador del sistema'
                ]
            ];
        }

        private function identificarEspaciosFallidos(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

            // Contar días ya usados para esta materia+sección
            $diasUsados = 0;
            for ($d = 1; $d <= $totalDias; $d++) {
                $claveDiaCheck = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $d;
                if (!empty($bloquesPorDiaGlobal[$claveDiaCheck])) {
                    $diasUsados++;
                }
            }
            
            $diasSemana = [
                1 => 'Lunes',
                2 => 'Martes',
                3 => 'Miércoles',
                4 => 'Jueves',
                5 => 'Viernes'
            ];
            
            $espaciosFallidos = [];
            
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $claveGlobal = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
                $bloquesHoy = count($bloquesPorDiaGlobal[$claveGlobal] ?? []);
                $diaYaUsado = !empty($bloquesPorDiaGlobal[$claveGlobal]);

                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;

                    $razones = [];

                    // Verificar disponibilidad docente
                    if (isset($matrices['docenteOcupado'][$claveDocente])) {
                        $razones[] = 'docente ocupado';
                    }

                    // Verificar disponibilidad sección
                    if (isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $razones[] = 'sección ocupada';
                    }

                    // Verificar límite por día
                    if ($bloquesHoy >= $maxBloquesPorDia) {
                        $razones[] = 'límite diario alcanzado';
                    }

                    // Verificar límite de días por semana
                    if (!$diaYaUsado && $diasUsados >= $diasMaxSemana) {
                        $razones[] = 'límite de días semanales alcanzado';
                    }

                    // Verificar continuidad de bloques
                    $bloquesUsadosHoy = $bloquesPorDiaGlobal[$claveGlobal] ?? [];
                    if (!empty($bloquesUsadosHoy)) {
                        $minBloque = min($bloquesUsadosHoy);
                        $maxBloque = max($bloquesUsadosHoy);
                        if ($bloque !== $minBloque - 1 && $bloque !== $maxBloque + 1) {
                            $razones[] = 'no cumple continuidad (no es adyacente)';
                        }
                    }

                    // Verificar disponibilidad de aula
                    if ($esEspecializada) {
                        $aulaLibre = $this->buscarAulaEspecialLibre(
                            $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $seccionId
                        );
                        if ($aulaLibre === null) {
                            $razones[] = 'aula especializada no disponible';
                        }
                    } else {
                        $aulaAsignada = $this->obtenerAulaNormal($seccionId);
                        if ($aulaAsignada === null) {
                            $razones[] = 'aula regular no disponible';
                        }
                    }
                    
                    // Si hay razones por las que no se pudo asignar, registrar este espacio
                    if (!empty($razones)) {
                        $espaciosFallidos[] = [
                            'dia' => $diasSemana[$dia] ?? "Día $dia",
                            'bloque' => $bloque,
                            'razones' => $razones
                        ];
                    }
                }
            }
            
            return $espaciosFallidos;
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
