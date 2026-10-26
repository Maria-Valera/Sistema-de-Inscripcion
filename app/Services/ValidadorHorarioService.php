<?php

namespace App\Services;

use App\Enums\Dia;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\BloqueReservado;
use App\Models\ConfiguracionHorario;
use App\Models\DocenteAreaGrado;
use App\Models\DocenteIndisponibilidad;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\seccion_aula;

class ValidadorHorarioService
{
    /**
     * Valida si los datos del horario son lógicamente viables antes de ejecutar el generador.
     * Este servicio es estrictamente de solo lectura y no escribe en la base de datos.
     *
     * @param int $anio ID del año escolar
     * @param int|null $gradoId ID del grado específico a validar (null = todos)
     * @return array{errores: array, avisos: array, info: array}
     */
    public function validar(int $anio, ?int $gradoId = null): array
    {
        $errores = [];
        $avisos = [];
        $info = [];

        // 1. Configuración de horario y bloques reservados
        $config = ConfiguracionHorario::where('anio_escolar_id', $anio)->first();
        $numeroBloques = $config ? (int) $config->numero_bloques : 8;
        $totalDias = 5;
        $totalBloquesSemana = $totalDias * $numeroBloques;

        $reservados = BloqueReservado::where('anio_escolar_id', $anio)->get();
        $reservadosMap = [];
        foreach ($reservados as $r) {
            $diaVal = $r->dia instanceof Dia ? $r->dia->value : (int) $r->dia;
            $bloqueVal = (int) $r->bloque;
            if ($diaVal >= 1 && $diaVal <= 5 && $bloqueVal >= 1 && $bloqueVal <= $numeroBloques) {
                $reservadosMap[$diaVal . '_' . $bloqueVal] = true;
            }
        }
        $bloquesReservados = count($reservadosMap);
        $bloquesDisponibles = max(0, $totalBloquesSemana - $bloquesReservados);

        // 2. Cargar grados y secciones en el ámbito seleccionado
        $gradosQuery = Grado::where('status', true);
        if ($gradoId !== null) {
            $gradosQuery->where('id', $gradoId);
        }
        $grados = $gradosQuery->with([
            'gradoAreaFormacion' => fn($q) => $q->where('status', true)->with('area_formacion.aula')
        ])->get();

        $seccionesQuery = Seccion::where('status', true)->with(['grado', 'aulaFija.aula']);
        if ($gradoId !== null) {
            $seccionesQuery->where('grado_id', $gradoId);
        }
        $secciones = $seccionesQuery->get();

        // 3. Cargar asignaciones de carga académica (DocenteAreaGrado)
        $dagQuery = DocenteAreaGrado::where('status', true)->with([
            'detalleDocenteEstudio.docente.persona',
            'areaEstudios.areaFormacion.aula',
            'grado',
            'seccion'
        ]);
        if ($gradoId !== null) {
            $dagQuery->where('grado_id', $gradoId);
        }
        $asignacionesDag = $dagQuery->get();

        // 4. ÁREAS: Validar bloques_semanales (Errores y Avisos)
        $areasMap = [];
        foreach ($grados as $g) {
            foreach ($g->gradoAreaFormacion as $gaf) {
                if ($gaf->area_formacion && $gaf->area_formacion->status) {
                    $areasMap[$gaf->area_formacion->id] = $gaf->area_formacion;
                }
            }
        }
        foreach ($asignacionesDag as $dag) {
            $af = $dag->areaEstudios?->areaFormacion;
            if ($af && $af->status) {
                $areasMap[$af->id] = $af;
            }
        }
        if (empty($areasMap) && $gradoId === null) {
            foreach (AreaFormacion::where('status', true)->get() as $af) {
                $areasMap[$af->id] = $af;
            }
        }

        foreach ($areasMap as $area) {
            $bloques = $area->bloques_semanales;
            if ($bloques === null) {
                $avisos[] = [
                    'codigo' => 'area_bloques_nulos',
                    'mensaje' => "El área de formación '{$area->nombre_area_formacion}' tiene bloques semanales sin definir (null).",
                    'detalle' => "El generador no podrá programarla hasta que se configure su carga horaria semanal.",
                ];
            } elseif ($bloques > 5 || $bloques <= 0) {
                $errores[] = [
                    'codigo' => 'area_bloques_invalidos',
                    'mensaje' => "El área de formación '{$area->nombre_area_formacion}' tiene {$bloques} bloques semanales configurados.",
                    'detalle' => "Debe tener entre 1 y 5 bloques semanales (máximo 1 bloque por día lectivo).",
                ];
            }
        }

        // 5. SECCIONES: Validar aula regular, capacidad, exceso de bloques, huecos y áreas sin docente
        $dagsPorSeccion = $asignacionesDag->groupBy('seccion_id');

        foreach ($secciones as $seccion) {
            $gradoNombre = $seccion->grado ? "{$seccion->grado->numero_grado}° año" : "Grado {$seccion->grado_id}";
            $dagsSeccion = $dagsPorSeccion->get($seccion->id, collect());

            // A) Validación de Aula Regular asignada y capacidad
            $seccionAula = $seccion->aulaFija;
            $aulaRegular = $seccionAula?->aula;

            if (!$seccionAula || !$aulaRegular || !$aulaRegular->status) {
                $errores[] = [
                    'codigo' => 'seccion_sin_aula_regular',
                    'mensaje' => "La sección {$seccion->nombre} ({$gradoNombre}) no tiene un aula regular asignada o activa.",
                    'detalle' => "Debe asignar un aula regular activa a la sección en la configuración de secciones y aulas.",
                ];
            } elseif ($seccion->cantidad_actual > $aulaRegular->capacidad_maxima) {
                $errores[] = [
                    'codigo' => 'seccion_supera_capacidad_aula',
                    'mensaje' => "La sección {$seccion->nombre} ({$gradoNombre}) tiene {$seccion->cantidad_actual} estudiantes, superando la capacidad máxima de {$aulaRegular->capacidad_maxima} del aula '{$aulaRegular->nombre_aula}'.",
                    'detalle' => "La matrícula actual excede la capacidad física del aula regular asignada.",
                ];
            }

            // B) Validación de áreas curriculares de la sección vs docente asignado
            $gradoDeSeccion = $grados->firstWhere('id', $seccion->grado_id);
            $materiasRequeridas = $gradoDeSeccion?->gradoAreaFormacion ?? collect();

            if ($materiasRequeridas->isNotEmpty()) {
                foreach ($materiasRequeridas as $gaf) {
                    $afCurricular = $gaf->area_formacion;
                    if (!$afCurricular || !$afCurricular->status) {
                        continue;
                    }

                    $dagMateria = $dagsSeccion->first(function ($d) use ($afCurricular) {
                        return $d->areaEstudios?->area_formacion_id == $afCurricular->id;
                    });

                    $docente = $dagMateria?->detalleDocenteEstudio?->docente;
                    if (!$dagMateria || !$docente || !$docente->status) {
                        $errores[] = [
                            'codigo' => 'area_sin_docente',
                            'mensaje' => "El área '{$afCurricular->nombre_area_formacion}' en la sección {$seccion->nombre} ({$gradoNombre}) no tiene docente asignado.",
                            'detalle' => "Debe asignar un docente activo en Carga Académica para esta sección y área de formación.",
                        ];
                    }
                }
            } else {
                foreach ($dagsSeccion as $dag) {
                    $docente = $dag->detalleDocenteEstudio?->docente;
                    $af = $dag->areaEstudios?->areaFormacion;
                    $nombreMateria = $af?->nombre_area_formacion ?? 'Materia';
                    if (!$docente || !$docente->status) {
                        $errores[] = [
                            'codigo' => 'area_sin_docente',
                            'mensaje' => "El área '{$nombreMateria}' en la sección {$seccion->nombre} ({$gradoNombre}) no tiene docente asignado.",
                            'detalle' => "Debe asignar un docente activo en Carga Académica para esta sección y área de formación.",
                        ];
                    }
                }
            }

            // C) Suma de bloques de las áreas de la sección
            $areasSeccionUnicas = [];
            foreach ($dagsSeccion as $dag) {
                $af = $dag->areaEstudios?->areaFormacion;
                if ($af && $af->status) {
                    $areasSeccionUnicas[$af->id] = (int) ($af->bloques_semanales ?? 0);
                }
            }
            $sumaBloquesSeccion = array_sum($areasSeccionUnicas);

            if ($sumaBloquesSeccion > $bloquesDisponibles) {
                $errores[] = [
                    'codigo' => 'seccion_exceso_bloques',
                    'mensaje' => "La sección {$seccion->nombre} ({$gradoNombre}) requiere {$sumaBloquesSeccion} bloques semanales, superando los {$bloquesDisponibles} disponibles.",
                    'detalle' => "Capacidad semanal: {$totalBloquesSemana} bloques menos {$bloquesReservados} bloques reservados institucionalmente.",
                ];
            } elseif ($sumaBloquesSeccion > 0 && $sumaBloquesSeccion < $bloquesDisponibles) {
                $huecos = $bloquesDisponibles - $sumaBloquesSeccion;
                $avisos[] = [
                    'codigo' => 'seccion_con_huecos',
                    'mensaje' => "La sección {$seccion->nombre} ({$gradoNombre}) tiene {$sumaBloquesSeccion} bloques asignados de {$bloquesDisponibles} disponibles ({$huecos} huecos libres).",
                    'detalle' => "Quedarán {$huecos} bloques sin actividad académica durante la semana para esta sección.",
                ];
            }
        }

        // 6. DOCENTES: Validar bloques requeridos > 18, espacios libres y saturación (<10%)
        $docentesData = [];
        foreach ($asignacionesDag as $dag) {
            $docente = $dag->detalleDocenteEstudio?->docente;
            if (!$docente || !$docente->status) {
                continue;
            }
            $af = $dag->areaEstudios?->areaFormacion;
            $bloques = $af ? (int) ($af->bloques_semanales ?? 0) : 0;

            if (!isset($docentesData[$docente->id])) {
                $persona = $docente->persona;
                $rawNombre = $docente->nombre_completo 
                    ?: trim(($persona?->primer_nombre ?? '') . ' ' . ($persona?->primer_apellido ?? ''));
                $nombreDocente = preg_replace('/\s+/', ' ', trim($rawNombre));
                if (empty($nombreDocente)) {
                    $nombreDocente = "Docente ID {$docente->id}";
                }

                $docentesData[$docente->id] = [
                    'docente' => $docente,
                    'nombre' => $nombreDocente,
                    'horas_academicas' => (int) ($docente->horas_academicas ?? 36),
                    'bloques_requeridos' => 0,
                ];
            }
            $docentesData[$docente->id]['bloques_requeridos'] += $bloques;
        }

        $docentesIds = array_keys($docentesData);
        $indisponibilidades = DocenteIndisponibilidad::whereIn('docente_id', $docentesIds)
            ->where('anio_escolar_id', $anio)
            ->where('estado', 'aprobada')
            ->where('tipo', 'prohibido')
            ->get()
            ->groupBy('docente_id');

        foreach ($docentesData as $docId => $dData) {
            $bloquesReq = $dData['bloques_requeridos'];
            $nombreDoc = $dData['nombre'];

            // A) Más de 18 bloques requeridos (Tope legal/institucional)
            if ($bloquesReq > 18) {
                $errores[] = [
                    'codigo' => 'docente_excede_maximo_bloques',
                    'mensaje' => "El docente {$nombreDoc} tiene asignados {$bloquesReq} bloques semanales, superando el límite institucional de 18 bloques (36 horas académicas).",
                    'detalle' => "El máximo legal permitido por docente es de 18 bloques por semana.",
                ];
            }

            // B) Calcular espacios libres (restando indisponibilidad y reservados)
            $celdasBloqueadas = $reservadosMap;
            $indisDocente = $indisponibilidades->get($docId, collect());
            foreach ($indisDocente as $ind) {
                $d = $ind->dia instanceof Dia ? $ind->dia->value : (int) $ind->dia;
                $b = (int) $ind->bloque;
                if ($d >= 1 && $d <= 5 && $b >= 1 && $b <= $numeroBloques) {
                    $celdasBloqueadas[$d . '_' . $b] = true;
                }
            }
            $espaciosLibres = max(0, $totalBloquesSemana - count($celdasBloqueadas));

            if ($bloquesReq > $espaciosLibres) {
                $errores[] = [
                    'codigo' => 'docente_sin_espacios_suficientes',
                    'mensaje' => "El docente {$nombreDoc} requiere {$bloquesReq} bloques pero solo dispone de {$espaciosLibres} espacios libres en la semana.",
                    'detalle' => "De {$totalBloquesSemana} bloques semanales posibles, tiene " . count($celdasBloqueadas) . " bloqueados por indisponibilidad y reservas institucionales.",
                ];
            }

            // C) Aviso: Docente con menos del 10% de margen de carga (casi saturado)
            $maxBloquesHoras = min(intdiv($dData['horas_academicas'], 2), 18);
            $topeEfectivo = min($maxBloquesHoras, $espaciosLibres);

            if ($bloquesReq > 0 && $bloquesReq <= $topeEfectivo && $topeEfectivo > 0) {
                $margen = $topeEfectivo - $bloquesReq;
                if (($margen / $topeEfectivo) < 0.10) {
                    $porcentaje = round(($bloquesReq / $topeEfectivo) * 100);
                    $avisos[] = [
                        'codigo' => 'docente_casi_saturado',
                        'mensaje' => "El docente {$nombreDoc} se encuentra al {$porcentaje}% de su capacidad máxima ({$bloquesReq}/{$topeEfectivo} bloques, margen menor al 10%).",
                        'detalle' => "Tiene solo {$margen} bloque(s) de margen libre, lo que reduce la flexibilidad para resolver colisiones horarias.",
                    ];
                }
            }
        }

        // 7. AULAS ESPECIALIZADAS: Demanda vs Oferta
        $demandaAulasEspeciales = [];
        foreach ($asignacionesDag as $dag) {
            $af = $dag->areaEstudios?->areaFormacion;
            if (!$af || !$af->status) {
                continue;
            }
            $aula = $af->aula;
            if ($aula && !Aula::esTipoRegular($aula->tipo_aula)) {
                $tipo = trim($aula->tipo_aula);
                $demandaAulasEspeciales[$tipo] = ($demandaAulasEspeciales[$tipo] ?? 0) + (int) ($af->bloques_semanales ?? 0);
            }
        }

        foreach ($demandaAulasEspeciales as $tipoAula => $demanda) {
            $aulasActivas = Aula::where('tipo_aula', $tipoAula)->where('status', true)->count();
            $oferta = $bloquesDisponibles * $aulasActivas;

            if ($demanda > $oferta) {
                $errores[] = [
                    'codigo' => 'demanda_aula_especializada_excedida',
                    'mensaje' => "La demanda del tipo de aula '{$tipoAula}' ({$demanda} bloques) supera la oferta disponible ({$oferta} bloques).",
                    'detalle' => "Hay {$aulasActivas} aula(s) activa(s) de tipo '{$tipoAula}' que ofrecen {$bloquesDisponibles} bloques útiles cada una (total {$oferta} bloques).",
                ];
            }
        }

        // 8. INFO: Resúmenes de carga
        $totalDocentes = count($docentesData);
        $info[] = [
            'codigo' => 'resumen_carga_docentes',
            'mensaje' => "Resumen de carga docente: {$totalDocentes} docente(s) analizados.",
            'detalle' => $totalDocentes > 0
                ? collect($docentesData)->map(fn($d) => "{$d['nombre']}: {$d['bloques_requeridos']} bloques")->implode('; ')
                : "No se encontraron docentes asignados en este ámbito.",
        ];

        $totalSecciones = $secciones->count();
        $info[] = [
            'codigo' => 'resumen_carga_secciones',
            'mensaje' => "Resumen de carga por sección: {$totalSecciones} sección(es) analizadas.",
            'detalle' => $totalSecciones > 0
                ? $secciones->map(function ($s) use ($dagsPorSeccion, $bloquesDisponibles) {
                    $dags = $dagsPorSeccion->get($s->id, collect());
                    $areasUnicas = [];
                    foreach ($dags as $d) {
                        $af = $d->areaEstudios?->areaFormacion;
                        if ($af && $af->status) {
                            $areasUnicas[$af->id] = (int) ($af->bloques_semanales ?? 0);
                        }
                    }
                    $bSum = array_sum($areasUnicas);
                    return "{$s->nombre}: {$bSum}/{$bloquesDisponibles} bloques";
                })->implode('; ')
                : "No se encontraron secciones en este ámbito.",
        ];

        return [
            'errores' => $errores,
            'avisos' => $avisos,
            'info' => $info,
        ];
    }
}
