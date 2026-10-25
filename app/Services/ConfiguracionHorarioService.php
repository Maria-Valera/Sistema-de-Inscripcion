<?php

namespace App\Services;

use App\Models\ConfiguracionHorario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConfiguracionHorarioService
{
    /**
     * Obtiene la configuración de horario para el año escolar indicado,
     * cargando la relación de recesos ordenada.
     */
    public function actual(int $anioEscolarId): ?ConfiguracionHorario
    {
        return ConfiguracionHorario::with(['recesos' => function ($q) {
            $q->orderBy('despues_de_bloque');
        }])->where('anio_escolar_id', $anioEscolarId)->first();
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
     * indisponibilidad o bloques reservados para un año escolar.
     */
    public function maximoBloqueOcupado(int $anioEscolarId): int
    {
        $maxAsignaciones = DB::table('horario_asignaciones')
            ->where('anio_escolar_id', $anioEscolarId)
            ->max('bloque') ?? 0;

        $maxIndisponibilidad = DB::table('docente_indisponibilidad')
            ->where('anio_escolar_id', $anioEscolarId)
            ->max('bloque') ?? 0;

        $maxReservados = DB::table('bloque_reservados')
            ->where('anio_escolar_id', $anioEscolarId)
            ->max('bloque') ?? 0;

        return max((int) $maxAsignaciones, (int) $maxIndisponibilidad, (int) $maxReservados);
    }

    /**
     * Valida y guarda la configuración de horario y sus recesos en una transacción.
     *
     * @throws \InvalidArgumentException Si las reglas de negocio no se cumplen.
     */
    public function guardar(int $anioEscolarId, array $datos): ConfiguracionHorario
    {
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

        return DB::transaction(function () use ($anioEscolarId, $datos, $horaInicio, $numeroBloques, $duracionBloqueMin, $recesosData) {
            $config = ConfiguracionHorario::updateOrCreate(
                ['anio_escolar_id' => $anioEscolarId],
                [
                    'hora_inicio' => $horaInicio,
                    'numero_bloques' => $numeroBloques,
                    'duracion_bloque_min' => $duracionBloqueMin,
                    'minutos_entre_bloques' => max(0, (int) ($datos['minutos_entre_bloques'] ?? 0)),
                ]
            );

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

            return $config->load('recesos');
        });
    }
}
