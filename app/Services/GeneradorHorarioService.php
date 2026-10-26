<?php

namespace App\Services;

use App\Enums\Dia;
use App\Models\BloqueReservado;
use App\Models\CargaAcademica;
use App\Models\DocenteAreaGrado;
use App\Models\DocenteIndisponibilidad;
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

        return [
            'docenteOcupado' => $docenteOcupado,
            'seccionOcupado' => $seccionOcupado,
            'aulaEspecialOcupada' => $aulaEspecialOcupada,
            'bloquesPorDocente' => $bloquesPorDocente,
            'reservado' => $reservado,
            'materiaDia' => $materiaDia,
            'docenteDia' => $docenteDia,
            'docenteNoPreferente' => $docenteNoPreferente,
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
            //busca el aula asignada, si no busca otra disponible
            $seccionAula = seccion_aula::where('id_seccion', $seccionId)
                ->where('status', true)
                ->first();

                
            if (!$seccionAula) {
                $aulaRegular = Aula::where('tipo_aula', Aula::TIPO_REGULAR)
                    ->where('status', true)
                    ->first();
                
                    // valida la capacidad maxima de la seccion
                if ($aulaRegular && !$this->validarCapacidadAula($aulaRegular->id_aula, $seccionId)) {
                    return null;
                }
                
                return $aulaRegular?->id_aula;
            }

            if ($seccionAula && !$this->validarCapacidadAula($seccionAula->id_aula, $seccionId)) {
                return null;
            }

            return $seccionAula?->id_aula;
        }

        //para areas con aulas especializadas como edu
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
                if ($this->validarCapacidadAula($aula->id_aula, $seccionId)) {
                    return $aula->id_aula;
                }
            }
        }

        return null;
    }


    //asigna un bloque al hueco con menor costo que cumpla los requisitos (M5 / P6)
    public function asignarUnBloque(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente, array &$bloquesPorDiaGlobal = []): ?array
    {
        $docenteId = $clase['docente_id'];

        if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
            $matrices['bloquesPorDocente'][$docenteId] = 0;
        }

        //busca si el area necesita un aula especializada (ejem. Edu fisica)
        $aulaFijaMateria = $clase['area_aula'] ?? null;
        $esEspecializada = $aulaFijaMateria && !Aula::esTipoRegular($aulaFijaMateria->tipo_aula);

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

                $claveDocente = $docenteId . '_' . $dia . '_' . $bloque;
                $claveSeccion = $clase['seccion_id'] . '_' . $dia . '_' . $bloque;

                //verifica que el docente, la seccion no hayan roto alguna restriccion o si el docente ya agoto sus horas
                if (isset($matrices['docenteOcupado'][$claveDocente])) continue;
                if (isset($matrices['seccionOcupado'][$claveSeccion])) continue;

                if ($matrices['bloquesPorDocente'][$docenteId] >= $maxBloquesDocente) continue;

                $aulaAsignada = null;

                //busca el aula correspondiente al area
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

                // ===== CÁLCULO DE COSTO (M5) =====
                // 1. costoPosicion: 0 por ahora (punto de extensión P11). Si indisponibilidad no_preferente, suma 1.
                $costoPosicion = 0;
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
            $dia = $asig['dia'] ?? $asig['dia_id'] ?? null;
            $bloque = $asig['bloque'] ?? $asig['bloque_id'] ?? null;

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

                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->get();
            foreach ($noDisponible as $nd) {
                $clave = $nd->docente_id . '_' . $nd->dias_semana_id . '_' . $nd->id_bloque_hora;
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
                        'dia_id' => $dia,
                        'bloque_id' => $bloque,
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

                if (!isset($bloquesPorDocente[$asignacion['docente_id']])) {
                    $bloquesPorDocente[$asignacion['docente_id']] = 0;
                }
                $bloquesPorDocente[$asignacion['docente_id']]++;
            }

            $noDisponible = DocenteNoDisponibilidad::where('docente_id', $docenteId)
                ->where('anio_escolar_id', $anioEscolar)
                ->get();
            foreach ($noDisponible as $nd) {
                $clave = $nd->docente_id . '_' . $nd->dias_semana_id . '_' . $nd->id_bloque_hora;
                $docenteOcupado[$clave] = true;
            }

            foreach ($asignacionesExistentes as $index => $otraAsignacion) {
                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId &&
                    $otraAsignacion['dia_id'] == $diaActual &&
                    $otraAsignacion['bloque_id'] == $bloqueActual) {
                    continue;
                }

                if ($otraAsignacion['docente_id'] == $docenteId &&
                    $otraAsignacion['seccion_id'] == $seccionId) {
                    continue;
                }

                $otroDocenteId = $otraAsignacion['docente_id'];
                $otraSeccionId = $otraAsignacion['seccion_id'];
                $otroDia = $otraAsignacion['dia_id'];
                $otroBloque = $otraAsignacion['bloque_id'];
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

    public function generar(int $anioEscolar, int $totalDias, int $totalBloques, ?int $gradoId = null, ?int $areaFormacionId = null): array
        {
            //recorre todo dentro de una transacion
            return \DB::transaction(function() use ($anioEscolar, $totalDias, $totalBloques, $gradoId, $areaFormacionId) {
                //borra solo lo que se va a regenerar (todo el año, solo el grado y/o solo el área), respetando clases fijadas
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

                //cargar los datos, las restricciones y las prioridades
                $clases = $this->PreparasDatos($gradoId, $areaFormacionId, $anioEscolar);
                $matrices = $this->InicializarMatrices($anioEscolar);

                //las asignaciones que se conservan (fuera del alcance o fijadas) ocupan docente, sección y aula
                $conservadas = HorarioAsignacion::where('anio_escolar_id', $anioEscolar)
                    ->with('cargaAcademica')
                    ->get();
                foreach ($conservadas as $c) {
                    $cDia = $c->dia ?? $c->dia_id;
                    $cBloque = $c->bloque ?? $c->bloque_id;
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
                }

                $clasesOrdenadas = $this->ordenarClasesPendientes($clases, $matrices['docenteOcupado'], $totalDias, $totalBloques);

                $todasLasAsignaciones = [];
                $todosLosConflictos = [];
                
                $bloquesPorDiaGlobal = [];

                $clasesPendientes = [];
                foreach ($clasesOrdenadas as $clase) {
                    $claseArray = $clase;
                    $claseArray['bloques_restantes'] = $clase['area_bloque'];
                    $clasesPendientes[] = $claseArray;
                }

                
                //bucle asigna un bloque por clase en cada ronda
                do {
                    $huboAvanceEnRonda = false;

                    foreach ($clasesPendientes as &$clase) {
                        //solo procesa clases que aún necesitan bloques
                        if ($clase['bloques_restantes'] <= 0) {
                            continue;
                        }

                        $maxBloques = $this->calcularMaxBloques($clase['docente_horas_academicas'] ?? 36);

                        //intenta asignar un bloque para esta clase
                        $asignacion = $this->asignarUnBloque($clase, $matrices, $totalDias, $totalBloques, $maxBloques, $bloquesPorDiaGlobal);

                        if ($asignacion !== null) {
                            $aulaId = $asignacion['aula_id'] > 0 ? $asignacion['aula_id'] : null;
                            
                            //guarda la asignación en la base de datos
                            HorarioAsignacion::create([
                                'anio_escolar_id' => $anioEscolar,
                                'carga_academica_id' => $clase['carga_academica_id'],
                                'aula_id' => $aulaId,
                                'dia' => $asignacion['dia'],
                                'bloque' => $asignacion['bloque'],
                                'conflicto_manual' => false,
                            ]);

                            $todasLasAsignaciones[] = $asignacion;
                            $clase['bloques_restantes']--;
                            $huboAvanceEnRonda = true;
                        }
                    }
                    unset($clase);

                } while ($huboAvanceEnRonda && $this->hayClasesPendientes($clasesPendientes));

                //genera conflictos para las clases que no se pudieron asignar completamente
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
                ];
            });
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
