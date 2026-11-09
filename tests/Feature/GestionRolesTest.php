<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GestionRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(1)->format('Y-m-d'),
            'cierre_anio_escolar' => now()->addMonths(11)->format('Y-m-d'),
            'status' => 'Activo',
        ]);
    }

    protected function crearAdmin(): User
    {
        $admin = User::factory()->create([
            'email' => 'admin.roles@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        return $admin;
    }

    /**
     * 1. Verifica que un Admin puede crear un rol seleccionando permisos específicos en el módulo existente.
     * 2. Verifica que el rol tiene exactamente esos permisos y no otros.
     */
    public function test_admin_puede_crear_rol_con_permisos_especificos()
    {
        $admin = $this->crearAdmin();

        $permisosDeseados = ['alumnos.ver', 'alumnos.editar', 'aulas.ver'];

        $response = $this->actingAs($admin)->post(route('admin.roles.modales.store'), [
            'name' => 'Coordinador Academico',
            'permisos_seleccionados' => $permisosDeseados,
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('success');

        $rol = Role::where('name', 'Coordinador Academico')->first();
        $this->assertNotNull($rol, 'El rol no fue creado en la base de datos.');

        // Verificar que tiene exactamente los permisos seleccionados
        foreach ($permisosDeseados as $permiso) {
            $this->assertTrue(
                $rol->hasPermissionTo($permiso),
                "El rol debería tener el permiso {$permiso}"
            );
        }

        // Verificar que no tiene otros permisos
        $this->assertFalse($rol->hasPermissionTo('alumnos.eliminar'));
        $this->assertFalse($rol->hasPermissionTo('aulas.crear'));
        $this->assertFalse($rol->hasPermissionTo('horarios.eliminar'));

        $this->assertEquals(count($permisosDeseados), $rol->permissions()->count());
    }

    /**
     * Verifica que un Admin puede editar un rol y sincronizar nuevos permisos granulares.
     */
    public function test_admin_puede_actualizar_rol_y_sus_permisos_granulares()
    {
        $admin = $this->crearAdmin();

        $rol = Role::create(['name' => 'Supervisor', 'guard_name' => 'web']);
        $rol->givePermissionTo(['aulas.ver']);

        $nuevosPermisos = ['alumnos.ver', 'horarios.ver'];

        $response = $this->actingAs($admin)->post(route('admin.roles.modales.update', $rol->id), [
            'name' => 'Supervisor General',
            'permisos_seleccionados' => $nuevosPermisos,
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $rolActualizado = $rol->fresh();
        $this->assertEquals('Supervisor General', $rolActualizado->name);

        $this->assertTrue($rolActualizado->hasPermissionTo('alumnos.ver'));
        $this->assertTrue($rolActualizado->hasPermissionTo('horarios.ver'));
        $this->assertFalse($rolActualizado->hasPermissionTo('aulas.ver'));
        $this->assertEquals(2, $rolActualizado->permissions()->count());
    }

    /**
     * 3. Verifica que el Admin ve el enlace 'Roles y Permisos' en el menú lateral.
     */
    public function test_admin_ve_enlace_roles_y_permisos_en_menu()
    {
        $admin = $this->crearAdmin();

        $response = $this->actingAs($admin)->get(route('admin.roles.index'));
        $response->assertOk();
        $response->assertSee('Roles y Permisos');
        $response->assertSee(route('admin.roles.index'));
    }

    /**
     * Verifica que usuarios sin permiso administrativo no ven el ítem de Roles y Permisos en el menú y reciben 403.
     */
    public function test_usuario_sin_permiso_no_accede_a_modulo_roles()
    {
        $representante = User::factory()->create([
            'email' => 'representante@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $representante->assignRole('Representante');

        $response = $this->actingAs($representante)->get(route('admin.roles.index'));
        $response->assertForbidden();
    }
}
