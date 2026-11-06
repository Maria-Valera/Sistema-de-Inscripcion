<?php

namespace Database\Seeders;

use App\Models\Mencion;
use App\Models\Pensum;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class MencionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mencion = Mencion::updateOrCreate(
            ['codigo' => '01'],
            [
                'nombre' => 'Educación Media General',
                'ciclo'  => '50',
                'status' => true,
            ]
        );

        $pensum31059 = Pensum::where('codigo', '31059')->first();

        // Asignar el pensum 31059 y la mención 01 a las secciones existentes que no lo tengan
        if ($pensum31059) {
            Seccion::whereNull('pensum_id')->update([
                'pensum_id'  => $pensum31059->id,
                'mencion_id' => $mencion->id,
            ]);
        }

        $this->command->info('[MencionSeeder] Mención 01 (Ciclo 50 - Educación Media General) sembrada y asociada a secciones.');
    }
}

