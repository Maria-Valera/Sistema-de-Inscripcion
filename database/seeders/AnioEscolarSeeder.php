<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnioEscolarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anios = [
            ['inicio_anio_escolar' => '2023-09-01', 'cierre_anio_escolar' => '2024-06-01', 'status' => 'Inactivo'],
            ['inicio_anio_escolar' => '2024-09-01', 'cierre_anio_escolar' => '2025-06-01', 'status' => 'Inactivo'],
            ['inicio_anio_escolar' => '2025-09-01', 'cierre_anio_escolar' => '2026-06-01', 'status' => 'Activo'],
        ];

        foreach ($anios as $anio) {
            DB::table('anio_escolars')->updateOrInsert(
                ['inicio_anio_escolar' => $anio['inicio_anio_escolar']],
                $anio
            );
        }

        $this->command->info('Anio escolar insertados correctamente.');
    }
}
