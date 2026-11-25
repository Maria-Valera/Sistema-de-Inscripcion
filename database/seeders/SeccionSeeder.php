<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('seccions')->insert([
            /* Grado 1 */
            ['nombre' => 'A', 'cantidad_actual' => '0', 'grado_id' => '1', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'B', 'cantidad_actual' => '0', 'grado_id' => '1', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'C', 'cantidad_actual' => '0', 'grado_id' => '1', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'D', 'cantidad_actual' => '0', 'grado_id' => '1', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'E', 'cantidad_actual' => '0', 'grado_id' => '1', 'ejecucion_percentil_id' => null, 'status' => true, ],
            /* Grado 2 */
            ['nombre' => 'A', 'cantidad_actual' => '0', 'grado_id' => '2', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'B', 'cantidad_actual' => '0', 'grado_id' => '2', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'C', 'cantidad_actual' => '0', 'grado_id' => '2', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'D', 'cantidad_actual' => '0', 'grado_id' => '2', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'E', 'cantidad_actual' => '0', 'grado_id' => '2', 'ejecucion_percentil_id' => null, 'status' => true, ],
            /* Grado 3 */
            ['nombre' => 'A', 'cantidad_actual' => '0', 'grado_id' => '3', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'B', 'cantidad_actual' => '0', 'grado_id' => '3', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'C', 'cantidad_actual' => '0', 'grado_id' => '3', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'D', 'cantidad_actual' => '0', 'grado_id' => '3', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'E', 'cantidad_actual' => '0', 'grado_id' => '3', 'ejecucion_percentil_id' => null, 'status' => true, ],
            /* Grado 4 */
            ['nombre' => 'A', 'cantidad_actual' => '0', 'grado_id' => '4', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'B', 'cantidad_actual' => '0', 'grado_id' => '4', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'C', 'cantidad_actual' => '0', 'grado_id' => '4', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'D', 'cantidad_actual' => '0', 'grado_id' => '4', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'E', 'cantidad_actual' => '0', 'grado_id' => '4', 'ejecucion_percentil_id' => null, 'status' => true, ],
            /* Grado 5 */
            ['nombre' => 'A', 'cantidad_actual' => '0', 'grado_id' => '5', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'B', 'cantidad_actual' => '0', 'grado_id' => '5', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'C', 'cantidad_actual' => '0', 'grado_id' => '5', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'D', 'cantidad_actual' => '0', 'grado_id' => '5', 'ejecucion_percentil_id' => null, 'status' => true, ],
            ['nombre' => 'E', 'cantidad_actual' => '0', 'grado_id' => '5', 'ejecucion_percentil_id' => null, 'status' => true, ],
        ]);
        $this->command->info(string: 'Secciones insertadas correctamente.');
    }
}
