<?php

namespace App\Support;

use App\Enums\EstadoCalendario;
use App\Enums\EstadoCorte;
use App\Enums\EstadoLapso;
use App\Enums\ReglaRedondeo;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Corte;
use App\Models\Lapso;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Asistente de año escolar nuevo (María): año escolar → lapso 1 → calendario académico.
 *
 * Mientras se avanza, NADA se guarda en la base de datos: cada paso queda en la sesión (un borrador),
 * y con "Atrás" se vuelve al paso anterior con lo ya escrito. Recién al darle Guardar en el último
 * paso se crea todo junto, en una sola transacción. Los demás lapsos se crean después en el CRUD de Lapsos.
 */
class AsistenteAnioEscolar
{
    /** Clave de la sesión donde vive el borrador. */
    private const SESION = 'asistente_anio';

    /** Para proponer las fechas del lapso 1, el año se piensa dividido en 3 lapsos. */
    private const LAPSOS_SUGERIDOS = 3;

    // ----- El borrador (sesión) -----

    /** Lo que se lleva escrito: ['anio' => [...], 'lapso' => [...]] (cada parte puede faltar). */
    public static function borrador(): array
    {
        return session(self::SESION, []);
    }

    /** Guarda el paso 1. Si las fechas del año cambiaron y el lapso ya no cabe, se descarta para volver a proponerlo. */
    public static function guardarAnio(array $anio): void
    {
        $borrador = self::borrador();
        $borrador['anio'] = $anio;

        $lapso = $borrador['lapso'] ?? null;
        if ($lapso && ($lapso['fecha_inicio'] < $anio['inicio_anio_escolar'] || $lapso['fecha_fin'] > $anio['cierre_anio_escolar'])) {
            unset($borrador['lapso']);
        }

        session([self::SESION => $borrador]);
    }

    /** Guarda el paso 2. */
    public static function guardarLapso(array $lapso): void
    {
        $borrador = self::borrador();
        $borrador['lapso'] = $lapso;

        session([self::SESION => $borrador]);
    }

    /** Borra el borrador (al terminar). */
    public static function olvidar(): void
    {
        session()->forget(self::SESION);
    }

    // ----- Propuestas para los formularios -----

    /** Fechas propuestas para el lapso 1: el primer tercio del año, con 7 días para cargar notas. */
    public static function propuestaLapso(string $inicio, string $cierre): array
    {
        [$desde, $hasta] = self::dividir(Carbon::parse($inicio), Carbon::parse($cierre), self::LAPSOS_SUGERIDOS)[0];

        return [
            'nombre' => 'Lapso 1',
            'fecha_inicio' => $desde->toDateString(),
            'fecha_fin' => $hasta->toDateString(),
            'fecha_limite_carga' => $hasta->copy()->addDays(7)->toDateString(),
        ];
    }

    /** La configuración que se va a copiar: la del año más reciente que tenga una (o null: valores por defecto). */
    public static function configuracionAnterior(): ?ConfiguracionEvaluacion
    {
        return ConfiguracionEvaluacion::query()
            ->join('anio_escolars', 'anio_escolars.id', '=', 'configuracion_evaluacions.anio_escolar_id')
            ->orderByDesc('anio_escolars.inicio_anio_escolar')
            ->select('configuracion_evaluacions.*')
            ->with('anioEscolar')
            ->first();
    }

    // ----- Guardar todo (último paso) -----

    /**
     * Crea, en una sola transacción: el año (activo; los demás quedan inactivos), su configuración
     * de evaluación (copiada del año anterior), el lapso 1 con sus cortes y el calendario académico.
     * Si algo falla, no queda nada a medias.
     */
    public static function crearTodo(): CalendarioAcademico
    {
        $borrador = self::borrador();

        return DB::transaction(function () use ($borrador) {
            // 1. El año escolar: el nuevo queda activo y los demás inactivos (igual que en Año Escolar).
            AnioEscolar::query()->update(['status' => 'Inactivo']);
            $anio = AnioEscolar::create($borrador['anio'] + ['status' => 'Activo']);

            // 2. Su configuración de evaluación.
            self::crearConfiguracion($anio);

            // 3. El calendario académico.
            $calendario = CalendarioAcademico::create([
                'anio_escolar_id' => $anio->id,
                'estado' => EstadoCalendario::Confirmado,
                'fecha_confirmacion' => now(),
            ]);

            // 4. El lapso 1, enlazado al calendario, con sus 4 cortes de 20 puntos.
            $datos = $borrador['lapso'];
            $lapso = Lapso::create([
                'anio_escolar_id' => $anio->id,
                'calendario_academico_id' => $calendario->id,
                'numero' => 1,
                'nombre' => $datos['nombre'],
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'fecha_limite_carga' => Carbon::parse($datos['fecha_limite_carga'])->endOfDay(),
                'estado' => EstadoLapso::Planificado,
            ]);
            self::crearCortes($lapso);

            return $calendario;
        });
    }

    /** Copia la configuración del año anterior; si no hay ninguna, usa los valores por defecto. */
    private static function crearConfiguracion(AnioEscolar $anio): ConfiguracionEvaluacion
    {
        $anterior = ConfiguracionEvaluacion::query()
            ->join('anio_escolars', 'anio_escolars.id', '=', 'configuracion_evaluacions.anio_escolar_id')
            ->where('configuracion_evaluacions.anio_escolar_id', '!=', $anio->id)
            ->orderByDesc('anio_escolars.inicio_anio_escolar')
            ->select('configuracion_evaluacions.*')
            ->first();

        $valores = $anterior
            ? $anterior->only(['nota_minima', 'nota_maxima', 'nota_aprobatoria', 'regla_redondeo', 'max_lapsos', 'momentos_quedada', 'max_areas_pendientes'])
            : [
                'nota_minima' => 1, 'nota_maxima' => 20, 'nota_aprobatoria' => 10,
                'regla_redondeo' => ReglaRedondeo::HalfUp,
                'max_lapsos' => 3,
                'momentos_quedada' => 4, 'max_areas_pendientes' => 2,
            ];

        return ConfiguracionEvaluacion::create(['anio_escolar_id' => $anio->id] + $valores);
    }

    /** Crea los cortes de un lapso: 4 cortes con fechas en partes iguales, de 20 puntos cada uno. */
    public static function crearCortes(Lapso $lapso): void
    {
        $partes = self::dividir($lapso->fecha_inicio, $lapso->fecha_fin, Corte::CANTIDAD_POR_DEFECTO);

        foreach ($partes as $i => [$inicio, $fin]) {
            $lapso->cortes()->create([
                'numero' => $i + 1,
                'nombre' => 'Corte ' . ($i + 1),
                'peso_lapso' => Corte::PUNTOS_POR_DEFECTO,
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
