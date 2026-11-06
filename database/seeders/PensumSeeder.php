<?php

namespace Database\Seeders;

use App\Models\Pensum;
use Illuminate\Database\Seeder;

class PensumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $planes = [
            [
                'codigo'      => '31059',
                'nombre'      => 'Educación Media General',
                'descripcion' => 'Plan de estudio oficial de Educación Media General. No derogado según lo establecido en la Circular N° 01 (enero 2024).',
                'anio_inicio' => 2017,
                'anio_fin'    => null, // Año fin vacío = vigente
                'status'      => true,
            ],
            [
                'codigo'      => '31060',
                'nombre'      => 'Mención Ciencia y Tecnología',
                'descripcion' => 'Plan de estudio oficial de Ciencia y Tecnología según Circular N° 01 (enero 2024). Coexiste con el plan 31059.',
                'anio_inicio' => 2024,
                'anio_fin'    => null, // Año fin vacío = vigente
                'status'      => true,
            ],
        ];

        foreach ($planes as $plan) {
            Pensum::updateOrCreate(
                ['codigo' => $plan['codigo']],
                [
                    'nombre'      => $plan['nombre'],
                    'descripcion' => $plan['descripcion'],
                    'anio_inicio' => $plan['anio_inicio'],
                    'anio_fin'    => $plan['anio_fin'],
                    'status'      => $plan['status'],
                ]
            );
        }

        $this->command->info('[PensumSeeder] Planes de estudio 31059 y 31060 sembrados exitosamente.');
    }
}

