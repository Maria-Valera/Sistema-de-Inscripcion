<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aula;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = [
            ['nombre_aula' => 'Aula 101', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 102', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 103', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 201', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 202', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 203', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 301', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Aula 302', 'tipo_aula' => 'Aula Regular'],
            ['nombre_aula' => 'Laboratorio de Computación', 'tipo_aula' => 'Laboratorio'],
            ['nombre_aula' => 'Sala de Música', 'tipo_aula' => 'Sala Especializada'],
            ['nombre_aula' => 'Biblioteca', 'tipo_aula' => 'Biblioteca'],
            ['nombre_aula' => 'Aula Magna', 'tipo_aula' => 'Aula Magna'],
        ];

        foreach ($aulas as $aula) {
            Aula::firstOrCreate(
                ['nombre_aula' => $aula['nombre_aula']],
                [
                    'tipo_aula' => $aula['tipo_aula'],
                    'status'    => true
                ]
            );
        }

        $this->command->info('Aulas creadas correctamente.');
    }
}
