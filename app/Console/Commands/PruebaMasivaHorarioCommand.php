<?php

namespace App\Console\Commands;

use App\Models\AnioEscolar;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\Docente;
use App\Models\HorarioAsignacion;
use App\Models\BloqueReservado;
use App\Models\ConfiguracionHorario;
use App\Services\GeneradorHorarioService;
use Illuminate\Console\Command;

class PruebaMasivaHorarioCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horario:prueba-masiva {--intentos=10 : Número de intentos por grado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta la prueba de fuego de generación masiva de horarios y verificación de integridad.';

    /**
     * Execute the console command.
     */
    public function handle(GeneradorHorarioService $generador): int
    {
        $this->info("════════════════════════════════════════════════════════════════════════════════");
        $this->info("    TAREA P13: PRUEBA DE FUEGO - GENERACIÓN MASIVA Y VERIFICACIÓN DEL MOTOR    ");
        $this->info("════════════════════════════════════════════════════════════════════════════════");

        $anio = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first()
            ?? AnioEscolar::latest('id')->first();

        if (!$anio) {
            $this->error("No se encontró un año escolar activo o extendido.");
            return Command::FAILURE;
        }

        $config = ConfiguracionHorario::where('anio_escolar_id', $anio->id)->first();
        $totalBloques = $config ? (int)$config->numero_bloques : 7;
        $totalDias = 5;
        $intentos = (int) $this->option('intentos');

        $periodoNombre = ($anio->inicio_anio_escolar && $anio->cierre_anio_escolar)
            ? $anio->inicio_anio_escolar->format('Y') . '-' . $anio->cierre_anio_escolar->format('Y')
            : "Periodo #{$anio->id}";

        $this->line("Año escolar activo: <comment>ID {$anio->id} ({$periodoNombre})</comment>");
        $this->line("Configuración: <comment>{$totalDias} días lectivos, {$totalBloques} bloques diarios, {$intentos} intentos por grado</comment>");

        $grados = Grado::orderBy('numero_grado')->get();
        $this->line("Grados a procesar: <comment>{$grados->count()}</comment>\n");

        // Registrar clases fijadas existentes para verificar conservación
        $fijadasPrevias = HorarioAsignacion::where('anio_escolar_id', $anio->id)
            ->where('fijada', true)
            ->get(['id', 'carga_academica_id', 'dia', 'bloque']);

        $this->line("Clases fijadas detectadas antes de iniciar: <comment>{$fijadasPrevias->count()}</comment>");

        $tiempoInicioGlobal = microtime(true);
        $resumenGrados = [];
        $totalAsignaciones = 0;
        $totalConflictos = 0;
        $rechazosDocentesGlobales = [];
        $mejorScoreGlobal = 0;

        foreach ($grados as $grado) {
            $this->line("────────────────────────────────────────────────────────────────────────────────");
            $this->info("Generando Horario para: {$grado->numero_grado}° Año de Educación Media...");

            $tInicioGrado = microtime(true);
            $resultado = $generador->generar(
                $anio->id,
                $totalDias,
                $totalBloques,
                $grado->id,
                null,
                $intentos
            );
            $tFinGrado = microtime(true);
            $tiempoGrado = round($tFinGrado - $tInicioGrado, 2);

            $numAsigs = count($resultado['asignaciones']);
            $numConflicts = count($resultado['conflictos']);
            $score = $resultado['puntaje'];
            $huecos = $resultado['huecos'];

            $totalAsignaciones += $numAsigs;
            $totalConflictos += $numConflicts;
            $mejorScoreGlobal += $score;

            // Recolectar rechazos de diagnóstico
            $topDocs = $resultado['diagnostico']['docentes'] ?? [];
            foreach ($topDocs as $td) {
                $docId = $td['docente_id'];
                $rechazosDocentesGlobales[$docId] = [
                    'nombre' => $td['nombre'],
                    'rechazos' => ($rechazosDocentesGlobales[$docId]['rechazos'] ?? 0) + $td['rechazos']
                ];
            }

            $cumpleTiempo = $tiempoGrado <= 20.0;
            $badgeTiempo = $cumpleTiempo ? "<info>{$tiempoGrado}s (✓ <20s)</info>" : "<error>{$tiempoGrado}s (✗ >20s)</error>";

            $resumenGrados[] = [
                'grado' => "{$grado->numero_grado}° Año",
                'tiempo' => "{$tiempoGrado}s",
                'asignaciones' => $numAsigs,
                'conflictos' => $numConflicts,
                'huecos' => $huecos,
                'score' => $score,
                'estado' => $cumpleTiempo ? '✓ ÓPTIMO' : '⚠ LENTO',
            ];

            $this->line("  Tiempo: {$badgeTiempo} | Asignaciones: <comment>{$numAsigs}</comment> | Conflictos: <comment>{$numConflicts}</comment> | Score: <comment>{$score}</comment>");
        }

        $tiempoTotalGlobal = round(microtime(true) - $tiempoInicioGlobal, 2);

        $this->newLine();
        $this->info("════════════════════════════════════════════════════════════════════════════════");
        $this->info("                   1. REPORTE DE GENERACIÓN MASIVA                             ");
        $this->info("════════════════════════════════════════════════════════════════════════════════");

        $this->table(
            ['Grado', 'Tiempo', 'Asignaciones', 'Conflictos', 'Huecos', 'Puntaje', 'Estado'],
            $resumenGrados
        );

        $this->line("• <info>Tiempo Total de Generación:</info> {$tiempoTotalGlobal}s (Promedio: " . round($tiempoTotalGlobal / max(1, $grados->count()), 2) . "s por grado)");
        $this->line("• <info>Total de Asignaciones Generadas:</info> {$totalAsignaciones}");
        $this->line("• <info>Total de Conflictos:</info> {$totalConflictos}");
        $this->line("• <info>Puntaje Acumulado de Calidad:</info> {$mejorScoreGlobal}");

        // Métricas de huecos por sección
        $this->newLine();
        $this->info("─── Detalle de Huecos por Sección ───────────────────────────────────────────────");
        $secciones = Seccion::with('grado')
            ->where('status', true)
            ->whereHas('grado')
            ->orderBy('grado_id')
            ->orderBy('nombre')
            ->get();
        
        $reservadosMap = [];
        foreach (BloqueReservado::where('anio_escolar_id', $anio->id)->get() as $br) {
            $diaNum = $br->dia instanceof \App\Enums\Dia ? $br->dia->value : (int) $br->dia;
            $reservadosMap["{$diaNum}_{$br->bloque}"] = true;
        }

        $filasSecciones = [];
        $totalHuecosSecciones = 0;

        foreach ($secciones as $sec) {
            $asigsSec = HorarioAsignacion::where('anio_escolar_id', $anio->id)
                ->whereHas('cargaAcademica', fn($q) => $q->where('seccion_id', $sec->id))
                ->get(['dia', 'bloque', 'fijada'])
                ->map(fn($a) => [
                    'dia' => $a->dia instanceof \App\Enums\Dia ? $a->dia->value : (int) $a->dia,
                    'bloque' => (int) $a->bloque,
                    'fijada' => (bool) $a->fijada,
                    'seccion_id' => $sec->id,
                ])
                ->toArray();

            $huecosSec = $generador->contarHuecos($asigsSec, $reservadosMap, [$sec->id]);
            $totalHuecosSecciones += $huecosSec;

            $filasSecciones[] = [
                'grado' => ($sec->grado ? "{$sec->grado->numero_grado}° Año" : 'N/A'),
                'seccion' => $sec->nombre,
                'clases_asignadas' => count($asigsSec),
                'huecos' => $huecosSec,
                'estado' => $huecosSec === 0 ? '<info>0 (Compacto)</info>' : "<comment>{$huecosSec}</comment>",
            ];
        }

        $this->table(['Grado', 'Sección', 'Clases Asignadas', 'Huecos', 'Estado'], $filasSecciones);
        $this->line("• <info>Total Huecos en el Colegio:</info> {$totalHuecosSecciones}");

        // Top 5 docentes con más rechazos
        $this->newLine();
        $this->info("─── Top 5 Docentes con Más Rechazos (Diagnóstico de Cuellos de Botella) ─────────");
        uasort($rechazosDocentesGlobales, fn($a, $b) => $b['rechazos'] <=> $a['rechazos']);
        $top5Docentes = array_slice($rechazosDocentesGlobales, 0, 5, true);

        $filasTop5 = [];
        $rank = 1;
        foreach ($top5Docentes as $docId => $info) {
            $filasTop5[] = [
                'puesto' => "#{$rank}",
                'docente_id' => $docId,
                'nombre' => $info['nombre'],
                'rechazos' => $info['rechazos'],
            ];
            $rank++;
        }
        $this->table(['Ranking', 'Docente ID', 'Nombre', 'Rechazos Registrados'], $filasTop5);

        // ══════════════════════════════════════════════════════════════════
        // 2. VERIFICACIÓN DE INTEGRIDAD
        // ══════════════════════════════════════════════════════════════════
        $this->newLine();
        $this->info("════════════════════════════════════════════════════════════════════════════════");
        $this->info("                   2. VERIFICACIONES DE INTEGRIDAD ESTRICTA                    ");
        $this->info("════════════════════════════════════════════════════════════════════════════════");

        $todasAsignaciones = HorarioAsignacion::where('anio_escolar_id', $anio->id)
            ->with(['cargaAcademica.docente.persona', 'cargaAcademica.seccion.grado'])
            ->get();

        // 1. Bloques reservados
        $reservadosDB = BloqueReservado::where('anio_escolar_id', $anio->id)->get();
        $violacionesReservados = 0;
        foreach ($reservadosDB as $r) {
            $rDia = $r->dia instanceof \App\Enums\Dia ? $r->dia->value : (int) $r->dia;
            $enConflicto = $todasAsignaciones->filter(function($a) use ($rDia, $r) {
                $aDia = $a->dia instanceof \App\Enums\Dia ? $a->dia->value : (int) $a->dia;
                return $aDia === $rDia && (int)$a->bloque === (int)$r->bloque;
            });
            $violacionesReservados += $enConflicto->count();
        }

        // 2. Tope de 18 bloques por docente
        $docentesExcedidos = [];
        $cargasPorDocente = $todasAsignaciones->groupBy(fn($a) => $a->cargaAcademica?->docente_id);
        foreach ($cargasPorDocente as $docId => $asigs) {
            if ($docId && count($asigs) > 18) {
                $doc = $asigs->first()->cargaAcademica?->docente;
                $nomDoc = $doc && $doc->persona ? "{$doc->persona->primer_nombre} {$doc->persona->primer_apellido}" : "Docente #{$docId}";
                $docentesExcedidos[] = "{$nomDoc} (" . count($asigs) . " bloques)";
            }
        }

        // 3. Clases fijadas conservadas
        $fijadasModificadas = 0;
        foreach ($fijadasPrevias as $fp) {
            $actual = HorarioAsignacion::find($fp->id);
            $fpDia = $fp->dia instanceof \App\Enums\Dia ? $fp->dia->value : (int) $fp->dia;
            $actDia = $actual && $actual->dia instanceof \App\Enums\Dia ? $actual->dia->value : (int) $actual?->dia;
            if (!$actual || $actDia !== $fpDia || (int)$actual->bloque !== (int)$fp->bloque || !$actual->fijada) {
                $fijadasModificadas++;
            }
        }

        // 4. Choques de sección o docente en la misma sección/día/bloque
        $solapamientosDocentes = 0;
        $solapamientosSecciones = 0;
        $mapDocentes = [];
        $mapSecciones = [];

        foreach ($todasAsignaciones as $asig) {
            $d = $asig->dia instanceof \App\Enums\Dia ? $asig->dia->value : (int)$asig->dia;
            $b = (int)$asig->bloque;
            $docId = $asig->cargaAcademica?->docente_id;
            $secId = $asig->cargaAcademica?->seccion_id;

            if ($docId) {
                $keyDoc = "{$docId}_{$d}_{$b}";
                if (isset($mapDocentes[$keyDoc])) $solapamientosDocentes++;
                $mapDocentes[$keyDoc] = true;
            }

            if ($secId) {
                $keySec = "{$secId}_{$d}_{$b}";
                if (isset($mapSecciones[$keySec])) $solapamientosSecciones++;
                $mapSecciones[$keySec] = true;
            }
        }

        $verificaciones = [
            [
                'regla' => '1. Ninguna asignación en bloque reservado institucional',
                'esperado' => '0 violaciones',
                'obtenido' => "{$violacionesReservados} violaciones",
                'estado' => $violacionesReservados === 0 ? '<info>✓ APROBADO</info>' : '<error>✗ FALLO</error>',
            ],
            [
                'regla' => '2. Tope máximo de 18 bloques por docente semanal',
                'esperado' => '0 excesos',
                'obtenido' => count($docentesExcedidos) === 0 ? '0 excesos' : implode(', ', $docentesExcedidos),
                'estado' => count($docentesExcedidos) === 0 ? '<info>✓ APROBADO</info>' : '<error>✗ FALLO</error>',
            ],
            [
                'regla' => '3. Clases fijadas conservadas en su día y bloque',
                'esperado' => '0 alteradas',
                'obtenido' => "{$fijadasModificadas} alteradas",
                'estado' => $fijadasModificadas === 0 ? '<info>✓ APROBADO</info>' : '<error>✗ FALLO</error>',
            ],
            [
                'regla' => '4a. Cero solapamientos de docentes (mismo docente en dos secciones)',
                'esperado' => '0 conflictos',
                'obtenido' => "{$solapamientosDocentes} solapamientos",
                'estado' => $solapamientosDocentes === 0 ? '<info>✓ APROBADO</info>' : '<error>✗ FALLO</error>',
            ],
            [
                'regla' => '4b. Cero solapamientos de secciones (misma sección con dos clases simultáneas)',
                'esperado' => '0 conflictos',
                'obtenido' => "{$solapamientosSecciones} solapamientos",
                'estado' => $solapamientosSecciones === 0 ? '<info>✓ APROBADO</info>' : '<error>✗ FALLO</error>',
            ],
        ];

        $this->table(['Regla de Integridad', 'Esperado', 'Resultado Obtenido', 'Estado'], $verificaciones);

        $todasAprobadas = ($violacionesReservados === 0)
            && (count($docentesExcedidos) === 0)
            && ($fijadasModificadas === 0)
            && ($solapamientosDocentes === 0)
            && ($solapamientosSecciones === 0);

        if ($todasAprobadas) {
            $this->newLine();
            $this->info("✓ TODAS LAS VERIFICACIONES DE INTEGRIDAD HAN SIDO APROBADAS SATISFACTORIAMENTE.");
            return Command::SUCCESS;
        } else {
            $this->newLine();
            $this->error("✗ SE DETECTARON VIOLACIONES A LAS REGLAS DE INTEGRIDAD.");
            return Command::FAILURE;
        }
    }
}
