<?php

namespace App\Services;

use App\Enums\Dia;
use App\Models\BloqueReservado;
use App\Models\CargaAcademica;
use App\Models\DocenteAreaGrado;
use App\Models\DocenteIndisponibilidad;
use App\Models\AreaFormacion;
use App\Models\AreaFormacionPosicion;
use App\Models\seccion_aula;
use App\Models\Docente;
use App\Models\HorarioAsignacion;
use App\Models\Aula;
use App\Models\Seccion;
use Illuminate\Support\Collection;

Class GeneradorHorarioService
{
    protected array $cacheAulaNormal = [];
    protected array $cacheCapacidadAula = [];
    protected array $cacheAulasEspeciales = [];

    public function limpiarCaches(): void
    {
        $this->cacheAulaNormal = [];
        $this->cacheCapacidadAula = [];
        $this->cacheAulasEspeciales = [];
    }

    public function PreparasDatos(?int $gradoId = null, ?int $areaFormacionId = null, ?int $anioEscolarId = null): Collection
    {
        $query = CargaAcademica::with([
            'docente.persona',
            'areaFormacion.aula',
            'seccion.grado',
        ]);

        if ($anioEscolarId !== null) {
            $query->where('anio_escolar_id', $anioEscolarId);
        }

        //filtro grado y seccion
        if ($gradoId !== null) {
            $query->whereHas('seccion', function ($q) use ($gradoId) {
                $q->where('grado_id', $gradoId);
            });
        }

        //filtro area de formacion
        if ($areaFormacionId !== null) {
            $query->where('area_formacion_id', $areaFormacionId);
        }

        $asignaciones = $query->get();

        return $asignaciones->map(function ($fila) {
            $docente = $fila->docente;
            $area = $fila->areaFormacion;
            $seccion = $fila->seccion;
            $grado = $seccion?->grado;

            return [
                'carga_academica_id' => $fila->id,
                'docente_id' => $docente?->id,
                'docente_nombre' => trim(($docente?->persona?->primer_nombre ?? '') . ' ' . ($docente?->persona?->primer_apellido ?? '')),
                'docente_horas_academicas' => $docente?->horas_academicas ?? 36,
                'area_id' => $area?->id,
                'area_nombre' => $area?->nombre_area_formacion,
                'area_bloque' => $area?->bloques_semanales,
                'area_aula' => $area?->aula,
                'grado_id' => $grado?->id,
                'grado_nombre' => $grado?->numero_grado,
                'seccion_id' => $seccion?->id,
                'seccion_nombre' => $seccion?->nombre,
            ];
        });
    }


    public function InicializarMatrices(int $anioEscolar): array
    {
        //marcar y guardar quien esta ocupado y restricciones de cada docente
        $docenteOcupado = [];
        $seccionOcupado = [];
        $aulaEspecialOcupada = [];
        $bloquesPorDocente = [];
        $reservado = [];
        $materiaDia = [];
        $docenteDia = [];
        $docenteNoPreferente = [];

        //busca los dias y bloques de hora que el docente no puede ser asignado (solo aprobadas y prohibidas)
        $noDisponible = DocenteIndisponibilidad::where('anio_escolar_id', $anioEscolar)
            ->where('estado', 'aprobada')
            ->where('tipo', 'prohibido')
            ->get();
        foreach ($noDisponible as $nd) {
            $dia = $nd->dia instanceof Dia ? $nd->dia->value : (int) $nd->dia;
            $clave = $nd->docente_id . '_' . $dia . '_' . $nd->bloque;
            $docenteOcupado[$clave] = true;
        }

        //busca indisponibilidades no_preferente aprobadas (para costoPosicion en M5/P6)
        $noPreferente = DocenteIndisponibilidad::where('anio_escolar_id', $anioEscolar)
            ->where('estado', 'aprobada')
            ->where('tipo', 'no_preferente')
            ->get();
        foreach ($noPreferente as $np) {
            $dia = $np->dia instanceof Dia ? $np->dia->value : (int) $np->dia;
            $clave = $np->docente_id . '_' . $dia . '_' . $np->bloque;
            $docenteNoPreferente[$clave] = true;
        }

        //bloques reservados (ej. grupos estables)
        $bloquesReservados = BloqueReservado::where('anio_escolar_id', $anioEscolar)->get();
        foreach ($bloquesReservados as $br) {
            $dia = $br->dia instanceof Dia ? $br->dia->value : (int) $br->dia;
            $reservado[$dia . '_' . $br->bloque] = true;
        }

        //posiciones por materia (reglas pedagógicas M8 / P11)
        $posicionesMateria = [];
        $posiciones = AreaFormacionPosicion::all();
        foreach ($posiciones as $pos) {
            $dia = $pos->dia instanceof Dia ? $pos->dia->value : (int) $pos->dia;
            $gradoKey = $pos->grado_id ?? 0;
            $clave = $pos->area_formacion_id . '_' . $gradoKey . '_' . $dia . '_' . $pos->bloque;
            $posicionesMateria[$clave] = $pos->tipo;
        }

        return [
            'docenteOcupado' => $docenteOcupado,
            'seccionOcupado' => $seccionOcupado,
            'aulaEspecialOcupada' => $aulaEspecialOcupada,
            'bloquesPorDocente' => $bloquesPorDocente,
            'reservado' => $reservado,
            'materiaDia' => $materiaDia,
            'docenteDia' => $docenteDia,
            'docenteNoPreferente' => $docenteNoPreferente,
            'posicionesMateria' => $posicionesMateria,
            'rechazos' => [
                'docente' => [],
                'seccion' => [],
                'aula' => [],
                'evaluaciones' => 0,
            ],
        ];
    }

    
    public function calcularMaxBloques(int $horasAcademicasSemanales, int $minutosPorHoraAcademica = 45, int $minutosPorBloque = 80): int
    {
        return min(intdiv($horasAcademicasSemanales, 2), 18);
    }


    //Priorizacion de clases

    public function calcularDisponibilidad(int $docenteId, array $docenteOcupado, int $totalDias, int $totalBloques): int
        {
            $totalBloquesSemana = $totalDias * $totalBloques;
            $bloquesBloqueados = 0;

            //recorre la dispinibilidad semanal del docente y cuenta los huecos libres
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $clave = $docenteId . '_' . $dia . '_' . $bloque;

                    if (isset($docenteOcupado[$clave])) {
                        $bloquesBloqueados++;
                    }
                }
            }
        //resta los bloques no disponibles del total
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
        //ordenamos por los docentes que tienen menos diponibilidad
        return $clasesConDisponibilidad->sortBy([
            ['disponibilidad_docente', 'asc'],
            ['area_bloque', 'desc'],
        ])->values();
    }




    public function obtenerAulaNormal(int $seccionId): ?int
    {
        if (array_key_exists($seccionId, $this->cacheAulaNormal)) {
            return $this->cacheAulaNormal[$seccionId];
        }

        $seccionAula = seccion_aula::where('id_seccion', $seccionId)
            ->where('status', true)
            ->first();

        if (!$seccionAula) {
            $aulaRegular = Aula::where('tipo_aula', Aula::TIPO_REGULAR)
                ->where('status', true)
                ->first();

            if ($aulaRegular && !$this->validarCapacidadAula($aulaRegular->id_aula, $seccionId)) {
                return $this->cacheAulaNormal[$seccionId] = null;
            }

            return $this->cacheAulaNormal[$seccionId] = $aulaRegular?->id_aula;
        }

        if ($seccionAula && !$this->validarCapacidadAula($seccionAula->id_aula, $seccionId)) {
            return $this->cacheAulaNormal[$seccionId] = null;
        }

        return $this->cacheAulaNormal[$seccionId] = $seccionAula?->id_aula;
    }

    //para areas con aulas especializadas como edu
    public function verificarAulaEspecialLibre(int $aulaId, int $diaId, int $bloqueId, array $aulaEspecialOcupada): bool
    {
        $clave = $aulaId . '_' . $diaId . '_' . $bloqueId;
        return !isset($aulaEspecialOcupada[$clave]);
    }

    public function buscarAulaEspecialLibre(string $tipoAula, int $diaId, int $bloqueId, array $aulaEspecialOcupada, int $seccionId): ?int
    {
        if (!isset($this->cacheAulasEspeciales[$tipoAula])) {
            $this->cacheAulasEspeciales[$tipoAula] = Aula::where('tipo_aula', $tipoAula)
                ->where('status', true)
                ->get();
        }

        $aulas = $this->cacheAulasEspeciales[$tipoAula];

        foreach ($aulas as $aula) {
            $clave = $aula->id_aula . '_' . $diaId . '_' . $bloqueId;
            if (!isset($aulaEspecialOcupada[$clave])) {
                if ($this->validarCapacidadAula($aula->id_aula, $seccionId)) {
                    return $aula->id_aula;
                }
            }
        }

        return null;
    }


    /**
     * Calcula el costo de posición para una materia según las reglas pedagógicas (M8 / P11).
     *
     * @param int $areaId ID del área de formación
     * @param int|null $gradoId ID del grado específico (o null)
     * @param int $dia Día de la semana (1-5)
     * @param int $bloque Número del bloque (1-N)
     * @param array $posicionesMateria Mapa de posiciones de materia
     * @return int|null Retorna el costo adicional (0, 1, 3) o null si la posición está prohibida.
     */
    public function costoPosicion(int $areaId, ?int $gradoId, int $dia, int $bloque, array $posicionesMateria): ?int
    {
        $tipo = null;

        // 1. Regla específica para el grado (gana sobre la general)
        if ($gradoId !== null) {
            $claveGrado = $areaId . '_' . $gradoId . '_' . $dia . '_' . $bloque;
            if (isset($posicionesMateria[$claveGrado])) {
                $tipo = $posicionesMateria[$claveGrado];
            }
        }

        // 2. Si no hay regla específica de grado, buscar regla general (grado_id = null -> clave con 0)
        if ($tipo === null) {
            $claveGeneral = $areaId . '_0_' . $dia . '_' . $bloque;
            if (isset($posicionesMateria[$claveGeneral])) {
                $tipo = $posicionesMateria[$claveGeneral];
            }
        }

        if ($tipo === null) {
            return 0;
        }

        if ($tipo === 'prohibido') {
            return null; // Descarta la celda inmediatamente
        }

        if ($tipo === 'no_preferente') {
            return 1;
        }

        if ($tipo === 'no_preferente_fuerte') {
            return 3;
        }

        return 0;
    }

    //asigna un bloque al hueco con menor costo que cumpla los requisitos (M5 / P6)
    public function asignarUnBloque(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente, array &$bloquesPorDiaGlobal = []): ?array
    {
        $docenteId = $clase['docente_id'];

        if (!isset($matrices['rechazos'])) {
            $matrices['rechazos'] = [
                'docente' => [],
                'seccion' => [],
                'aula' => [],
                'evaluaciones' => 0,
            ];
        }

        if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
            $matrices['bloquesPorDocente'][$docenteId] = 0;
        }

        //busca si el area necesita un aula especializada (ejem. Edu fisica)
        $aulaFijaMateria = $clase['area_aula'] ?? null;
        $esEspecializada = $aulaFijaMateria && !Aula::esTipoRegular($aulaFijaMateria->tipo_aula);

        $aulaNormalSeccion = null;
        if (!$esEspecializada) {
            $aulaNormalSeccion = $this->obtenerAulaNormal($clase['seccion_id']);
            if ($aulaNormalSeccion === null) {
                $matrices['rechazos']['aula'][$docenteId] = ($matrices['rechazos']['aula'][$docenteId] ?? 0) + 1;
                return null;
            }
        }

        $mejorOpcion = null;
        $menorCosto = PHP_INT_MAX;

        //recorre todas las celdas válidas (dia, bloque) y calcula el costo para cada una
        for ($dia = 1; $dia <= $totalDias; $dia++) {
            // Regla: si ya existe una asignación de esa misma materia en esa sección en ese día, saltar al siguiente día
            $claveMateriaDia = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
            if (isset($matrices['materiaDia'][$claveMateriaDia])) {
                continue;
            }

            for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                // Salta la celda inmediatamente si el bloque está reservado (ej. grupos estables)
                if (isset($matrices['reservado'][$dia . '_' . $bloque])) {
                    continue;
                }

                // Contador de evaluaciones
                $matrices['rechazos']['evaluaciones'] = ($matrices['rechazos']['evaluaciones'] ?? 0) + 1;

                $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                $claveSeccion = $clase['seccion_id'] . '_' . $dia . '_' . $bloque;

                //verifica que el docente, la seccion no hayan roto alguna restriccion o si el docente ya agoto sus horas
                if (isset($matrices['docenteOcupado'][$claveDocente])) {
                    $matrices['rechazos']['docente'][$docenteId] = ($matrices['rechazos']['docente'][$docenteId] ?? 0) + 1;
                    continue;
                }

                if (isset($matrices['seccionOcupado'][$claveSeccion])) {
                    $matrices['rechazos']['seccion'][$clase['seccion_id']] = ($matrices['rechazos']['seccion'][$clase['seccion_id']] ?? 0) + 1;
                    continue;
                }

                if ($matrices['bloquesPorDocente'][$docenteId] >= $maxBloquesDocente) {
                    $matrices['rechazos']['docente'][$docenteId] = ($matrices['rechazos']['docente'][$docenteId] ?? 0) + 1;
                    continue;
                }

                $aulaAsignada = null;

                //busca el aula correspondiente al area
                if ($esEspecializada) {
                    $aulaLibre = $this->buscarAulaEspecialLibre(
                        $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $clase['seccion_id']
                    );
                    if ($aulaLibre === null) {
                        $matrices['rechazos']['aula'][$docenteId] = ($matrices['rechazos']['aula'][$docenteId] ?? 0) + 1;
                        continue;
                    }
                    $aulaAsignada = $aulaLibre;
                } else {
                    $aulaAsignada = $aulaNormalSeccion;
                }

                // ===== CÁLCULO DE COSTO (M5 / M8 / P11) =====
                // 1. costoPosicion: reglas pedagógicas de materia (M8 / P11) + indisponibilidad docente
                $costoMateria = $this->costoPosicion(
                    (int) $clase['area_id'],
                    isset($clase['grado_id']) ? (int) $clase['grado_id'] : null,
                    $dia,
                    $bloque,
                    $matrices['posicionesMateria'] ?? []
                );

                // Si la posición está prohibida para la materia, descartar la celda inmediatamente
                if ($costoMateria === null) {
                    continue;
                }

                $costoPosicion = $costoMateria;
                if (isset($matrices['docenteNoPreferente'][$claveDocente])) {
                    $costoPosicion += 1;
                }

                // 2. costoHueco: 2 si colocar la clase deja un hueco en la sección ese día.
                // Un hueco es un bloque libre no reservado entre dos bloques ocupados.
                $bloquesOcupados = [$bloque];
                for ($b = 1; $b <= $totalBloques; $b++) {
                    if ($b === $bloque) continue;
                    if (isset($matrices['seccionOcupado'][$clase['seccion_id'] . '_' . $dia . '_' . $b]) ||
                        isset($matrices['reservado'][$dia . '_' . $b])) {
                        $bloquesOcupados[] = $b;
                    }
                }
                $costoHueco = 0;
                if (count($bloquesOcupados) > 1) {
                    $min = min($bloquesOcupados);
                    $max = max($bloquesOcupados);
                    if (($max - $min + 1) - count($bloquesOcupados) > 0) {
                        $costoHueco = 2;
                    }
                }

                // 3. cargaDelDocenteEseDia: cantidad de bloques ya asignados a ese docente en ese día (para balancear)
                $cargaDelDocenteEseDia = $matrices['docenteDia'][$docenteId . '_' . $dia] ?? 0;

                $costoTotal = $costoPosicion + $costoHueco + $cargaDelDocenteEseDia;

                // Elige la celda con el menor costo total.
                // En caso de empate, la condición estricta (<) preserva la primera celda evaluada (orden ascendente de día y bloque).
                if ($costoTotal < $menorCosto) {
                    $menorCosto = $costoTotal;
                    $mejorOpcion = [
                        'dia' => $dia,
                        'bloque' => $bloque,
                        'aula_id' => $aulaAsignada,
                    ];
                }
            }
        }

        if ($mejorOpcion === null) {
            return null;
        }

        $dia = $mejorOpcion['dia'];
        $bloque = $mejorOpcion['bloque'];
        $aulaAsignada = $mejorOpcion['aula_id'];

        $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
        $claveSeccion = $clase['seccion_id'] . '_' . $dia . '_' . $bloque;
        $claveMateriaDia = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;

        //asigna marcando todo como ocupado para las siguientes iteraciones
        $asignacion = [
            'carga_academica_id' => $clase['carga_academica_id'] ?? null,
            'docente_id' => $docenteId,
            'materia_id' => $clase['area_id'],
            'seccion_id' => $clase['seccion_id'],
            'dia' => $dia,
            'bloque' => $bloque,
            'aula_id' => $aulaAsignada,
        ];

        $matrices['docenteOcupado'][$claveDocente] = true;
        $matrices['seccionOcupado'][$claveSeccion] = true;
        $matrices['bloquesPorDocente'][$docenteId]++;
        $matrices['docenteDia'][$docenteId . '_' . $dia] = ($matrices['docenteDia'][$docenteId . '_' . $dia] ?? 0) + 1;
        $matrices['materiaDia'][$claveMateriaDia] = true;

        if ($esEspecializada) {
            $matrices['aulaEspecialOcupada'][$aulaAsignada . '_' . $dia . '_' . $bloque] = true;
        }

        return $asignacion;
    }

    /**
     * Cuenta el total de huecos en un conjunto de asignaciones para las secciones dadas (M5 / P6).
     * Un hueco se define como: (último bloque ocupado - primer bloque ocupado + 1) - cantidad de bloques ocupados.
     * Las celdas reservadas cuentan como ocupadas.
     */
    public function contarHuecos(array $asignaciones, array $reservado, array $seccionIds): int
    {
        $totalHuecos = 0;

        // Indexar bloques asignados por [seccion_id][dia][bloque]
        $ocupadosPorSeccionDia = [];
        foreach ($seccionIds as $secId) {
            $ocupadosPorSeccionDia[$secId] = [];
        }

        foreach ($asignaciones as $asig) {
            $secId = $asig['seccion_id'] ?? null;
            $dia = $asig['dia'] ?? null;
            $bloque = $asig['bloque'] ?? null;

            if ($secId !== null && in_array($secId, $seccionIds) && $dia !== null && $bloque !== null) {
                $ocupadosPorSeccionDia[$secId][$dia][$bloque] = true;
            }
        }

        // Días regulares de evaluación (días 1 a 5)
        $dias = range(1, 5);

        foreach ($seccionIds as $secId) {
            foreach ($dias as $dia) {
                $bloquesOcupados = isset($ocupadosPorSeccionDia[$secId][$dia])
                    ? array_keys($ocupadosPorSeccionDia[$secId][$dia])
                    : [];

                // Celdas reservadas cuentan como ocupadas
                foreach ($reservado as $clave => $val) {
                    if ($val) {
                        $partes = explode('_', (string) $clave);
                        if (count($partes) === 2 && (int) $partes[0] === (int) $dia) {
                            $bloquesOcupados[] = (int) $partes[1];
                        }
                    }
                }

                $bloquesOcupados = array_unique($bloquesOcupados);

                if (count($bloquesOcupados) > 1) {
                    $min = min($bloquesOcupados);
                    $max = max($bloquesOcupados);
                    $cant = count($bloquesOcupados);
                    $huecos = ($max - $min + 1) - $cant;
                    if ($huecos > 0) {
                        $totalHuecos += $huecos;
                    }
                }
            }
        }

        return $totalHuecos;
    }

    //reconstruye las matrices de ocupación a partir de las asignaciones ya guardadas en BD
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
            $docenteOcupado = [];
            $seccionOcupado = [];
            $bloquesPorDocente = [];

            foreach ($asignacionesExistentes as $asignacion) {
                $asigDia = (int) ($asignacion['dia'] ?? 0);
                $asigBloque = (int) ($asignacion['bloque'] ?? 0);

                if ($asignacion['docente_id'] == $docenteId &&
                    $asignacion['seccion_id'] == $seccionId &&
                    $asigDia == $diaActual &&
                    $asigBloque == $bloqueActual) {
                    continue;
                }

                $claveDocente = $asignacion['docente_id'] . '_' . $asigDia . '_' . $asigBloque;
                $claveSeccion = $asignacion['seccion_id'] . '_' . $asigDia . '_' . $asigBloque;

                $docenteOcupado[$claveDocente] = true;
                $seccionOcupado[$claveSeccion] = true;

                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            $noDisponible = DocenteIndisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->where('estado', 'aprobada')
                ->where('tipo', 'prohibido')
                ->get();
            foreach ($noDisponible as $nd) {
                $diaNum = $nd->dia instanceof \App\Enums\Dia ? $nd->dia->value : (int) $nd->dia;
                $bloqueNum = (int) $nd->bloque;
                $clave = $nd->docente_id . '_' . $diaNum . '_' . $bloqueNum;
                $docenteOcupado[$clave] = true;
            }

            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    if ($dia == $diaActual && $bloque == $bloqueActual) {
                        continue;
                    }

                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;

                    if (isset($docenteOcupado[$claveDocente])) continue;
                    if (isset($seccionOcupado[$claveSeccion])) continue;

                    $bloquesActuales = $bloquesPorDocente[$docenteId] ?? 0;
                    if ($bloquesActuales >= $maxBloquesDocente) continue;

                    return [
                        'dia' => $dia,
                        'bloque' => $bloque,
                        'aula_id' => $aulaId,
                    ];
                }
            }

            return null;
        }
    //si no hay hueco libre, busca otra clase con la que se pueda intercambiar
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
            $docenteOcupado = [];
            $seccionOcupado = [];
            $bloquesPorDocente = [];

            foreach ($asignacionesExistentes as $asignacion) {
                $asigDia = (int) ($asignacion['dia'] ?? 0);
                $asigBloque = (int) ($asignacion['bloque'] ?? 0);

                if ($asignacion['docente_id'] == $docenteId &&
                    $asignacion['seccion_id'] == $seccionId &&
                    $asigDia == $diaActual &&
                    $asigBloque == $bloqueActual) {
                    continue;
                }

                $claveDocente = $asignacion['docente_id'] . '_' . $asigDia . '_' . $asigBloque;
                $claveSeccion = $asignacion['seccion_id'] . '_' . $asigDia . '_' . $asigBloque;

                $docenteOcupado[$claveDocente] = true;
                $seccionOcupado[$claveSeccion] = true;

                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            $noDisponible = DocenteIndisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->where('estado', 'aprobada')
                ->where('tipo', 'prohibido')
                ->get();
            foreach ($noDisponible as $nd) {
                $diaNum = $nd->dia instanceof \App\Enums\Dia ? $nd->dia->value : (int) $nd->dia;
                $bloqueNum = (int) $nd->bloque;
                $clave = $nd->docente_id . '_' . $diaNum . '_' . $bloqueNum;
                $docenteOcupado[$clave] = true;
            }

            foreach ($asignacionesExistentes as $index => $otraAsignacion) {
                $otroDia = (int) ($otraAsignacion['dia'] ?? 0);
                $otroBloque = (int) ($otraAsignacion['bloque'] ?? 0);

                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId &&
                    $otroDia == $diaActual &&
                    $otroBloque == $bloqueActual) {
                    continue;
                }

                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId) {
                    continue;
                }

                $otroDocenteId = $otraAsignacion['docente_id'];
                $otraSeccionId = $otraAsignacion['seccion_id'];
                $otraAula = $otraAsignacion['aula_id'];

                $maxBloquesOtroDocente = $this->calcularMaxBloques(36);

                $claveDocenteActual = $otroDocenteId . '_' . $diaActual . '_' . $bloqueActual;
                if (isset($docenteOcupado[$claveDocenteActual])) continue;

                $claveSeccionActual = $otraSeccionId . '_' . $diaActual . '_' . $bloqueActual;
                if (isset($seccionOcupado[$claveSeccionActual])) continue;

                $claveDocenteOtro = $docenteId . '_' . $otroDia . '_' . $otroBloque;
                if (isset($docenteOcupado[$claveDocenteOtro])) continue;

                $claveSeccionOtro = $seccionId . '_' . $otroDia . '_' . $otroBloque;
                if (isset($seccionOcupado[$claveSeccionOtro])) continue;

                $bloquesDocenteActual = 0;
                $bloquesOtroDocente = 0;
                
                foreach ($asignacionesExistentes as $asignacion) {
                    $curDia = (int) ($asignacion['dia'] ?? 0);
                    $curBloque = (int) ($asignacion['bloque'] ?? 0);

                    if ($asignacion['docente_id'] == $docenteId &&
                        !($asignacion['docente_id'] == $docenteId &&
                          $asignacion['seccion_id'] == $seccionId &&
                          $curDia == $diaActual &&
                          $curBloque == $bloqueActual)) {
                        $bloquesDocenteActual++;
                    }
                    if ($asignacion['docente_id'] == $otroDocenteId &&
                        !($asignacion['docente_id'] == $otroDocenteId &&
                          $asignacion['seccion_id'] == $otraSeccionId &&
                          $curDia == $otroDia &&
                          $curBloque == $otroBloque)) {
                        $bloquesOtroDocente++;
                    }
                }

                if ($bloquesDocenteActual >= $maxBloquesDocente) continue;
                if ($bloquesOtroDocente >= $maxBloquesOtroDocente) continue;

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

            return null;
        }
    //llama a intercambios
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
            $maxBloquesDocente = $this->calcularMaxBloques(36);

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

    /**
     * Realiza un intento de generación en memoria sin escribir en la base de datos.
     *
     * @param int $anio ID del año escolar
     * @param int|null $gradoId Filtro opcional por grado
     * @param int|null $areaId Filtro opcional por área de formación
     * @param bool $aleatorizar Si es true, permuta aleatoriamente clases con misma disponibilidad y tamaño
     * @param int $totalDias Total de días lectivos semanales (def: 5)
     * @param int $totalBloques Total de bloques por día (def: 8)
     * @return array{asignaciones: array, conflictos: array, matrices: array}
     */
    public function intentarGenerar(
        int $anio,
        ?int $gradoId = null,
        ?int $areaId = null,
        bool $aleatorizar = false,
        int $totalDias = 5,
        int $totalBloques = 8
    ): array {
        // Cargar datos, restricciones y prioridades
        $clases = $this->PreparasDatos($gradoId, $areaId, $anio);
        $matrices = $this->InicializarMatrices($anio);

        // Las asignaciones conservadas (fijadas o fuera del alcance) ocupan docente, sección y aula
        $conservadas = HorarioAsignacion::where('anio_escolar_id', $anio)
            ->with('cargaAcademica.seccion')
            ->get();

        $bloquesFijadosPorCarga = [];

        foreach ($conservadas as $c) {
            $estaEnAlcance = true;
            if ($gradoId !== null && $c->cargaAcademica?->seccion?->grado_id != $gradoId) {
                $estaEnAlcance = false;
            }
            if ($areaId !== null && $c->cargaAcademica?->area_formacion_id != $areaId) {
                $estaEnAlcance = false;
            }

            // Si es fijada o está fuera del alcance, se mantiene ocupando celda
            if ($c->fijada || !$estaEnAlcance) {
                $cDia = $c->dia instanceof Dia ? $c->dia->value : (int) $c->dia;
                $cBloque = (int) $c->bloque;
                $cDocenteId = $c->cargaAcademica?->docente_id;
                $cSeccionId = $c->cargaAcademica?->seccion_id;
                $cMateriaId = $c->cargaAcademica?->area_formacion_id;

                if ($cDocenteId && $cDia && $cBloque) {
                    $matrices['docenteOcupado'][$cDocenteId . '_' . $cDia . '_' . $cBloque] = true;
                    $matrices['bloquesPorDocente'][$cDocenteId] = ($matrices['bloquesPorDocente'][$cDocenteId] ?? 0) + 1;
                    $matrices['docenteDia'][$cDocenteId . '_' . $cDia] = ($matrices['docenteDia'][$cDocenteId . '_' . $cDia] ?? 0) + 1;
                }
                if ($cSeccionId && $cDia && $cBloque) {
                    $matrices['seccionOcupado'][$cSeccionId . '_' . $cDia . '_' . $cBloque] = true;
                }
                if ($c->aula_id && $cDia && $cBloque) {
                    $matrices['aulaEspecialOcupada'][$c->aula_id . '_' . $cDia . '_' . $cBloque] = true;
                }
                if ($cMateriaId && $cSeccionId && $cDia) {
                    $matrices['materiaDia'][$cMateriaId . '_' . $cSeccionId . '_' . $cDia] = true;
                }

                // Contar cuántos bloques fijados tiene cada carga académica
                if ($c->fijada && $c->carga_academica_id) {
                    $bloquesFijadosPorCarga[$c->carga_academica_id] = ($bloquesFijadosPorCarga[$c->carga_academica_id] ?? 0) + 1;
                }
            }
        }

        // Descontar bloques ya fijados del total semanal de cada clase pendiente
        $clases = $clases->map(function ($clase) use ($bloquesFijadosPorCarga) {
            $cargaId = $clase['carga_academica_id'] ?? null;
            $fijados = ($cargaId && isset($bloquesFijadosPorCarga[$cargaId])) ? (int) $bloquesFijadosPorCarga[$cargaId] : 0;
            $clase['area_bloque'] = max(0, (int) ($clase['area_bloque'] ?? 0) - $fijados);
            $clase['bloques_fijados'] = $fijados;
            return $clase;
        })->filter(function ($clase) {
            return $clase['area_bloque'] > 0;
        })->values();

        // Calcular disponibilidad docente para cada clase
        $clasesConDisp = $clases->map(function ($clase) use ($matrices, $totalDias, $totalBloques) {
            $clase['disponibilidad_docente'] = $this->calcularDisponibilidad(
                $clase['docente_id'],
                $matrices['docenteOcupado'],
                $totalDias,
                $totalBloques
            );
            return $clase;
        });

        // Ordenar y opcionalmente mezclar clases con la misma disponibilidad y tamaño
        if ($aleatorizar) {
            $grupos = $clasesConDisp->groupBy(function ($item) {
                return $item['disponibilidad_docente'] . '_' . $item['area_bloque'];
            });

            $gruposOrdenados = $grupos->sort(function ($a, $b) {
                $pA = $a->first();
                $pB = $b->first();
                if ($pA['disponibilidad_docente'] !== $pB['disponibilidad_docente']) {
                    return $pA['disponibilidad_docente'] <=> $pB['disponibilidad_docente'];
                }
                return $pB['area_bloque'] <=> $pA['area_bloque'];
            });

            $clasesOrdenadas = collect();
            foreach ($gruposOrdenados as $grupo) {
                $items = $grupo->values()->all();
                // Fisher-Yates shuffle determinista basado en mt_rand()
                for ($j = count($items) - 1; $j > 0; $j--) {
                    $k = mt_rand(0, $j);
                    $temp = $items[$j];
                    $items[$j] = $items[$k];
                    $items[$k] = $temp;
                }
                foreach ($items as $item) {
                    $clasesOrdenadas->push($item);
                }
            }
        } else {
            $clasesOrdenadas = $clasesConDisp->sortBy([
                ['disponibilidad_docente', 'asc'],
                ['area_bloque', 'desc'],
            ])->values();
        }

        $todasLasAsignaciones = [];
        $todosLosConflictos = [];
        $bloquesPorDiaGlobal = [];

        $clasesPendientes = [];
        foreach ($clasesOrdenadas as $clase) {
            $claseArray = $clase;
            $claseArray['bloques_restantes'] = $clase['area_bloque'];
            $clasesPendientes[] = $claseArray;
        }

        // Bucle asigna un bloque por clase en cada ronda
        do {
            $huboAvanceEnRonda = false;

            foreach ($clasesPendientes as &$clase) {
                if ($clase['bloques_restantes'] <= 0) {
                    continue;
                }

                $maxBloques = $this->calcularMaxBloques($clase['docente_horas_academicas'] ?? 36);

                $asignacion = $this->asignarUnBloque($clase, $matrices, $totalDias, $totalBloques, $maxBloques, $bloquesPorDiaGlobal);

                if ($asignacion !== null) {
                    $aulaId = $asignacion['aula_id'] > 0 ? $asignacion['aula_id'] : null;

                    // Acumular en memoria, sin escribir en BD
                    $todasLasAsignaciones[] = [
                        'anio_escolar_id' => $anio,
                        'carga_academica_id' => $clase['carga_academica_id'],
                        'docente_id' => $clase['docente_id'],
                        'seccion_id' => $clase['seccion_id'],
                        'materia_id' => $clase['area_id'],
                        'aula_id' => $aulaId,
                        'dia' => $asignacion['dia'],
                        'bloque' => $asignacion['bloque'],
                        'fijada' => false,
                        'conflicto_manual' => false,
                    ];

                    $clase['bloques_restantes']--;
                    $huboAvanceEnRonda = true;
                }
            }
            unset($clase);

        } while ($huboAvanceEnRonda && $this->hayClasesPendientes($clasesPendientes));

        // Detectar conflictos para clases no asignadas completamente
        foreach ($clasesPendientes as $clase) {
            if ($clase['bloques_restantes'] > 0) {
                $tipoConflicto = $this->determinarTipoConflicto($clase, $matrices, $totalDias, $totalBloques, $bloquesPorDiaGlobal);
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
            'matrices' => $matrices,
        ];
    }

    /**
     * Calcula la suma de (máx - mín) de bloques por día de cada docente.
     */
    public function calcularDesbalanceDocentes(array $asignaciones, int $totalDias): int
    {
        $asignacionesPorDocente = [];
        foreach ($asignaciones as $asig) {
            $docId = $asig['docente_id'] ?? null;
            if ($docId) {
                $asignacionesPorDocente[$docId][] = $asig;
            }
        }

        $desbalanceTotal = 0;
        foreach ($asignacionesPorDocente as $docId => $docAsigs) {
            $bloquesPorDia = array_fill(1, $totalDias, 0);
            foreach ($docAsigs as $asig) {
                $dia = (int) ($asig['dia'] ?? 0);
                if (isset($bloquesPorDia[$dia])) {
                    $bloquesPorDia[$dia]++;
                }
            }
            $max = max($bloquesPorDia);
            $min = min($bloquesPorDia);
            $desbalanceTotal += ($max - $min);
        }

        return $desbalanceTotal;
    }

    /**
     * Ejecuta múltiples intentos aleatorizados en memoria, selecciona el de menor puntaje
     * y guarda el resultado en la base de datos bajo una única transacción.
     *
     * @param int $anioEscolar ID del año escolar
     * @param int $totalDias Total de días lectivos (def: 5)
     * @param int $totalBloques Total de bloques por día (def: 8)
     * @param int|null $gradoId Filtro opcional por grado
     * @param int|null $areaFormacionId Filtro opcional por área
     * @param int $intentos Número de intentos en memoria (def: 20)
     * @return array
     */
    public function generar(
        int $anioEscolar,
        $totalDias = 5,
        $totalBloques = 8,
        ?int $gradoId = null,
        ?int $areaFormacionId = null,
        int $intentos = 20
    ): array {
        $tiempoInicio = microtime(true);

        // Soporte de compatibilidad si se llama con firma posicional (anio, gradoId, areaId, intentos)
        if ($totalDias === null || (!is_numeric($totalDias) && is_null($totalDias))) {
            $intentos = is_numeric($gradoId) ? (int)$gradoId : 20;
            $areaFormacionId = $totalBloques;
            $gradoId = $totalDias;
            $totalDias = 5;
            $config = app(\App\Services\ConfiguracionHorarioService::class)->actual();
            $totalBloques = (int)$config->numero_bloques;
        }

        $totalDias = (int) ($totalDias ?? 5);
        $totalBloques = (int) ($totalBloques ?? 8);
        $intentos = max(1, (int) ($intentos ?? 20));

        $mejorIntento = null;
        $menorPuntaje = PHP_INT_MAX;
        $intentosEjecutados = 0;

        for ($i = 1; $i <= $intentos; $i++) {
            $intentosEjecutados++;
            mt_srand($i);

            $intento = $this->intentarGenerar(
                anio: $anioEscolar,
                gradoId: $gradoId,
                areaId: $areaFormacionId,
                aleatorizar: true,
                totalDias: $totalDias,
                totalBloques: $totalBloques
            );

            $asignaciones = $intento['asignaciones'];
            $conflictos = $intento['conflictos'];
            $matrices = $intento['matrices'];

            $seccionIds = array_values(array_unique(array_filter(array_column($asignaciones, 'seccion_id'))));
            $huecos = $this->contarHuecos($asignaciones, $matrices['reservado'] ?? [], $seccionIds);
            $desbalance = $this->calcularDesbalanceDocentes($asignaciones, $totalDias);

            // Fórmula: (count($conflictos) * 1000) + ($this->contarHuecos(...) * 10) + $desbalanceCargaDocentes
            $puntaje = (count($conflictos) * 1000) + ($huecos * 10) + $desbalance;

            if ($puntaje < $menorPuntaje || $mejorIntento === null) {
                $menorPuntaje = $puntaje;
                $mejorIntento = [
                    'numero_intento' => $i,
                    'asignaciones' => $asignaciones,
                    'conflictos' => $conflictos,
                    'puntaje' => $puntaje,
                    'huecos' => $huecos,
                    'desbalance' => $desbalance,
                    'matrices' => $matrices,
                ];
            }

            // Si el puntaje es 0, es perfecto, rompe el bucle
            if ($puntaje === 0) {
                break;
            }
        }

        if ($mejorIntento === null) {
            $mejorIntento = [
                'numero_intento' => 1,
                'asignaciones' => [],
                'conflictos' => [],
                'puntaje' => 0,
                'huecos' => 0,
                'desbalance' => 0,
                'matrices' => [
                    'rechazos' => [
                        'docente' => [],
                        'seccion' => [],
                        'aula' => [],
                        'evaluaciones' => 0,
                    ]
                ],
            ];
        }

        // Guardado final en base de datos bajo transacción única
        \DB::transaction(function () use ($anioEscolar, $gradoId, $areaFormacionId, $mejorIntento) {
            // Borra solo lo que se va a regenerar respetando clases fijadas
            $aBorrar = HorarioAsignacion::where('anio_escolar_id', $anioEscolar)
                ->where('fijada', false);

            if ($gradoId !== null) {
                $aBorrar->whereHas('cargaAcademica.seccion', function ($q) use ($gradoId) {
                    $q->where('grado_id', $gradoId);
                });
            }

            if ($areaFormacionId !== null) {
                $aBorrar->whereHas('cargaAcademica', function ($q) use ($areaFormacionId) {
                    $q->where('area_formacion_id', $areaFormacionId);
                });
            }

            $aBorrar->delete();

            // Inserta en lote las asignaciones del intento ganador
            if (!empty($mejorIntento['asignaciones'])) {
                $ahora = now();
                $filasAInsertar = [];
                foreach ($mejorIntento['asignaciones'] as $asig) {
                    $filasAInsertar[] = [
                        'anio_escolar_id' => $anioEscolar,
                        'carga_academica_id' => $asig['carga_academica_id'],
                        'aula_id' => $asig['aula_id'] ?? null,
                        'dia' => $asig['dia'],
                        'bloque' => $asig['bloque'],
                        'fijada' => false,
                        'conflicto_manual' => false,
                        'motivo_conflicto' => null,
                        'created_at' => $ahora,
                        'updated_at' => $ahora,
                    ];
                }

                foreach (array_chunk($filasAInsertar, 500) as $chunk) {
                    HorarioAsignacion::insert($chunk);
                }
            }
        });

        $tiempoFin = microtime(true);
        $tiempoMs = round(($tiempoFin - $tiempoInicio) * 1000, 2);

        $diagnostico = $this->construirDiagnostico(
            $mejorIntento['matrices'] ?? [],
            $anioEscolar,
            $mejorIntento['conflictos'] ?? []
        );

        return [
            'asignaciones' => $mejorIntento['asignaciones'],
            'conflictos' => $mejorIntento['conflictos'],
            'intento_ganador' => $mejorIntento['numero_intento'],
            'puntaje' => $mejorIntento['puntaje'],
            'intentos_ejecutados' => $intentosEjecutados,
            'huecos' => $mejorIntento['huecos'],
            'tiempo_ms' => $tiempoMs,
            'diagnostico' => $diagnostico,
        ];
    }

    /**
     * Construye el diagnóstico de dificultad y cuellos de botella (M7 / P8).
     *
     * @param array $matrices Matrices de ocupación y contadores de rechazos
     * @param int $anioEscolar ID del año escolar
     * @param array $conflictos Conflictos detectados no asignados
     * @return array
     */
    public function construirDiagnostico(array $matrices, int $anioEscolar, array $conflictos = []): array
    {
        $rechazos = $matrices['rechazos'] ?? [
            'docente' => [],
            'seccion' => [],
            'aula' => [],
            'evaluaciones' => 0,
        ];

        $totalEvaluaciones = (int) ($rechazos['evaluaciones'] ?? 0);
        $divisor = max(1, $totalEvaluaciones);

        // Mapeo de nombres de docentes
        $docenteIds = array_keys($rechazos['docente'] ?? []);
        if (!empty($rechazos['aula'])) {
            $docenteIds = array_unique(array_merge($docenteIds, array_keys($rechazos['aula'])));
        }
        $docentes = !empty($docenteIds)
            ? Docente::with('persona')->whereIn('id', $docenteIds)->get()->keyBy('id')
            : collect();

        // Mapeo de nombres de secciones
        $seccionIds = array_keys($rechazos['seccion'] ?? []);
        $secciones = !empty($seccionIds)
            ? Seccion::with('grado')->whereIn('id', $seccionIds)->get()->keyBy('id')
            : collect();

        // Top 5 docentes con más rechazos
        $rechazosDocente = $rechazos['docente'] ?? [];
        arsort($rechazosDocente);
        $topDocentes = [];
        $count = 0;
        foreach ($rechazosDocente as $docId => $cantidad) {
            if ($count++ >= 5) break;
            $docente = $docentes->get($docId);
            $nombre = $docente && $docente->persona
                ? trim($docente->persona->primer_nombre . ' ' . $docente->persona->primer_apellido)
                : "Docente #{$docId}";

            $porcentaje = round(($cantidad / $divisor) * 100, 1);
            $topDocentes[] = [
                'docente_id' => (int) $docId,
                'nombre' => $nombre,
                'rechazos' => (int) $cantidad,
                'porcentaje' => $porcentaje,
            ];
        }

        // Top 5 secciones con más rechazos
        $rechazosSeccion = $rechazos['seccion'] ?? [];
        arsort($rechazosSeccion);
        $topSecciones = [];
        $count = 0;
        foreach ($rechazosSeccion as $secId => $cantidad) {
            if ($count++ >= 5) break;
            $seccion = $secciones->get($secId);
            $nombreSeccion = 'Sección #' . $secId;
            if ($seccion) {
                $nombreGrado = $seccion->grado ? $seccion->grado->numero_grado . '° ' : '';
                $nombreSeccion = trim($nombreGrado . $seccion->nombre);
            }

            $porcentaje = round(($cantidad / $divisor) * 100, 1);
            $topSecciones[] = [
                'seccion_id' => (int) $secId,
                'nombre' => $nombreSeccion,
                'rechazos' => (int) $cantidad,
                'porcentaje' => $porcentaje,
            ];
        }

        // Top 5 aulas con más rechazos
        $rechazosAula = $rechazos['aula'] ?? [];
        arsort($rechazosAula);
        $topAulas = [];
        $count = 0;
        foreach ($rechazosAula as $key => $cantidad) {
            if ($count++ >= 5) break;
            $docente = $docentes->get($key);
            $nombre = $docente && $docente->persona
                ? trim($docente->persona->primer_nombre . ' ' . $docente->persona->primer_apellido)
                : "Docente #{$key}";

            $porcentaje = round(($cantidad / $divisor) * 100, 1);
            $topAulas[] = [
                'docente_id' => (int) $key,
                'nombre' => $nombre,
                'rechazos' => (int) $cantidad,
                'porcentaje' => $porcentaje,
            ];
        }

        return [
            'total_evaluaciones' => $totalEvaluaciones,
            'docentes' => $topDocentes,
            'secciones' => $topSecciones,
            'aulas' => $topAulas,
            'conflictos' => $conflictos,
        ];
    }

        //recorre el array de clases y busca si hay alguna vacia para la iteracion de asignaciones

        private function hayClasesPendientes(array $clasesPendientes): bool
        {
            foreach ($clasesPendientes as $clase) {
                if ($clase['bloques_restantes'] > 0) {
                    return true;
                }
            }
            return false;
        }

        //analiza porque no se pudo asignar, busca todas las combinaciones
        public function determinarTipoConflicto(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal = []): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && !Aula::esTipoRegular($aulaFijaMateria->tipo_aula);

            $maxBloquesDocente = $this->calcularMaxBloques($clase['docente_horas_academicas'] ?? 36);
            $bloquesDocenteActuales = $matrices['bloquesPorDocente'][$docenteId] ?? 0;

            $espaciosLibresDocente = 0;
            $espaciosLibresSeccion = 0;
            $espaciosLibresAulaEspecial = 0;
            $espaciosLibresAulaRegular = 0;
            $espaciosLibresReservados = 0;
            $espaciosLibresUnaPorDia = 0;

            $coincidentesLibres = 0;
            $bloqueadosPorReservadoEnCoincidentes = 0;
            $bloqueadosPorUnaPorDiaEnCoincidentes = 0;

            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $claveMateriaDia = $clase['area_id'] . '_' . $seccionId . '_' . $dia;
                $diaTieneMateria = isset($matrices['materiaDia'][$claveMateriaDia]);

                if (!$diaTieneMateria) {
                    $espaciosLibresUnaPorDia += $totalBloques;
                }

                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;
                    $claveReservado = $dia . '_' . $bloque;

                    $docenteLibre = !isset($matrices['docenteOcupado'][$claveDocente]);
                    $seccionLibre = !isset($matrices['seccionOcupado'][$claveSeccion]);
                    $noReservado = !isset($matrices['reservado'][$claveReservado]);

                    if ($docenteLibre) {
                        $espaciosLibresDocente++;
                    }

                    if ($seccionLibre) {
                        $espaciosLibresSeccion++;
                    }

                    if ($noReservado) {
                        $espaciosLibresReservados++;
                    }

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

                    // Celdas donde tanto docente como sección están libres
                    if ($docenteLibre && $seccionLibre) {
                        $coincidentesLibres++;
                        if (!$noReservado) {
                            $bloqueadosPorReservadoEnCoincidentes++;
                        }
                        if ($diaTieneMateria) {
                            $bloqueadosPorUnaPorDiaEnCoincidentes++;
                        }
                    }
                }
            }

            if ($esEspecializada && $espaciosLibresAulaEspecial === 0) {
                return [
                    'tipo' => 'aula_especializada',
                    'motivo' => 'No hay aulas especializadas disponibles para esta materia',
                    'sugerencias' => [
                        'Considerar asignar más aulas del tipo ' . ($aulaFijaMateria?->tipo_aula ?? 'especializada'),
                        'Reducir la frecuencia de esta materia',
                        'Programar en horarios donde estas aulas estén disponibles',
                    ],
                ];
            }

            if (!$esEspecializada && $espaciosLibresAulaRegular === 0) {
                return [
                    'tipo' => 'aula_regular',
                    'motivo' => 'La sección no tiene aula regular asignada o no tiene capacidad suficiente',
                    'sugerencias' => [
                        'Asignar un aula regular a la sección',
                        'Verificar la configuración de sección-aula y su capacidad',
                    ],
                ];
            }

            if ($bloquesDocenteActuales >= $maxBloquesDocente || $espaciosLibresDocente === 0) {
                return [
                    'tipo' => 'docente_saturado',
                    'motivo' => 'El docente ya tiene su máximo de bloques asignados (' . $maxBloquesDocente . ' bloques)',
                    'sugerencias' => [
                        'Aumentar las horas académicas del docente',
                        'Asignar esta materia a otro docente',
                        'Reducir la carga académica de este docente',
                    ],
                ];
            }

            if ($espaciosLibresSeccion === 0) {
                return [
                    'tipo' => 'seccion_saturada',
                    'motivo' => 'La sección ya tiene todos sus bloques ocupados',
                    'sugerencias' => [
                        'Revisar la distribución de materias de esta sección',
                        'Considerar reducir bloques de otras materias',
                    ],
                ];
            }

            if ($espaciosLibresUnaPorDia === 0 || ($coincidentesLibres > 0 && $bloqueadosPorUnaPorDiaEnCoincidentes === $coincidentesLibres)) {
                return [
                    'tipo' => 'una_por_dia',
                    'motivo' => 'La materia ya tiene un bloque asignado en ese día para esa sección',
                    'sugerencias' => [
                        'Verificar la cantidad de bloques semanales configurados para esta materia',
                        'Distribuir la materia en otros días de la semana donde aún no esté asignada',
                    ],
                ];
            }

            if ($espaciosLibresReservados === 0 || ($coincidentesLibres > 0 && $bloqueadosPorReservadoEnCoincidentes === $coincidentesLibres)) {
                return [
                    'tipo' => 'bloque_reservado',
                    'motivo' => 'Los bloques disponibles están bloqueados por reservaciones globales',
                    'sugerencias' => [
                        'Revisar la configuración de bloques reservados del año escolar',
                        'Ajustar la distribución de horarios de grupos estables',
                    ],
                ];
            }

            $coincidentesValidos = 0;
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $claveMateriaDia = $clase['area_id'] . '_' . $seccionId . '_' . $dia;
                if (isset($matrices['materiaDia'][$claveMateriaDia])) {
                    continue;
                }
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    if (isset($matrices['reservado'][$dia . '_' . $bloque])) {
                        continue;
                    }
                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;
                    if (!isset($matrices['docenteOcupado'][$claveDocente]) && !isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $coincidentesValidos++;
                    }
                }
            }

            if ($coincidentesValidos === 0 || $espaciosLibresDocente < $clase['bloques_restantes'] || $espaciosLibresSeccion < $clase['bloques_restantes']) {
                return [
                    'tipo' => 'combinacion_restricciones',
                    'motivo' => 'Combinación de restricciones que limitan la disponibilidad',
                    'sugerencias' => [
                        'Ajustar la distribución horaria',
                        'Considerar intercambios con otras asignaciones',
                    ],
                ];
            }

            return [
                'tipo' => 'desconocido',
                'motivo' => 'No se pudo determinar la causa específica',
                'sugerencias' => [
                    'Revisar manualmente la disponibilidad',
                    'Contactar al administrador del sistema',
                ],
            ];
        }

        // da un listado del diaybloque que no pudo asignarse
        public function identificarEspaciosFallidos(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal = []): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && !Aula::esTipoRegular($aulaFijaMateria->tipo_aula);

            $maxBloquesDocente = $this->calcularMaxBloques($clase['docente_horas_academicas'] ?? 36);
            $bloquesDocenteActuales = $matrices['bloquesPorDocente'][$docenteId] ?? 0;

            $espaciosFallidos = [];

            for ($dia = 1; $dia <= $totalDias; $dia++) {
                $claveMateriaDia = $clase['area_id'] . '_' . $seccionId . '_' . $dia;

                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {
                    $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                    $claveSeccion = $seccionId . '_' . $dia . '_' . $bloque;
                    $claveReservado = $dia . '_' . $bloque;

                    $razones = [];

                    if (isset($matrices['docenteOcupado'][$claveDocente])) {
                        $razones[] = 'docente ocupado';
                    }

                    if (isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $razones[] = 'sección ocupada';
                    }

                    if ($bloquesDocenteActuales >= $maxBloquesDocente) {
                        $razones[] = 'límite del docente alcanzado';
                    }

                    if (isset($matrices['reservado'][$claveReservado])) {
                        $razones[] = 'bloque reservado globalmente';
                    }

                    if (isset($matrices['materiaDia'][$claveMateriaDia])) {
                        $razones[] = 'materia ya asignada ese día';
                    }

                    $costoMat = $this->costoPosicion(
                        (int) $clase['area_id'],
                        isset($clase['grado_id']) ? (int) $clase['grado_id'] : null,
                        $dia,
                        $bloque,
                        $matrices['posicionesMateria'] ?? []
                    );
                    if ($costoMat === null) {
                        $razones[] = 'posición prohibida para la materia';
                    }

                    if ($esEspecializada) {
                        $aulaLibre = $this->buscarAulaEspecialLibre(
                            $aulaFijaMateria->tipo_aula, $dia, $bloque, $matrices['aulaEspecialOcupada'], $seccionId
                        );
                        if ($aulaLibre === null) {
                            $razones[] = 'aula no disponible';
                        }
                    } else {
                        $aulaAsignada = $this->obtenerAulaNormal($seccionId);
                        if ($aulaAsignada === null) {
                            $razones[] = 'aula no disponible';
                        }
                    }

                    if (!empty($razones)) {
                        $espaciosFallidos[] = [
                            'dia' => Dia::from($dia)->label(),
                            'bloque' => $bloque,
                            'razones' => $razones,
                        ];
                    }
                }
            }

            return $espaciosFallidos;
        }

        //compara la cantidad actual de la seccion con la capacidad que tiene la aula
        public function validarCapacidadAula(int $aulaId, int $seccionId): bool
        {
            $clave = $aulaId . '_' . $seccionId;
            if (isset($this->cacheCapacidadAula[$clave])) {
                return $this->cacheCapacidadAula[$clave];
            }

            $aula = Aula::find($aulaId);
            $seccion = Seccion::find($seccionId);

            if (!$aula || !$seccion) {
                return $this->cacheCapacidadAula[$clave] = false;
            }

            if ($aula->capacidad_maxima === null) {
                return $this->cacheCapacidadAula[$clave] = true;
            }

            return $this->cacheCapacidadAula[$clave] = ($seccion->cantidad_actual <= $aula->capacidad_maxima);
        }
}
