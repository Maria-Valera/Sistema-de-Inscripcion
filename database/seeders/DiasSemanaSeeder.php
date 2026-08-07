<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dias_semana;

class DiasSemanaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dias = [
            ['nombre_dia' => 'Lunes', 'status' => true],
            ['nombre_dia' => 'Martes', 'status' => true],
            ['nombre_dia' => 'Miércoles', 'status' => true],
            ['nombre_dia' => 'Jueves', 'status' => true],
            ['nombre_dia' => 'Viernes', 'status' => true],
        ];

        foreach ($dias as $dia) {
            Dias_semana::firstOrCreate($dia);
        }

        $this->command->info('Días de la semana creados exitosamente.');
    }
}
