<?php

namespace Database\Seeders;

use App\Models\seccion_aula;
use App\Models\Seccion;
use App\Models\Aula;
use Illuminate\Database\Seeder;

class SeccionAulaSeeder extends Seeder
{
    public function run(): void
    {
        $secciones = Seccion::all();
        $aulas = Aula::where('status', true)->where('tipo_aula', 'Aula Regular')->get();
        if ($aulas->isEmpty()) {
            $aulas = Aula::where('status', true)->get();
        }

        if ($aulas->isEmpty()) {
            $this->command->warn('No hay aulas disponibles para asignar.');
            return;
        }

        foreach ($secciones as $index => $seccion) {
            // Asignar un aula regular rotativa
            $aula = $aulas[$index % $aulas->count()];

            seccion_aula::updateOrCreate(
                ['id_seccion' => $seccion->id],
                ['id_aula' => $aula->id_aula, 'status' => true]
            );
        }

        $this->command->info('Asignaciones Sección-Aula creadas correctamente.');
    }
}
