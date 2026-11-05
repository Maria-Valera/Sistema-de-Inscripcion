<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modulos = config('modulos');
        $permisosEnBD = Permission::pluck('name')->toArray();
        $permisosNoEnArchivo = [];

        foreach ($modulos as $claveModulo => $modulo) {
            foreach ($modulo['permisos'] as $permiso) {
                // Crear permiso si no existe
                Permission::firstOrCreate([
                    'name' => $permiso,
                    'guard_name' => 'web'
                ]);
            }
        }

        // Reportar permisos en BD que no están en config/modulos.php
        foreach ($permisosEnBD as $permisoBD) {
            $existeEnConfig = false;
            foreach ($modulos as $modulo) {
                if (in_array($permisoBD, $modulo['permisos'])) {
                    $existeEnConfig = true;
                    break;
                }
            }
            if (!$existeEnConfig) {
                $permisosNoEnArchivo[] = $permisoBD;
            }
        }

        if (!empty($permisosNoEnArchivo)) {
            $this->command->warn('ATENCIÓN: Los siguientes permisos existen en la base de datos pero NO en config/modulos.php:');
            foreach ($permisosNoEnArchivo as $permiso) {
                $this->command->warn("  - {$permiso}");
            }
        }

        $this->command->info('Seeder de permisos ejecutado correctamente.');
    }
}
