<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\AnioEscolar;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener o crear un aula
        $aula = Aula::firstOrCreate(
            ['nombre_aula' => 'Aula 101'],
            ['status' => true]
        );

        // Obtener o crear un año escolar activo
        $anioEscolar = AnioEscolar::firstOrCreate(
            [
                'inicio_anio_escolar' => '2026-01-01',
                'cierre_anio_escolar' => '2026-12-31',
            ],
            [
                'status' => 'Activo',
            ]
        );

        // Crear horarios de prueba
        Horario::create([
            'aula_id' => $aula->id_aula,
            'anio_escolar_id' => $anioEscolar->id,
            'status' => true,
        ]);

        Horario::create([
            'aula_id' => $aula->id_aula,
            'anio_escolar_id' => $anioEscolar->id,
            'status' => true,
        ]);

        Horario::create([
            'aula_id' => $aula->id_aula,
            'anio_escolar_id' => $anioEscolar->id,
            'status' => false,
        ]);

        $this->command->info('Horarios creados exitosamente.');
    }
}
