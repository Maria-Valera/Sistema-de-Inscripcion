<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Aula;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        $aulas = [
            'Aula 101',
            'Aula 102',
            'Aula 103',
            'Aula 201',
            'Aula 202',
            'Aula 203',
            'Aula 301',
            'Aula 302',
            'Laboratorio de Computación',
            'Sala de Música',
            'Biblioteca',
            'Aula Magna',
        ];

        foreach ($aulas as $nombre) {
            Aula::firstOrCreate(
                ['nombre_aula' => $nombre],
                ['status'      => true]
            );
        }

        $this->command->info('Aulas creadas correctamente.');
    }
}
