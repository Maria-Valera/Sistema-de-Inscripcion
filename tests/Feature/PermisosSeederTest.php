<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermisosSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test que el seeder es idempotente (correrlo dos veces no duplica nada)
     */
    public function test_seeder_es_idempotente()
    {
        // Ejecutar PermisosSeeder por primera vez
        $this->seed(\Database\Seeders\PermisosSeeder::class);

        $cantidadPermisosPrimera = Permission::count();

        // Ejecutar PermisosSeeder por segunda vez
        $this->seed(\Database\Seeders\PermisosSeeder::class);

        $cantidadPermisosSegunda = Permission::count();

        // Verificar que no se duplicó nada
        $this->assertEquals($cantidadPermisosPrimera, $cantidadPermisosSegunda, 'Los permisos no deberían duplicarse');
    }

    /**
     * Test que cada rol tiene exactamente los permisos del mapeo de config/roles_modulos.php
     */
    public function test_roles_tienen_permisos_del_mapeo()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Verificar Representante tiene permisos del portal
        $rolRepresentante = Role::where('name', 'Representante')->first();
        $this->assertNotNull($rolRepresentante);
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_prosecucion.registrar'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_prosecucion.ver_planilla'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_prosecucion.generar_planilla'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_carnets.ver_estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_carnets.generar_estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_horarios.ver_estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_horarios.generar_estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_constancias.ver'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_constancias.generar'));

        // Verificar Docente tiene permisos del portal
        $rolDocente = Role::where('name', 'Docente')->first();
        $this->assertNotNull($rolDocente);
        $this->assertTrue($rolDocente->hasPermissionTo('portal_carnets.ver_docente'));
        $this->assertTrue($rolDocente->hasPermissionTo('portal_carnets.generar_docente'));
        $this->assertTrue($rolDocente->hasPermissionTo('portal_horarios.ver_docente'));
        $this->assertTrue($rolDocente->hasPermissionTo('portal_horarios.generar_docente'));
        $this->assertTrue($rolDocente->hasPermissionTo('portal_carga_academica.ver'));

        // Verificar Subdirectora tiene permisos asignados y restricciones clave
        $rolSubdirectora = Role::where('name', 'Subdirectora')->first();
        $this->assertNotNull($rolSubdirectora);
        $this->assertTrue($rolSubdirectora->hasPermissionTo('asistencia.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('justificaciones.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('reportes_asistencia.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('aulas.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('aulas.crear'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('calendario.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('calendario.crear'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('horarios.ver'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('horarios.crear'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('horarios.editar'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('horarios.eliminar'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('personal.ver'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('personal.crear'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('personal.editar'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('personal.eliminar'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('alumnos.ver'));
        $this->assertFalse($rolSubdirectora->hasPermissionTo('usuarios_accesos.ver'));


        // Verificar Admin tiene módulos administrativos y NO tiene permisos portal_*
        $rolAdmin = Role::where('name', 'Admin')->first();
        $this->assertNotNull($rolAdmin);
        $this->assertTrue($rolAdmin->hasPermissionTo('horarios.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('aulas.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('personal.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('areas_formacion.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('prosecucion.activar'));
        $this->assertFalse($rolAdmin->hasPermissionTo('portal_prosecucion.registrar'));
        $this->assertFalse($rolAdmin->hasPermissionTo('portal_carnets.ver_estudiante'));
        $this->assertFalse($rolAdmin->hasPermissionTo('portal_horarios.ver_estudiante'));
    }

    /**
     * Test que Representante y Docente NO tienen permisos administrativos
     */
    public function test_representante_docente_sin_permisos_administrativos()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $rolRepresentante = Role::where('name', 'Representante')->first();
        $rolDocente = Role::where('name', 'Docente')->first();

        // Permisos administrativos que NO deben tener
        $permisosAdministrativos = [
            'horarios.ver', 'horarios.crear', 'horarios.editar', 'horarios.eliminar',
            'aulas.ver', 'aulas.crear', 'aulas.editar', 'aulas.eliminar',
            'personal.ver', 'personal.crear', 'personal.editar', 'personal.eliminar',
            'prosecucion.ver', 'prosecucion.registrar', 'prosecucion.consultar',
            'asignacion_docente.ver', 'asignacion_docente.crear', 'asignacion_docente.editar', 'asignacion_docente.eliminar',
        ];

        foreach ($permisosAdministrativos as $permiso) {
            $this->assertFalse($rolRepresentante->hasPermissionTo($permiso), "Representante no debería tener $permiso");
            $this->assertFalse($rolDocente->hasPermissionTo($permiso), "Docente no debería tener $permiso");
        }
    }

    /**
     * Test que Docente tiene permisos de docente pero no de estudiante en portal_carnets
     */
    public function test_docente_permisos_carnets_separados()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $rolDocente = Role::where('name', 'Docente')->first();
        $rolRepresentante = Role::where('name', 'Representante')->first();

        // Docente tiene permisos de docente
        $this->assertTrue($rolDocente->hasPermissionTo('portal_carnets.ver_docente'));
        $this->assertTrue($rolDocente->hasPermissionTo('portal_carnets.generar_docente'));

        // Docente NO tiene permisos de estudiante
        $this->assertFalse($rolDocente->hasPermissionTo('portal_carnets.ver_estudiante'));
        $this->assertFalse($rolDocente->hasPermissionTo('portal_carnets.generar_estudiante'));

        // Representante tiene permisos de estudiante
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_carnets.ver_estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('portal_carnets.generar_estudiante'));

        // Representante NO tiene permisos de docente
        $this->assertFalse($rolRepresentante->hasPermissionTo('portal_carnets.ver_docente'));
        $this->assertFalse($rolRepresentante->hasPermissionTo('portal_carnets.generar_docente'));
    }

    /**
     * Test que Editor y User tienen 0 permisos
     */
    public function test_editor_user_sin_permisos()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $rolEditor = Role::where('name', 'Editor')->first();
        $rolUser = Role::where('name', 'User')->first();

        $this->assertNotNull($rolEditor);
        $this->assertNotNull($rolUser);

        $this->assertEquals(0, $rolEditor->permissions()->count(), 'Editor debería tener 0 permisos');
        $this->assertEquals(0, $rolUser->permissions()->count(), 'User debería tener 0 permisos');
    }

    /**
     * Test que un usuario con múltiples roles tiene la unión de permisos
     */
    public function test_usuario_multiples_roles_union_permisos()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Crear usuario con roles Docente y Representante
        $user = User::factory()->create();
        $user->assignRole('Docente');
        $user->assignRole('Representante');

        // Verificar que tiene permisos de ambos roles
        $this->assertTrue($user->hasPermissionTo('portal_carnets.ver_docente'), 'Debería tener permiso de Docente');
        $this->assertTrue($user->hasPermissionTo('portal_carnets.ver_estudiante'), 'Debería tener permiso de Representante');
        $this->assertTrue($user->hasPermissionTo('portal_carga_academica.ver'), 'Debería tener permiso de Docente');
        $this->assertTrue($user->hasPermissionTo('portal_prosecucion.registrar'), 'Debería tener permiso de Representante');
    }

    /**
     * Test de idempotencia de RoleSeeder
     */
    public function test_role_seeder_idempotente()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $permisosRepresentantePrimera = Role::where('name', 'Representante')->first()->permissions()->count();
        $permisosDocentePrimera = Role::where('name', 'Docente')->first()->permissions()->count();

        // Ejecutar RoleSeeder por segunda vez
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $permisosRepresentanteSegunda = Role::where('name', 'Representante')->first()->permissions()->count();
        $permisosDocenteSegunda = Role::where('name', 'Docente')->first()->permissions()->count();

        $this->assertEquals($permisosRepresentantePrimera, $permisosRepresentanteSegunda, 'Representante no debería cambiar permisos');
        $this->assertEquals($permisosDocentePrimera, $permisosDocenteSegunda, 'Docente no debería cambiar permisos');
    }
}
