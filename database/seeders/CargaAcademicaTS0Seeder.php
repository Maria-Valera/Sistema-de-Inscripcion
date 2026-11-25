<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Services\Cargas\GenerarCargasAcademicasService;
use App\Support\AnioEscolarSeleccionado;
use Illuminate\Database\Seeder;

class CargaAcademicaTS0Seeder extends Seeder
{
    /**
     * Siembra las cargas académicas del año 2026-2027 a partir de docente_area_grados
     * utilizando directamente el servicio GenerarCargasAcademicasService.
     */
    public function run(): void
    {
        $this->command?->info('Iniciando CargaAcademicaTS0Seeder...');

        // Buscar el año 2026-2027 o el activo
        $anio = AnioEscolar::where(function ($q) {
            $q->where('inicio_anio_escolar', 'like', '2026%')
              ->orWhereIn('status', ['Activo', 'Extendido']);
        })->first() ?? AnioEscolarSeleccionado::modelo();

        if (!$anio) {
            $this->command?->warn('No se encontró el año escolar 2026-2027 ni un año activo para sembrar cargas TS0.');
            return;
        }

        // Asegurar que tenga un calendario académico confirmado
        $calendario = CalendarioAcademico::firstOrCreate(
            ['anio_escolar_id' => $anio->id],
            [
                'estado' => 'confirmado',
                'fecha_confirmacion' => now(),
            ]
        );

        $servicio = app(GenerarCargasAcademicasService::class);
        $resumen = $servicio->generar($calendario->id);

        $this->command?->info("✓ CargaAcademicaTS0Seeder completado para año {$anio->id}:");
        $this->command?->info("  - Creadas: {$resumen['creadas']}");
        $this->command?->info("  - Actualizadas: {$resumen['actualizadas']}");
        $this->command?->info("  - Omitidas: {$resumen['omitidas']}");
    }
}
