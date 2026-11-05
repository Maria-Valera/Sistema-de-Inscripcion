<?php

namespace App\Console\Commands;

use App\Models\AnioEscolar;
use App\Services\ValidadorHorarioService;
use Database\Seeders\HorarioPruebasSeeder;
use Illuminate\Console\Command;

class PrepararHorarioPruebasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'horario:preparar-pruebas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta el seeder de pruebas de horarios preparando datos limpios para obtener 0 errores de validación.';

    /**
     * Execute the console command.
     */
    public function handle(ValidadorHorarioService $validador): int
    {
        $this->info("═══════════════════════════════════════════════════════════════════════");
        $this->info("   PREPARACIÓN DE DATOS DE PRUEBA PARA GENERACIÓN DE HORARIOS        ");
        $this->info("═══════════════════════════════════════════════════════════════════════");

        if (!app()->environment(['local', 'testing'])) {
            $this->error("Este comando solo puede ser ejecutado en entornos 'local' o 'testing'.");
            return Command::FAILURE;
        }

        $seeder = new HorarioPruebasSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        $resumen = $seeder->resumen;

        $this->newLine();
        $this->info("RESUMEN DE PREPARACIÓN:");
        $this->line("  * Áreas actualizadas a {$resumen['bloquesPermitidos']} bloques semanales.");
        $this->line("  * Docentes activos: {$resumen['docentesActivos']}");
        $this->line("  * Cargas académicas creadas/verificadas: {$resumen['cargasCreadas']}");
        $this->line("  * Tabla horario_asignaciones limpiada.");

        // Ejecutar validación para corroborar el estado
        $anio = AnioEscolar::whereIn('status', ['Activo', 'Extendido'])->first()
            ?? AnioEscolar::latest('id')->first();

        if ($anio) {
            $this->newLine();
            $this->info("Ejecutando validación de horarios para año escolar ID {$anio->id} ({$anio->nombre_periodo})...");
            $resultado = $validador->validar($anio->id);

            $numErrores = count($resultado['errores']);
            $numAvisos = count($resultado['avisos']);

            if ($numErrores === 0) {
                $this->info("✓ Validación completada con éxito: {$numErrores} errores críticos.");
            } else {
                $this->error("✗ La validación detectó {$numErrores} error(es) crítico(s):");
                foreach ($resultado['errores'] as $err) {
                    $this->line("    - [{$err['codigo']}] {$err['mensaje']}");
                }
            }

            if ($numAvisos > 0) {
                $this->comment("  Información adicional: {$numAvisos} aviso(s) no bloqueante(s) detectado(s).");
            }
        }

        $this->newLine();
        $this->info("✓ Proceso finalizado correctamente.");
        return Command::SUCCESS;
    }
}
