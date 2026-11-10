<?php

namespace App\Services;

use App\Enums\Dia;
use App\Models\BloqueReservado;
use App\Models\ConfiguracionHorario;
use App\Support\AnioEscolarSeleccionado;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConfiguracionHorarioService
{
    /**
     * Obtiene la configuración de horario global única,
     * cargando la relación de recesos ordenada.
     * Si no existe, crea una configuración por defecto (07:00 AM, 4 bloques de 90 min).
     */
    public function actual(?int $anioEscolarId = null): ConfiguracionHorario
    {
        $config = ConfiguracionHorario::with(['recesos' => function ($q) {
            $q->orderBy('despues_de_bloque');
        }])->first();

        if (!$config) {
            $config = ConfiguracionHorario::create([
                'hora_inicio' => '07:00:00',
                'numero_bloques' => 4,
                'duracion_bloque_min' => 90,
                'minutos_entre_bloques' => 0,
            ]);
            $config->setRelation('recesos', collect());
        }

        return $config;
    }

    /**
     * Calcula y retorna un mapa de bloques horarios para la configuración dada.
     * Retorna una colección con formato: [n => ['inicio' => 'H:i', 'fin' => 'H:i']].
     */
    public function bloques(ConfiguracionHorario $config): Collection
    {
        $recesosMap = $config->relationLoaded('recesos')
            ? $config->recesos->pluck('duracion_min', 'despues_de_bloque')->all()
            : $config->recesos()->pluck('duracion_min', 'despues_de_bloque')->all();

        $horaInicioStr = is_string($config->hora_inicio)
            ? $config->hora_inicio
            : (is_object($config->hora_inicio) ? $config->hora_inicio->format('H:i:s') : '07:00:00');

        if (strlen($horaInicioStr) === 5) {
            $horaInicioStr .= ':00';
        }

        $horaActual = Carbon::createFromFormat('H:i:s', $horaInicioStr);
        $resultado = collect();

        for ($n = 1; $n <= $config->numero_bloques; $n++) {
            $inicio = $horaActual->copy();
            $fin = $inicio->copy()->addMinutes($config->duracion_bloque_min);

            $resultado->put($n, [
                'inicio' => $inicio->format('H:i'),
                'fin' => $fin->format('H:i'),
            ]);

            $recesoMin = (int) ($recesosMap[$n] ?? 0);
            $horaActual = $fin->copy()
                ->addMinutes($config->minutos_entre_bloques)
                ->addMinutes($recesoMin);
        }

        return $resultado;
    }

    /**
     * Obtiene el número de bloque más alto registrado en asignaciones,
     * indisponibilidad o bloques reservados (para un año específico o globalmente).
     */
    public function maximoBloqueOcupado(?int $anioEscolarId = null): int
    {
        $qAsignaciones = DB::table('horario_asignaciones');
        $qIndisponibilidad = DB::table('docente_indisponibilidad');
        $qReservados = DB::table('bloque_reservados');

        if ($anioEscolarId !== null) {
            $qAsignaciones->where('anio_escolar_id', $anioEscolarId);
            $qIndisponibilidad->where('anio_escolar_id', $anioEscolarId);
            $qReservados->where('anio_escolar_id', $anioEscolarId);
        }

        $maxAsignaciones = $qAsignaciones->max('bloque') ?? 0;
        $maxIndisponibilidad = $qIndisponibilidad->max('bloque') ?? 0;
        $maxReservados = $qReservados->max('bloque') ?? 0;

        return max((int) $maxAsignaciones, (int) $maxIndisponibilidad, (int) $maxReservados);
    }

    /**
     * Valida y guarda la configuración de horario y sus recesos en una transacción.
     * Soporta ser invocado como guardar(array $datos, ?int $anioEscolarId = null)
     * o mantener compatibilidad guardar(int $anioEscolarId, array $datos).
     *
     * @throws \InvalidArgumentException Si las reglas de negocio no se cumplen.
     */
    public function guardar($arg1, $arg2 = null): ConfiguracionHorario
    {
        if (is_array($arg1)) {
            $datos = $arg1;
            $anioEscolarId = is_numeric($arg2) ? (int) $arg2 : AnioEscolarSeleccionado::id();
        } else {
            $anioEscolarId = is_numeric($arg1) ? (int) $arg1 : AnioEscolarSeleccionado::id();
            $datos = is_array($arg2) ? $arg2 : [];
        }

        $numeroBloques = (int) ($datos['numero_bloques'] ?? 0);
        if ($numeroBloques <= 0) {
            throw new \InvalidArgumentException('El número de bloques debe ser mayor a 0.');
        }

        $duracionBloqueMin = (int) ($datos['duracion_bloque_min'] ?? 0);
        if ($duracionBloqueMin <= 0) {
            throw new \InvalidArgumentException('La duración del bloque debe ser mayor a 0 minutos.');
        }

        $horaInicio = trim((string) ($datos['hora_inicio'] ?? ''));
        if ($horaInicio === '') {
            throw new \InvalidArgumentException('La hora de inicio es obligatoria.');
        }
        if (strlen($horaInicio) === 5) {
            $horaInicio .= ':00';
        }

        // Regla: no permitir reducir numero_bloques por debajo del mayor bloque existente
        $maxBloqueOcupado = $this->maximoBloqueOcupado($anioEscolarId);
        if ($numeroBloques < $maxBloqueOcupado) {
            throw new \InvalidArgumentException(
                "No se puede reducir el número de bloques a {$numeroBloques} porque ya existen registros asignados hasta el bloque {$maxBloqueOcupado}."
            );
        }

        // Validación de recesos
        $recesosData = $datos['recesos'] ?? [];
        $bloquesConReceso = [];

        foreach ($recesosData as $receso) {
            if (!isset($receso['despues_de_bloque']) || !isset($receso['duracion_min'])) {
                continue;
            }
            $despuesDe = (int) $receso['despues_de_bloque'];
            $duracion = (int) $receso['duracion_min'];

            if ($duracion <= 0) {
                throw new \InvalidArgumentException("La duración del receso tras el bloque {$despuesDe} debe ser mayor a 0 minutos.");
            }

            if ($despuesDe < 1 || $despuesDe >= $numeroBloques) {
                throw new \InvalidArgumentException(
                    "El receso tras el bloque {$despuesDe} no es válido. Debe ubicarse entre el bloque 1 y el bloque " . ($numeroBloques - 1) . "."
                );
            }

            if (in_array($despuesDe, $bloquesConReceso, true)) {
                throw new \InvalidArgumentException("No puede haber más de un receso después del bloque {$despuesDe}.");
            }
            $bloquesConReceso[] = $despuesDe;
        }

        // Validación de bloques reservados (soporta bloque único o rangos desde..hasta)
        $reservadosRaw = $datos['reservados'] ?? [];
        $paresReservados = [];
        foreach ($reservadosRaw as $item) {
            $dia = null;
            $desde = null;
            $hasta = null;

            if (is_string($item) && str_contains($item, '_')) {
                [$diaStr, $bloqueStr] = explode('_', $item, 2);
                $dia = (int) $diaStr;
                $desde = (int) $bloqueStr;
                $hasta = $desde;
            } elseif (is_array($item)) {
                if (isset($item['dia'])) {
                    $dia = (int) $item['dia'];
                }
                if (isset($item['desde'])) {
                    $desde = (int) $item['desde'];
                    $hasta = isset($item['hasta']) ? (int) $item['hasta'] : $desde;
                } elseif (isset($item['bloque'])) {
                    $desde = (int) $item['bloque'];
                    $hasta = $desde;
                }
            }

            if ($dia === null || $desde === null || $hasta === null) {
                continue;
            }

            if ($dia < 1 || $dia > 5) {
                throw new \InvalidArgumentException("El día {$dia} es inválido. Debe ser de lunes (1) a viernes (5).");
            }

            if ($desde > $hasta) {
                throw new \InvalidArgumentException("El bloque inicial ({$desde}) no puede ser mayor al bloque final ({$hasta}).");
            }

            if ($desde < 1 || $hasta > $numeroBloques) {
                $bloqueErroneo = $hasta > $numeroBloques ? $hasta : $desde;
                throw new \InvalidArgumentException("No se puede reservar el bloque {$bloqueErroneo}. El máximo de bloques configurado para la jornada es {$numeroBloques}.");
            }

            for ($b = $desde; $b <= $hasta; $b++) {
                $paresReservados["{$dia}_{$b}"] = ['dia' => $dia, 'bloque' => $b];
            }
        }

        // Validación: verificar si algún bloque a reservar ya tiene clases asignadas en horario_asignaciones
        if (!empty($paresReservados) && $anioEscolarId) {
            $conflictos = [];
            foreach ($paresReservados as $par) {
                $asignaciones = DB::table('horario_asignaciones')
                    ->where('anio_escolar_id', $anioEscolarId)
                    ->where('dia', $par['dia'])
                    ->where('bloque', $par['bloque'])
                    ->count();

                if ($asignaciones > 0) {
                    $nombreDia = Dia::tryFrom($par['dia'])?->label() ?? "Día {$par['dia']}";
                    $conflictos[] = "{$nombreDia} Bloque {$par['bloque']} ({$asignaciones} clase(s))";
                }
            }

            if (!empty($conflictos)) {
                $mensajeConflictos = implode(', ', $conflictos);
                throw new \InvalidArgumentException("No se pueden guardar los bloques reservados porque existen clases asignadas en: {$mensajeConflictos}. Desasigna las clases antes de reservar estos bloques.");
            }
        }

        return DB::transaction(function () use ($anioEscolarId, $datos, $horaInicio, $numeroBloques, $duracionBloqueMin, $recesosData, $paresReservados) {
            $config = ConfiguracionHorario::first();
            $payload = [
                'hora_inicio' => $horaInicio,
                'numero_bloques' => $numeroBloques,
                'duracion_bloque_min' => $duracionBloqueMin,
                'minutos_entre_bloques' => max(0, (int) ($datos['minutos_entre_bloques'] ?? 0)),
            ];

            if ($config) {
                $config->update($payload);
            } else {
                $config = ConfiguracionHorario::create($payload);
            }

            // Reemplazar recesos
            $config->recesos()->delete();

            foreach ($recesosData as $receso) {
                if (!isset($receso['despues_de_bloque']) || !isset($receso['duracion_min'])) {
                    continue;
                }
                $despuesDe = (int) $receso['despues_de_bloque'];
                $duracion = (int) $receso['duracion_min'];

                if ($despuesDe >= 1 && $despuesDe < $numeroBloques && $duracion > 0) {
                    $config->recesos()->create([
                        'despues_de_bloque' => $despuesDe,
                        'duracion_min' => $duracion,
                    ]);
                }
            }

            // Sincronizar bloques reservados si se envió la clave en $datos y hay un año escolar disponible
            if (array_key_exists('reservados', $datos) && $anioEscolarId) {
                BloqueReservado::where('anio_escolar_id', $anioEscolarId)->delete();

                foreach ($paresReservados as $par) {
                    BloqueReservado::create([
                        'anio_escolar_id' => $anioEscolarId,
                        'dia' => $par['dia'],
                        'bloque' => $par['bloque'],
                    ]);
                }
            }

            return $config->load('recesos');
        });
    }

    /**
     * Retorna el mapa de bloques reservados para un año escolar [ "dia_bloque" => true ].
     */
    public function reservadosMapa(int $anioEscolarId): array
    {
        return BloqueReservado::mapaPorAnio($anioEscolarId);
    }

    /**
     * Guarda los bloques reservados (grupos estables) para un año escolar.
     *
     * @param int $anioEscolarId
     * @param array $reservadosRaw Lista de cadenas "dia_bloque" o arrays ['dia' => d, 'bloque' => b]
     * @throws \InvalidArgumentException Si un bloque supera el número de bloques configurado o tiene asignaciones
     */
    public function guardarReservados(int $anioEscolarId, array $reservadosRaw): void
    {
        $config = $this->actual();
        $datos = [
            'hora_inicio' => $config->hora_inicio,
            'numero_bloques' => $config->numero_bloques,
            'duracion_bloque_min' => $config->duracion_bloque_min,
            'minutos_entre_bloques' => $config->minutos_entre_bloques,
            'recesos' => $config->recesos->map(fn($r) => [
                'despues_de_bloque' => (int) $r->despues_de_bloque,
                'duracion_min' => (int) $r->duracion_min,
            ])->all(),
            'reservados' => $reservadosRaw,
        ];

        $this->guardar($datos, $anioEscolarId);
    }
}
