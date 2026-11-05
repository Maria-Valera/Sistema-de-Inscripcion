<?php

namespace Database\Seeders;

use App\Enums\EstadoCorte;
use App\Enums\EstadoLapso;
use App\Enums\ReglaRedondeo;
use App\Models\AnioEscolar;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Corte;
use App\Models\Lapso;
use Illuminate\Database\Seeder;

// Datos iniciales de los maestros de Maria: configuracion de evaluacion, lapsos y cortes
// del año escolar mas reciente. Se puede correr varias veces sin duplicar (updateOrCreate).
class MariaMaestrosSeeder extends Seeder
{
    public function run(): void
    {
        $anio = AnioEscolar::orderByDesc('inicio_anio_escolar')->first();

        if (! $anio) {
            $this->command?->warn('[MariaMaestrosSeeder] No hay años escolares. Se omite.');
            return;
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
        $calendarioId = $anio->calendarioAcademico?->id;

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

                        // M5. Cortes: el lapso se divide en partes iguales; cada corte vale 20 / num_cortes puntos
            $diasLapso = $lapso->fecha_inicio->diffInDays($lapso->fecha_fin) + 1;
            $diasPorCorte = intdiv($diasLapso, $config->num_cortes);
            $peso = round($config->nota_maxima / $config->num_cortes, 2);

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

        $this->command?->info("[MariaMaestrosSeeder] Año {$anio->id}: configuracion, {$config->num_lapsos} lapsos y {$config->num_cortes} cortes por lapso.");
    }
}
