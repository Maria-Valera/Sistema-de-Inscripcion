<?php

namespace Database\Seeders;

use App\Models\DocenteAreaGrado;
use App\Models\DetalleDocenteEstudio;
use App\Models\AreaEstudioRealizado;
use App\Models\Grado;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class DocenteAreaGradoSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar datos existentes para crear datos más realistas
        DocenteAreaGrado::query()->delete();

        // Obtener datos existentes
        $detallesDocente = DetalleDocenteEstudio::take(3)->get();
        $areasEstudio = AreaEstudioRealizado::take(2)->get();
        $grados = Grado::take(2)->get();
        $secciones = Seccion::take(3)->get(); // Usar 3 secciones para más variedad

        // Crear asignaciones más realistas - cada docente con pocas materias
        $contador = 0;
        foreach ($detallesDocente as $indexDetalle => $detalle) {
            // Cada docente tiene 2 materias diferentes
            $materiasPorDocente = $areasEstudio->slice(0, 2);
            foreach ($materiasPorDocente as $indexArea => $area) {
                // Cada materia en 1 grado
                $gradosPorMateria = $grados->slice($indexArea, 1);
                foreach ($gradosPorMateria as $grado) {
                    // Cada grado en una sección diferente (rotar secciones)
                    $seccionIndex = ($indexDetalle + $indexArea) % $secciones->count();
                    $seccion = $secciones[$seccionIndex];

                    // Solo crear si el docente tiene relación con el área
                    if ($detalle->docente_id && $area->area_formacion_id) {
                        DocenteAreaGrado::create([
                            'docente_estudio_realizado_id' => $detalle->id,
                            'area_estudio_realizado_id' => $area->id,
                            'grado_id' => $grado->id,
                            'seccion_id' => $seccion->id,
                            'tipo_asignacion' => 'area',
                            'status' => true,
                        ]);
                        $contador++;
                    }
                }
            }
        }

        $this->command->info("Asignaciones Docente-Area-Grado creadas correctamente: $contador registros.");
    }
}
