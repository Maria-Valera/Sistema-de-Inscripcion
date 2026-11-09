<?php

namespace Database\Seeders;

use App\Enums\CategoriaDia;
use App\Enums\EstadoCalendario;
use App\Enums\EstadoCorte;
use App\Enums\EstadoLapso;
use App\Enums\ReglaRedondeo;
use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\CalendarioDia;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Corte;
use App\Models\Lapso;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

// Datos iniciales de los maestros de Maria: calendario con feriados, configuracion de evaluacion, lapsos y cortes
class MariaMaestrosSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::orderByDesc('inicio_anio_escolar')->first();

        if (! $anio) {
            $this->command?->warn('[MariaMaestrosSeeder] No hay años escolares. Se omite.');
            return;
        }

        // M1 y M2. Calendario del año con los feriados nacionales que caen dentro del año escolar
        $calendario = CalendarioAcademico::updateOrCreate(
            ['anio_escolar_id' => $anio->id],
            ['estado' => EstadoCalendario::Confirmado, 'fecha_confirmacion' => now()]
        );

        $feriados = 0;
        foreach ($this->feriadosNacionales($anio->inicio_anio_escolar, $anio->cierre_anio_escolar) as $fecha => $nombre) {
            CalendarioDia::updateOrCreate(
                ['calendario_id' => $calendario->id, 'fecha' => $fecha, 'texto_extraido' => $nombre],
                [
                    'categoria' => CategoriaDia::NoLaborable,
                    'es_efemeride' => false,
                    'aplica_personal' => true,
                    'aplica_estudiantes' => true,
                    'color' => CalendarioDia::determinarColor(true, true, true, false, null),
                ]
            );
            $feriados++;
        }

                // M3. Configuracion de evaluacion del año
        $config = ConfiguracionEvaluacion::updateOrCreate(
            ['anio_escolar_id' => $anio->id],
            [
                'nota_minima' => 1,
                'nota_maxima' => 20,
                'nota_aprobatoria' => 10,
                'regla_redondeo' => ReglaRedondeo::HalfUp,
                'num_cortes' => 4,
                'puntos_corte' => 20,
                'num_lapsos' => 3,
                'momentos_quedada' => 4,
                'max_areas_pendientes' => 2,
            ]
        );

                // M4. Lapsos: el año se divide en partes iguales segun num_lapsos
        $inicio = $anio->inicio_anio_escolar->copy();
        $fin = $anio->cierre_anio_escolar->copy();
        $diasPorLapso = intdiv($inicio->diffInDays($fin) + 1, $config->num_lapsos);
        $calendarioId = $calendario->id;

        for ($n = 1; $n <= $config->num_lapsos; $n++) {
            $inicioLapso = $inicio->copy()->addDays(($n - 1) * $diasPorLapso);
            $finLapso = $n === $config->num_lapsos ? $fin->copy() : $inicioLapso->copy()->addDays($diasPorLapso - 1);

            $lapso = Lapso::updateOrCreate(
                ['anio_escolar_id' => $anio->id, 'numero' => $n],
                [
                    'calendario_academico_id' => $calendarioId,
                    'nombre' => "Lapso {$n}",
                    'fecha_inicio' => $inicioLapso,
                    'fecha_fin' => $finLapso,
                    'fecha_limite_carga' => $finLapso->copy()->endOfDay(),
                    'estado' => EstadoLapso::Planificado,
                ]
            );

            // M5. Cortes: el lapso se divide en partes iguales; cada corte vale los puntos por corte (20), en total 4 × 20 = 80
            $diasLapso = $lapso->fecha_inicio->diffInDays($lapso->fecha_fin) + 1;
            $diasPorCorte = intdiv($diasLapso, $config->num_cortes);
            $peso = $config->puntos_corte;

            for ($c = 1; $c <= $config->num_cortes; $c++) {
                $inicioCorte = $lapso->fecha_inicio->copy()->addDays(($c - 1) * $diasPorCorte);
                $finCorte = $c === $config->num_cortes ? $lapso->fecha_fin->copy() : $inicioCorte->copy()->addDays($diasPorCorte - 1);

                Corte::updateOrCreate(
                    ['lapso_id' => $lapso->id, 'numero' => $c],
                    [
                        'nombre' => "Corte {$c}",
                        'peso_lapso' => $peso,
                        'fecha_inicio' => $inicioCorte,
                        'fecha_fin' => $finCorte,
                        'fecha_limite_carga' => $finCorte->copy()->endOfDay(),
                        'estado' => EstadoCorte::Planificado,
                    ]
                );
            }
        }

                $this->command?->info("[MariaMaestrosSeeder] Año {$anio->id}: calendario con {$feriados} feriados, configuracion, {$config->num_lapsos} lapsos y {$config->num_cortes} cortes por lapso.");
    }


    /**
     * Feriados nacionales de Venezuela que caen entre $inicio y $fin, como [fecha 'Y-m-d' => nombre].
     * Los fijos se repiten cada año; Carnaval y Semana Santa se calculan desde el Domingo de Pascua.
     */
    private function feriadosNacionales(Carbon $inicio, Carbon $fin): array
    {
        $feriados = [];

        foreach (range($inicio->year, $fin->year) as $anio) {
            $fijos = [
                "{$anio}-01-01" => 'Año Nuevo',
                "{$anio}-04-19" => 'Declaración de la Independencia',
                "{$anio}-05-01" => 'Día del Trabajador',
                "{$anio}-06-24" => 'Batalla de Carabobo',
                "{$anio}-07-05" => 'Día de la Independencia',
                "{$anio}-07-24" => 'Natalicio del Libertador',
                "{$anio}-10-12" => 'Día de la Resistencia Indígena',
                "{$anio}-12-24" => 'Nochebuena',
                "{$anio}-12-25" => 'Navidad',
                "{$anio}-12-31" => 'Fin de Año',
            ];

            $pascua = $this->domingoDePascua($anio);
            $moviles = [
                $pascua->copy()->subDays(48)->toDateString() => 'Lunes de Carnaval',
                $pascua->copy()->subDays(47)->toDateString() => 'Martes de Carnaval',
                $pascua->copy()->subDays(3)->toDateString() => 'Jueves Santo',
                $pascua->copy()->subDays(2)->toDateString() => 'Viernes Santo',
            ];

            foreach ($fijos + $moviles as $fecha => $nombre) {
                if ($fecha >= $inicio->toDateString() && $fecha <= $fin->toDateString()) {
                    $feriados[$fecha] = $nombre;
                }
            }
        }

        ksort($feriados);

        return $feriados;
    }

    /**
     * Domingo de Pascua (algoritmo de Meeus/Jones/Butcher). No depende de la extension "calendar" de PHP.
     */
    private function domingoDePascua(int $anio): Carbon
    {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($anio, $mes, $dia);
    }

}
