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
        //trae todas las relacciones del docente

        $query = DocenteAreaGrado::with([
            'detalleDocenteEstudio.docente.persona',
            'areaEstudios.areaFormacion.aula', 
            'grado',    
            'seccion'
        ]);
        //filtro grado  y seccion
        if ($gradoId !== null) {
            $query->where('grado_id', $gradoId);
        }
        //filtro area de dformacion
        if ($areaFormacionId !== null) {
            $query->whereHas('areaEstudios', function ($q) use ($areaFormacionId) {
                $q->where('area_formacion_id', $areaFormacionId);
            });
        }
        
        $asignaciones = $query->get();
        
        return $asignaciones->map(function ($fila){
            $detalle=$fila->detalleDocenteEstudio;
            $docente= $detalle?->docente;
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
        //marcar y guardar quien esta ocupado y restricciones de cada docente
        $docenteOcupado = [];
        $seccionOcupado = [];
        $aulaEspecialOcupada = [];
        $bloquesPorDocente = [];

        //busca los dias y bloques de hora que el docente no puede ser asignado
        $noDisponible = DocenteNoDisponibilidad::where('anio_escolar_id', $anioEscolar)->get();
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

    
    public function calcularMaxBloques(int $horasAcademicasSemanales, int $minutosPorHoraAcademica = 45, int $minutosPorBloque = 80): int
    {
        //Convertir horas academicas en bloques de hora
        return (int) floor(($horasAcademicasSemanales * $minutosPorHoraAcademica) / $minutosPorBloque);
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
                $aulaRegular = Aula::where('tipo_aula', 'Aula Regular')
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


    //asigna un bloque al primer hueco que cumpla los requisitos
    public function asignarUnBloque(array &$clase, array &$matrices, int $totalDias, int $totalBloques, int $maxBloquesDocente, array &$bloquesPorDiaGlobal = []): ?array
        {
            $docenteId = $clase['docente_id'];
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

            $diasUsados = 0;
            for ($d = 1; $d <= $totalDias; $d++) {
                $claveDiaCheck = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $d;
                if (!empty($bloquesPorDiaGlobal[$claveDiaCheck])) {
                    $diasUsados++;
                }
            }

            if (!isset($matrices['bloquesPorDocente'][$docenteId])) {
                $matrices['bloquesPorDocente'][$docenteId] = 0;
            }

            //busca si el area necesita un aula especializada(ejem. Edu fisica)
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';

            //por cada dia y bloque se asegura que la seccion no haya pasado el limite diario de bloques
            for ($dia = 1; $dia <= $totalDias; $dia++) {
                for ($bloque = 1; $bloque <= $totalBloques; $bloque++) {

                    $claveGlobal = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $dia;
                    $bloquesUsadosHoy = $bloquesPorDiaGlobal[$claveGlobal] ?? [];

                    //cuenta los dias que se dio el area en la seccion
                    if (empty($bloquesUsadosHoy) && $diasUsados >= $diasMaxSemana) {
                        continue;
                    }

                    if (count($bloquesUsadosHoy) >= $maxBloquesPorDia) continue;

                    //verifica que haya continuidad de bloques con su area
                    if (!empty($bloquesUsadosHoy)) {
                        $minBloque = min($bloquesUsadosHoy);
                        $maxBloque = max($bloquesUsadosHoy);
                        if ($bloque !== $minBloque - 1 && $bloque !== $maxBloque + 1) {
                            continue;
                        }
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

                    //asigna marcando todo como ocupado para las siguientes iteraciones
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

                    $bloquesPorDiaGlobal[$claveGlobal][] = $bloque;

                    return $asignacion;
                }
            }

            return null;
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
                HorarioAsignacion::where('anio_escolar_id', $anioEscolar)->delete();

                //cargar los dato, las restricciones y las prioridades
                $clases = $this->PreparasDatos($gradoId, $areaFormacionId);
                $matrices = $this->InicializarMatrices($anioEscolar);
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
        private function determinarTipoConflicto(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

            $diasUsados = 0;
            for ($d = 1; $d <= $totalDias; $d++) {
                $claveDiaCheck = $clase['area_id'] . '_' . $clase['seccion_id'] . '_' . $d;
                if (!empty($bloquesPorDiaGlobal[$claveDiaCheck])) {
                    $diasUsados++;
                }
            }

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

                    if (!isset($matrices['docenteOcupado'][$claveDocente])) {
                        $espaciosLibresDocente++;
                    }

                    if (!isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $espaciosLibresSeccion++;
                    }

                    if ($bloquesHoy < $maxBloquesPorDia) {
                        $espaciosLibresPorLimiteDia++;
                    }

                    if (!$diaYaUsado || $diasUsados < $diasMaxSemana) {
                        $espaciosLibresPorLimiteDiasSemana++;
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
                }
            }
            
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

        // da un listado del diaybloque que no pudo asignarse
        private function identificarEspaciosFallidos(array $clase, array $matrices, int $totalDias, int $totalBloques, array $bloquesPorDiaGlobal): array
        {
            $docenteId = $clase['docente_id'];
            $seccionId = $clase['seccion_id'];
            $aulaFijaMateria = $clase['area_aula'] ?? null;
            $esEspecializada = $aulaFijaMateria && $aulaFijaMateria->tipo_aula !== 'Aula Regular';
            $maxBloquesPorDia = $clase['bloques_max_dia'] ?? 2;
            $diasMaxSemana = $clase['dias_max_semana'] ?? 3;

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

                    if (isset($matrices['docenteOcupado'][$claveDocente])) {
                        $razones[] = 'docente ocupado';
                    }

                    if (isset($matrices['seccionOcupado'][$claveSeccion])) {
                        $razones[] = 'sección ocupada';
                    }

                    if ($bloquesHoy >= $maxBloquesPorDia) {
                        $razones[] = 'límite diario alcanzado';
                    }

                    if (!$diaYaUsado && $diasUsados >= $diasMaxSemana) {
                        $razones[] = 'límite de días semanales alcanzado';
                    }

                    $bloquesUsadosHoy = $bloquesPorDiaGlobal[$claveGlobal] ?? [];
                    if (!empty($bloquesUsadosHoy)) {
                        $minBloque = min($bloquesUsadosHoy);
                        $maxBloque = max($bloquesUsadosHoy);
                        if ($bloque !== $minBloque - 1 && $bloque !== $maxBloque + 1) {
                            $razones[] = 'no cumple continuidad (no es adyacente)';
                        }
                    }

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
