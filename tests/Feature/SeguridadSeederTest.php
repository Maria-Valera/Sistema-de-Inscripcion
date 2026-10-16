<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\User;
use Database\Seeders\SeguridadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeguridadSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * a) En local: el admin existe, con rol Admin, activo, debe_cambiar_password = false,
     *    y Hash::check de la contraseña de desarrollo es true.
     */
    public function test_admin_en_entorno_local()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(SeguridadSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin, 'El usuario admin@example.com debe existir');
        $this->assertTrue($admin->hasRole('Admin'), 'El admin debe tener el rol Admin');
        $this->assertEquals('Admin', $admin->rol_principal);
        $this->assertTrue((bool)$admin->activo);
        $this->assertFalse((bool)$admin->debe_cambiar_password);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('12345678', $admin->password));
    }

    /**
     * b) Fuera de local, sin ADMIN_INICIAL_*: el seeder lanza excepción.
     */
    public function test_fuera_de_local_sin_admin_inicial_lanza_excepcion()
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'seguridad.admin_email' => null,
            'seguridad.admin_password' => null,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->artisan('db:seed', ['--class' => SeguridadSeeder::class, '--force' => true]);
    }

    /**
     * c) Fuera de local, con ADMIN_INICIAL_* configuradas: el admin se crea con esas credenciales,
     *    debe_cambiar_password = true, email_verified_at no vacío, y la contraseña de desarrollo NO funciona.
     */
    public function test_fuera_de_local_con_credenciales_iniciales()
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'seguridad.admin_email' => 'superadmin@produccion.gob.ve',
            'seguridad.admin_password' => 'SecretaProd2026!',
        ]);

        $this->artisan('db:seed', ['--class' => SeguridadSeeder::class, '--force' => true]);

        $admin = User::where('email', 'superadmin@produccion.gob.ve')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertEquals('Admin', $admin->rol_principal);
        $this->assertTrue((bool)$admin->activo);
        $this->assertTrue((bool)$admin->debe_cambiar_password);
        $this->assertNotNull($admin->email_verified_at);

        // La clave de producción funciona
        $this->assertTrue(Hash::check('SecretaProd2026!', $admin->password));
        // La clave de desarrollo NO funciona
        $this->assertFalse(Hash::check('12345678', $admin->password));
    }

    /**
     * d) Fuera de local, si el admin ya existe, una segunda ejecución no le cambia la contraseña.
     */
    public function test_fuera_de_local_no_cambia_password_a_admin_existente()
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'seguridad.admin_email' => 'admin.prod@colegio.edu.ve',
            'seguridad.admin_password' => 'OriginalPassword99!',
        ]);

        $this->artisan('db:seed', ['--class' => SeguridadSeeder::class, '--force' => true]);

        $admin = User::where('email', 'admin.prod@colegio.edu.ve')->first();
        $passwordHashInicial = $admin->password;

        // Simulamos que el admin ya cambió su contraseña en producción
        $admin->password = Hash::make('NuevaPasswordPersonalizada123!');
        $admin->debe_cambiar_password = false;
        $admin->save();

        // Segunda ejecución del seeder con otra config de admin_password
        config(['seguridad.admin_password' => 'OtraPasswordDiferente!']);
        $this->artisan('db:seed', ['--class' => SeguridadSeeder::class, '--force' => true]);

        $admin->refresh();
        $this->assertTrue(Hash::check('NuevaPasswordPersonalizada123!', $admin->password));
        $this->assertFalse(Hash::check('OtraPasswordDiferente!', $admin->password));
        $this->assertFalse((bool)$admin->debe_cambiar_password);
    }

    /**
     * e) Idempotencia: ejecutar SeguridadSeeder dos veces no duplica usuarios, roles ni permisos.
     */
    public function test_idempotencia_seguridad_seeder()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(SeguridadSeeder::class);
        $usersCount1 = User::count();
        $rolesCount1 = Role::count();
        $permissionsCount1 = Permission::count();

        // Segunda ejecución
        $this->seed(SeguridadSeeder::class);
        $usersCount2 = User::count();
        $rolesCount2 = Role::count();
        $permissionsCount2 = Permission::count();

        $this->assertEquals($usersCount1, $usersCount2, 'No deben duplicarse usuarios');
        $this->assertEquals($rolesCount1, $rolesCount2, 'No deben duplicarse roles');
        $this->assertEquals($permissionsCount1, $permissionsCount2, 'No deben duplicarse permisos');
    }

    /**
     * f) La contraseña nunca está en texto plano en la base de datos.
     */
    public function test_password_nunca_esta_en_texto_plano()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(SeguridadSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotEquals('12345678', $admin->password);
        $this->assertStringStartsWith('$2y$', $admin->password);
    }

    /**
     * g) Los usuarios que crean los seeders (admin, representantes y de prueba)
     *    tienen email_verified_at y pasan el middleware verified: una petición autenticada al dashboard no redirige a /email/verify.
     */
    public function test_usuarios_de_seeders_pasan_middleware_verified()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(SeguridadSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($admin->email_verified_at);

        // Al acceder a /home autenticado como admin, NO debe redirigir a /email/verify
        $response = $this->actingAs($admin)->get(route('home'));
        $this->assertNotEquals(route('verification.notice'), $response->headers->get('Location'));
    }

    /**
     * h) En local, si el admin ya tiene un rol extra y un email_verified_at, una segunda ejecución conserva ambos.
     */
    public function test_admin_en_local_conserva_roles_extra_y_verified_at()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->seed(SeguridadSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();
        $fechaVerificacionFija = now()->subDays(10);
        $admin->email_verified_at = $fechaVerificacionFija;
        $admin->save();

        // Asignar rol extra
        $admin->assignRole('Subdirectora');
        $this->assertTrue($admin->hasRole('Subdirectora'));
        $this->assertTrue($admin->hasRole('Admin'));

        // Segunda ejecución del seeder
        $this->seed(SeguridadSeeder::class);

        $admin->refresh();
        $this->assertTrue($admin->hasRole('Admin'), 'Debe conservar el rol Admin');
        $this->assertTrue($admin->hasRole('Subdirectora'), 'Debe conservar el rol extra Subdirectora');
        $this->assertEquals(
            $fechaVerificacionFija->toDateTimeString(),
            $admin->email_verified_at->toDateTimeString(),
            'Debe conservar su email_verified_at original'
        );
    }

    /**
     * i) Correr SeguridadSeeder no agrega filas a auditorias,
     *    y ninguna fila existente de auditorias contiene la palabra 'password' en valores_anteriores ni valores_nuevos.
     */
    public function test_seguridad_seeder_no_agrega_auditorias_y_sin_passwords_en_auditoria()
    {
        $this->app->detectEnvironment(fn () => 'local');

        $auditoriasInicial = Auditoria::count();

        $this->seed(SeguridadSeeder::class);

        $auditoriasFinal = Auditoria::count();
        $this->assertEquals($auditoriasInicial, $auditoriasFinal, 'SeguridadSeeder no debe crear registros en auditorias');

        // Verificar que ninguna fila de auditorias contenga 'password' en valores_anteriores ni valores_nuevos
        $auditorias = Auditoria::all();
        foreach ($auditorias as $auditoria) {
            $ant = json_encode($auditoria->valores_anteriores ?? []);
            $nuev = json_encode($auditoria->valores_nuevos ?? []);

            $this->assertStringNotContainsStringIgnoringCase('password', $ant);
            $this->assertStringNotContainsStringIgnoringCase('password', $nuev);
        }
    }
}
