<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BloqueHorario;

class BloqueHorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bloques = [
            ['hora_inicio' => '07:00', 'hora_fin' => '07:45', 'status' => true],
            ['hora_inicio' => '07:45', 'hora_fin' => '08:30', 'status' => true],
            ['hora_inicio' => '08:30', 'hora_fin' => '09:15', 'status' => true],
            ['hora_inicio' => '09:15', 'hora_fin' => '10:00', 'status' => true],
            ['hora_inicio' => '10:00', 'hora_fin' => '10:45', 'status' => true],
            ['hora_inicio' => '10:45', 'hora_fin' => '11:30', 'status' => true],
            ['hora_inicio' => '11:30', 'hora_fin' => '12:15', 'status' => true],
            ['hora_inicio' => '12:15', 'hora_fin' => '13:00', 'status' => true],
        ];

        foreach ($bloques as $bloque) {
            BloqueHorario::firstOrCreate($bloque);
        }

        $this->command->info('Bloques horarios creados exitosamente.');
    }
}
