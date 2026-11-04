<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Cache;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modulos = config('modulos');
        $rolesModulos = config('roles_modulos');

        // Crear roles
        $roles = [
            'Admin',
            'Editor',
            'User',
            'Representante',
            'Subdirectora',
            'Docente'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }

        // Asignar permisos según config/roles_modulos.php
        foreach ($rolesModulos as $rolNombre => $modulosRol) {
            $rol = Role::where('name', $rolNombre)->first();
            if (!$rol) {
                continue;
            }

            $permisosRol = [];

            foreach ($modulosRol as $moduloNombre => $permisosConfig) {
                if (!isset($modulos[$moduloNombre])) {
                    $this->command->warn("Advertencia: El módulo '$moduloNombre' no existe en config/modulos.php");
                    continue;
                }

                if ($permisosConfig === '*') {
                    // Todos los permisos del módulo
                    $permisosRol = array_merge($permisosRol, $modulos[$moduloNombre]['permisos']);
                } else {
                    // Permisos específicos
                    foreach ($permisosConfig as $permisoAccion) {
                        $permisoNombre = $moduloNombre . '.' . $permisoAccion;
                        $permisosRol[] = $permisoNombre;
                    }
                }
            }

            // Eliminar duplicados
            $permisosRol = array_unique($permisosRol);

            // Sincronizar permisos (reemplaza los existentes)
            $permisosModelos = Permission::whereIn('name', $permisosRol)->get();
            $rol->syncPermissions($permisosModelos);

            $this->command->info("Rol '$rolNombre': " . count($permisosModelos) . " permisos asignados");
        }

        $this->command->info(string: 'Seeder de roles ejecutado correctamente.');
    }
}
