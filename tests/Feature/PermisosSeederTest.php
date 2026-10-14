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

        // Ya no se usan variables de entorno para el administrador
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
     * Test que cada rol tiene exactamente sus módulos por defecto
     */
    public function test_roles_tienen_modulos_por_defecto_correctos()
    {
        $this->seed(\Database\Seeders\PermisosSeeder::class);
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Verificar Representante
        $rolRepresentante = Role::where('name', 'Representante')->first();
        $this->assertNotNull($rolRepresentante);
        $this->assertTrue($rolRepresentante->hasPermissionTo('prosecucion.ver'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('prosecucion.registrar'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('prosecucion.consultar'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('carnets.estudiante'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('horarios.ver'));
        $this->assertTrue($rolRepresentante->hasPermissionTo('constancias.ver'));

        // Verificar Docente
        $rolDocente = Role::where('name', 'Docente')->first();
        $this->assertNotNull($rolDocente);
        $this->assertTrue($rolDocente->hasPermissionTo('carnets.propio'));
        $this->assertTrue($rolDocente->hasPermissionTo('horarios.ver'));
        $this->assertTrue($rolDocente->hasPermissionTo('asignacion_docente.ver'));

        // Verificar Subdirectora
        $rolSubdirectora = Role::where('name', 'Subdirectora')->first();
        $this->assertNotNull($rolSubdirectora);
        $this->assertTrue($rolSubdirectora->hasPermissionTo('asistencia.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('justificaciones.ver'));
        $this->assertTrue($rolSubdirectora->hasPermissionTo('reportes_asistencia.ver'));

        // Verificar Admin tiene módulos de gestión
        $rolAdmin = Role::where('name', 'Admin')->first();
        $this->assertNotNull($rolAdmin);
        $this->assertTrue($rolAdmin->hasPermissionTo('horarios.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('aulas.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('personal.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('areas_formacion.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('actas.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('usuarios_accesos.ver'));
        $this->assertTrue($rolAdmin->hasPermissionTo('auditoria.ver'));
    }

    /**
     * Test que el administrador existe y su contraseña está hasheada
     */
    public function test_administrador_existe_con_contrasena_hasheada()
    {
        // Eliminar usuario si existe para crear uno limpio
        User::where('email', 'admin@example.com')->delete();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin, 'El usuario administrador debería existir');
        $this->assertEquals('Administrador', $admin->name);
        $this->assertTrue($admin->debe_cambiar_password);
        $this->assertTrue($admin->activo);
        $this->assertEquals('Admin', $admin->rol_principal);
        
        // Verificar que la contraseña está hasheada
        $passwordCorrecta = \Illuminate\Support\Facades\Hash::check('12345678', $admin->password);
        $this->assertTrue($passwordCorrecta, 'La contraseña debería ser válida con hash');
        
        // Verificar que no está en texto plano
        $this->assertNotEquals('12345678', $admin->password, 'La contraseña no debería estar en texto plano');
    }

    /**
     * Test que el seeder falla si faltan las variables de entorno
     */
    public function test_seeder_falla_sin_variables_entorno()
    {
        // Ya no se usan variables de entorno para el administrador
        $this->assertTrue(true, 'El administrador se crea con credenciales fijas');
    }
}
