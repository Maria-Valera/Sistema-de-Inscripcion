<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class PermisosLimpiar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permisos:limpiar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista y elimina permisos huérfanos que no están en config/modulos.php';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modulos = config('modulos');
        $permisosEnConfig = [];

        // Recopilar todos los permisos definidos en config/modulos.php
        foreach ($modulos as $modulo) {
            foreach ($modulo['permisos'] as $permiso) {
                $permisosEnConfig[] = $permiso;
            }
        }

        // Obtener permisos en la base de datos
        $permisosEnBD = Permission::pluck('name')->toArray();

        // Encontrar permisos huérfanos
        $permisosHuerfanos = array_diff($permisosEnBD, $permisosEnConfig);

        if (empty($permisosHuerfanos)) {
            $this->info('No hay permisos huérfanos en la base de datos.');
            return 0;
        }

        $this->warn('Permisos huérfanos encontrados:');
        foreach ($permisosHuerfanos as $permiso) {
            $this->line("  - $permiso");
        }

        $this->newLine();
        $confirmar = $this->confirm('¿Desea eliminar estos permisos huérfanos?', false);

        if (!$confirmar) {
            $this->info('Operación cancelada. No se eliminaron permisos.');
            return 0;
        }

        // Eliminar permisos huérfanos
        $eliminados = Permission::whereIn('name', $permisosHuerfanos)->delete();

        $this->info("Se eliminaron $eliminados permisos huérfanos.");
        return 0;
    }
}
