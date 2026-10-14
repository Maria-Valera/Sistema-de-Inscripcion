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

        // Asignar módulos por defecto a cada rol
        $this->asignarModulosPorDefecto($modulos);

        $this->command->info(string: 'Seeder de roles ejecutado correctamente.');
    }

    /**
     * Asigna los módulos por defecto a cada rol según la configuración
     */
    private function asignarModulosPorDefecto(array $modulos): void
    {
        // ===== REPRESENTANTE =====
        // Permisos: prosecucion (registrar y consultar planilla), carnets (estudiante), horarios (estudiante), constancias
        $rolRepresentante = Role::where('name', 'Representante')->first();
        if ($rolRepresentante) {
            $permisosRepresentante = [];
            
            // prosecucion: registrar y consultar
            $permisosRepresentante = array_merge($permisosRepresentante, $modulos['prosecucion']['permisos']);
            
            // carnets: estudiante
            $permisosRepresentante[] = 'carnets.estudiante';
            
            // horarios: estudiante
            $permisosRepresentante[] = 'horarios.ver';
            
            // constancias
            $permisosRepresentante = array_merge($permisosRepresentante, $modulos['constancias']['permisos']);
            
            // Eliminar duplicados
            $permisosRepresentante = array_unique($permisosRepresentante);
            
            foreach ($permisosRepresentante as $permiso) {
                $permisoModel = Permission::where('name', $permiso)->first();
                if ($permisoModel) {
                    $rolRepresentante->givePermissionTo($permisoModel);
                }
            }
        }

        // ===== DOCENTE =====
        // Permisos: carnets (propio), horarios (propio), carga académica y listas de alumnos de sus secciones
        $rolDocente = Role::where('name', 'Docente')->first();
        if ($rolDocente) {
            $permisosDocente = [];
            
            // carnets: propio
            $permisosDocente[] = 'carnets.propio';
            
            // horarios: propio (ver)
            $permisosDocente[] = 'horarios.ver';
            
            // Carga académica y listas de alumnos (asignación docente)
            $permisosDocente = array_merge($permisosDocente, $modulos['asignacion_docente']['permisos']);
            
            // Eliminar duplicados
            $permisosDocente = array_unique($permisosDocente);
            
            foreach ($permisosDocente as $permiso) {
                $permisoModel = Permission::where('name', $permiso)->first();
                if ($permisoModel) {
                    $rolDocente->givePermissionTo($permisoModel);
                }
            }
        }

        // ===== SUBDIRECTORA =====
        // Permisos: asistencia, justificaciones, reportes_asistencia
        $rolSubdirectora = Role::where('name', 'Subdirectora')->first();
        if ($rolSubdirectora) {
            $permisosSubdirectora = [];
            
            // asistencia
            $permisosSubdirectora = array_merge($permisosSubdirectora, $modulos['asistencia']['permisos']);
            
            // justificaciones
            $permisosSubdirectora = array_merge($permisosSubdirectora, $modulos['justificaciones']['permisos']);
            
            // reportes_asistencia
            $permisosSubdirectora = array_merge($permisosSubdirectora, $modulos['reportes_asistencia']['permisos']);
            
            // Eliminar duplicados
            $permisosSubdirectora = array_unique($permisosSubdirectora);
            
            foreach ($permisosSubdirectora as $permiso) {
                $permisoModel = Permission::where('name', $permiso)->first();
                if ($permisoModel) {
                    $rolSubdirectora->givePermissionTo($permisoModel);
                }
            }
        }

        // ===== ADMIN =====
        // Permisos: horarios, aulas, personal, areas_formacion, prosecucion con activar, actas, usuarios_accesos, auditoria
        // + módulos sin dueño claro (calendario, inscripciones, alumnos, grados, secciones, bloques_horarios, asignacion_docente, 
        // grado_area_formacion, estudios_realizados, area_estudio_realizado, historico, percentil, permisos_reposos, 
        // inasistencia_docente, ubicacion_geografica, catalogos, instituciones)
        $rolAdmin = Role::where('name', 'Admin')->first();
        if ($rolAdmin) {
            $permisosAdmin = [];
            
            // Módulos base asignados al Director
            $modulosDirector = ['horarios', 'aulas', 'personal', 'areas_formacion', 'actas', 'usuarios_accesos', 'auditoria'];
            foreach ($modulosDirector as $modulo) {
                $permisosAdmin = array_merge($permisosAdmin, $modulos[$modulo]['permisos']);
            }
            
            // prosecucion con activar
            $permisosAdmin = array_merge($permisosAdmin, $modulos['prosecucion']['permisos']);
            $permisosAdmin[] = 'inscripciones.activar';
            
            // Módulos sin dueño claro
            $modulosSinDueno = [
                'calendario', 'inscripciones', 'alumnos', 'grados', 'secciones', 'bloques_horarios',
                'grado_area_formacion', 'estudios_realizados', 'area_estudio_realizado', 'historico',
                'percentil', 'permisos_reposos', 'inasistencia_docente', 'ubicacion_geografica',
                'catalogos', 'instituciones'
            ];
            foreach ($modulosSinDueno as $modulo) {
                $permisosAdmin = array_merge($permisosAdmin, $modulos[$modulo]['permisos']);
            }
            
            // Eliminar duplicados
            $permisosAdmin = array_unique($permisosAdmin);
            
            foreach ($permisosAdmin as $permiso) {
                $permisoModel = Permission::where('name', $permiso)->first();
                if ($permisoModel) {
                    $rolAdmin->givePermissionTo($permisoModel);
                }
            }
        }
    }
}
