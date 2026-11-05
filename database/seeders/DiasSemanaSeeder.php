<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiasSemanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('dias_semana')) {
            $this->command?->info('Tabla dias_semana no existe (obsoleta tras refactor de horarios). Omitiendo seeder.');
            return;
        }

        $dias = [
            ['nombre_dia' => 'Lunes', 'status' => true],
            ['nombre_dia' => 'Martes', 'status' => true],
            ['nombre_dia' => 'Miércoles', 'status' => true],
            ['nombre_dia' => 'Jueves', 'status' => true],
            ['nombre_dia' => 'Viernes', 'status' => true],
        ];

        foreach ($dias as $dia) {
            DB::table('dias_semana')->insertOrIgnore($dia);
        }

        $this->command?->info('Días de la semana creados exitosamente.');
    }
}
