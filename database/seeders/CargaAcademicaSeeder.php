<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\CargaAcademica;
use App\Models\DocenteAreaGrado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CargaAcademicaSeeder extends Seeder
{
    /**
     * Llena la tabla cargas_academicas leyendo de docente_area_grados
     * para el año escolar activo.
     */
    public function run(): void
    {
        $anioActivo = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first()
            ?? AnioEscolar::latest('id')->first();

        if (!$anioActivo) {
            $this->command?->warn("No hay año escolar activo o registrado en el sistema.");
            return;
        }

        $dags = DocenteAreaGrado::where('status', true)
            ->with(['detalleDocenteEstudio', 'areaEstudios'])
            ->get();

        $creados = 0;
        foreach ($dags as $dag) {
            $docenteId = $dag->detalleDocenteEstudio?->docente_id;
            $areaFormacionId = $dag->areaEstudios?->area_formacion_id;
            $seccionId = $dag->seccion_id;

            if ($docenteId && $areaFormacionId && $seccionId) {
                CargaAcademica::firstOrCreate([
                    'anio_escolar_id' => $anioActivo->id,
                    'docente_id' => $docenteId,
                    'area_formacion_id' => $areaFormacionId,
                    'seccion_id' => $seccionId,
                ]);
                $creados++;
            }
        }

        $this->command?->info("Se crearon/verificaron {$creados} registros en cargas_academicas para el año escolar ID {$anioActivo->id}.");
    }
}
