<?php

namespace Database\Seeders\Notas;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoEvaluacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            [
                'codigo' => 'parcial',
                'nombre' => 'Evaluación parcial',
                'activo' => true,
            ],
            [
                'codigo' => 'final_lapso',
                'nombre' => 'Evaluación final de lapso',
                'activo' => true,
            ],
            [
                'codigo' => 'extraordinaria',
                'nombre' => 'Evaluación extraordinaria',
                'activo' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            DB::table('tipo_evaluacions')->updateOrInsert(
                ['codigo' => $tipo['codigo']],
                array_merge($tipo, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command?->info('Tipos de evaluación sembrados correctamente (parcial, final_lapso, extraordinaria).');
    }
}
