<?php

namespace App\Support;

use App\Enums\EstadoCorte;
use App\Enums\ReglaRedondeo;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Lapso;
use Carbon\Carbon;

/**
 * Asistente de año escolar nuevo (María): después de crear el año (pantalla de Stephania),
 * el usuario crea sus lapsos y luego su calendario académico, y recién ahí entra al sistema.
 */
class AsistenteAnioEscolar
{
    /** Año que el asistente prepara: el año activo. */
    public static function anioActivo(): ?AnioEscolar
    {
        return AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first();
    }

    /** Marca el año recién creado desde "seleccionar año": a partir de aquí lo guía el asistente. */
    public static function iniciar(AnioEscolar $anio): void
    {
        AnioEscolar::whereKey($anio->id)->update(['asistente_pendiente' => true]);
    }

    /** El año ya tiene lapsos y calendario: el asistente terminó. */
    public static function terminar(AnioEscolar $anio): void
    {
        AnioEscolar::whereKey($anio->id)->update(['asistente_pendiente' => false]);
    }

    /**
     * Ruta del paso que le falta al año (lapsos o calendario), o null si no está en el asistente o ya lo terminó.
     * Solo cuenta para el año creado desde "seleccionar año"; los demás años no pasan por aquí.
     */
    public static function pasoPendiente(AnioEscolar $anio): ?string
    {
        if (! AnioEscolar::whereKey($anio->id)->value('asistente_pendiente')) {
            return null;
        }

        if (! Lapso::where('anio_escolar_id', $anio->id)->exists()) {
            return 'admin.asistente.lapsos';
        }

        if (! CalendarioAcademico::where('anio_escolar_id', $anio->id)->exists()) {
            return 'admin.asistente.calendario';
        }

        return null;
    }

    /**
     * Configuración de evaluación del año: si no tiene, copia la del último año que tenga una;
     * si no hay ninguna, usa los valores por defecto de M3.
     */
    public static function asegurarConfiguracion(AnioEscolar $anio): ConfiguracionEvaluacion
    {
        $existente = ConfiguracionEvaluacion::where('anio_escolar_id', $anio->id)->first();
        if ($existente) {
            return $existente;
        }

        $anterior = self::configuracionAnterior($anio);

        $valores = $anterior
            ? $anterior->only(['nota_minima', 'nota_maxima', 'nota_aprobatoria', 'regla_redondeo', 'num_lapsos', 'num_cortes', 'puntos_corte', 'momentos_quedada', 'max_areas_pendientes'])
            : [
                'nota_minima' => 1, 'nota_maxima' => 20, 'nota_aprobatoria' => 10,
                'regla_redondeo' => ReglaRedondeo::from('half_up'),
                'num_lapsos' => 3, 'num_cortes' => 4, 'puntos_corte' => 20,
                'momentos_quedada' => 4, 'max_areas_pendientes' => 2,
            ];

        return ConfiguracionEvaluacion::create(['anio_escolar_id' => $anio->id] + $valores);
    }

    /** Año del que se copió la configuración (para avisarlo en pantalla), o null si fueron los valores por defecto. */
    public static function anioDeOrigen(AnioEscolar $anio): ?AnioEscolar
    {
        return self::configuracionAnterior($anio)?->anioEscolar;
    }

    /** La configuración del año más reciente que no sea este. */
    private static function configuracionAnterior(AnioEscolar $anio): ?ConfiguracionEvaluacion
    {
        return ConfiguracionEvaluacion::query()
            ->join('anio_escolars', 'anio_escolars.id', '=', 'configuracion_evaluacions.anio_escolar_id')
            ->where('configuracion_evaluacions.anio_escolar_id', '!=', $anio->id)
            ->orderByDesc('anio_escolars.inicio_anio_escolar')
            ->select('configuracion_evaluacions.*')
            ->first();
    }

    /** Fechas propuestas: el año dividido en partes iguales, con 7 días para cargar notas al final de cada lapso. */
    public static function propuestaLapsos(AnioEscolar $anio, int $cantidad): array
    {
        $partes = self::dividir($anio->inicio_anio_escolar, $anio->cierre_anio_escolar, $cantidad);

        return collect($partes)->map(fn ($p, $i) => [
            'nombre' => 'Lapso ' . ($i + 1),
            'fecha_inicio' => $p[0]->toDateString(),
            'fecha_fin' => $p[1]->toDateString(),
            'fecha_limite_carga' => $p[1]->copy()->addDays(7)->toDateString(),
        ])->all();
    }

    /** Crea los cortes de un lapso: fechas en partes iguales y los puntos repartidos hasta sumar la nota máxima. */
    public static function crearCortes(Lapso $lapso, ConfiguracionEvaluacion $config): void
    {
        $cantidad = $config->num_cortes;
        $partes = self::dividir($lapso->fecha_inicio, $lapso->fecha_fin, $cantidad);
        $peso = floor($config->nota_maxima / $cantidad * 100) / 100;

        foreach ($partes as $i => [$inicio, $fin]) {
            $esUltimo = $i === $cantidad - 1;
            $lapso->cortes()->create([
                'numero' => $i + 1,
                'nombre' => 'Corte ' . ($i + 1),
                // El último se queda con lo que falte, para que la suma dé exacto la nota máxima.
                'peso_lapso' => $esUltimo ? round($config->nota_maxima - $peso * ($cantidad - 1), 2) : $peso,
                'fecha_inicio' => $inicio->toDateString(),
                'fecha_fin' => $fin->toDateString(),
                'fecha_limite_carga' => $fin->copy()->endOfDay(),
                'estado' => EstadoCorte::Planificado,
            ]);
        }
    }

    /** Divide [inicio, fin] en $partes tramos seguidos, sin huecos ni días repetidos. */
    private static function dividir(Carbon $inicio, Carbon $fin, int $partes): array
    {
        $dias = (int) $inicio->copy()->startOfDay()->diffInDays($fin->copy()->startOfDay()) + 1;
        $tramo = max(1, intdiv($dias, $partes));
        $resultado = [];

        for ($i = 0; $i < $partes; $i++) {
            $desde = $inicio->copy()->addDays($i * $tramo);
            $hasta = $i === $partes - 1 ? $fin->copy() : $desde->copy()->addDays($tramo - 1);
            $resultado[] = [$desde, $hasta];
        }

        return $resultado;
    }
}
