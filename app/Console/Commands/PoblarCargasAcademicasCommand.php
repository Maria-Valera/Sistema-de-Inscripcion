<?php

namespace App\Console\Commands;

use App\Models\AnioEscolar;
use App\Models\CargaAcademica;
use App\Models\DocenteAreaGrado;
use Illuminate\Console\Command;

class PoblarCargasAcademicasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horario:poblar-cargas {--anio= : ID opcional del año escolar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Puebla la tabla cargas_academicas a partir de docente_area_grados';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $anioId = $this->option('anio');
        if ($anioId) {
            $anioEscolar = AnioEscolar::find($anioId);
        } else {
            $anioEscolar = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first()
                ?? AnioEscolar::latest('id')->first();
        }

        if (!$anioEscolar) {
            $this->error("No se encontró el año escolar.");
            return Command::FAILURE;
        }

        $this->info("Poblando cargas_academicas para el año escolar: {$anioEscolar->nombre_periodo} (ID: {$anioEscolar->id})...");

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
                    'anio_escolar_id' => $anioEscolar->id,
                    'docente_id' => $docenteId,
                    'area_formacion_id' => $areaFormacionId,
                    'seccion_id' => $seccionId,
                ]);
                $creados++;
            }
        }

        $this->info("¡Éxito! Se sincronizaron {$creados} registros de carga académica.");
        return Command::SUCCESS;
    }
}
