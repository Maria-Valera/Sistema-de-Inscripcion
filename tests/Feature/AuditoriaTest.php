<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar RoleSeeder para tener roles disponibles
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    /**
     * Test que update sobre Auditoria falla por Eloquent
     */
    public function test_update_auditoria_falla_por_eloquent()
    {
        $auditoria = Auditoria::registrar('test', null, null, ['test' => 'data'], 'Test de auditoria');

        $this->assertNotNull($auditoria);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No se permite actualizar registros en la tabla de auditorias. La tabla es de solo lectura.');

        $auditoria->descripcion = 'Intento de actualización';
        $auditoria->save();
    }

    /**
     * Test que delete sobre Auditoria falla por Eloquent
     */
    public function test_delete_auditoria_falla_por_eloquent()
    {
        $auditoria = Auditoria::registrar('test', null, null, ['test' => 'data'], 'Test de auditoria');

        $this->assertNotNull($auditoria);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No se permite eliminar registros en la tabla de auditorias. La tabla es de solo lectura.');

        $auditoria->delete();
    }

    /**
     * Test que update sobre Auditoria falla por SQL directo (MySQL)
     */
    public function test_update_auditoria_falla_por_sql_directo()
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Solo se ejecuta en MySQL');
        }

        $auditoria = Auditoria::registrar('test', null, null, ['test' => 'data'], 'Test de auditoria');

        $this->assertNotNull($auditoria);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::statement("UPDATE auditorias SET descripcion = 'Intento SQL' WHERE id = ?", [$auditoria->id]);
    }

    /**
     * Test que delete sobre Auditoria falla por SQL directo (MySQL)
     */
    public function test_delete_auditoria_falla_por_sql_directo()
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Solo se ejecuta en MySQL');
        }

        $auditoria = Auditoria::registrar('test', null, null, ['test' => 'data'], 'Test de auditoria');

        $this->assertNotNull($auditoria);

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::statement("DELETE FROM auditorias WHERE id = ?", [$auditoria->id]);
    }

    /**
     * Test que crear un User genera auditoría sin contraseña
     */
    public function test_crear_user_genera_auditoria_sin_contrasena()
    {
        // Simular un request HTTP para que el auditoría pueda capturar datos
        $this->actingAs(User::factory()->create());

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $auditoria = Auditoria::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('accion', 'crear')
            ->first();

        $this->assertNotNull($auditoria);

        $valoresNuevos = $auditoria->valores_nuevos;
        $this->assertArrayNotHasKey('password', $valoresNuevos);
        $this->assertArrayNotHasKey('remember_token', $valoresNuevos);
    }

    /**
     * Test que modificar un User genera auditoría sin contraseña
     */
    public function test_modificar_user_genera_auditoria_sin_contrasena()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Limpiar auditorías del create
        Auditoria::truncate();

        $user->update([
            'name' => 'Updated User',
        ]);

        $auditoria = Auditoria::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('accion', 'actualizar')
            ->first();

        $this->assertNotNull($auditoria);

        $valoresNuevos = $auditoria->valores_nuevos;
        $this->assertArrayNotHasKey('password', $valoresNuevos);
        $this->assertArrayNotHasKey('remember_token', $valoresNuevos);
    }

    /**
     * Test que eliminar un User genera auditoría sin contraseña
     */
    public function test_eliminar_user_genera_auditoria_sin_contrasena()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $userId = $user->id;

        // Limpiar auditorías del create
        Auditoria::truncate();

        $user->delete();

        $auditoria = Auditoria::where('auditable_type', User::class)
            ->where('auditable_id', $userId)
            ->where('accion', 'eliminar')
            ->first();

        $this->assertNotNull($auditoria);

        $valoresAnteriores = $auditoria->valores_anteriores;
        $this->assertArrayNotHasKey('password', $valoresAnteriores);
        $this->assertArrayNotHasKey('remember_token', $valoresAnteriores);
    }

    /**
     * Test rolPrincipalEfectivo() cuando rol_principal está asignado
     */
    public function test_rol_principal_efectivo_con_rol_principal_asignado()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'rol_principal' => 'Admin',
        ]);

        $user->assignRole('Admin');
        $user->assignRole('Docente');

        $this->assertEquals('Admin', $user->rolPrincipalEfectivo());
    }

    /**
     * Test rolPrincipalEfectivo() cuando rol_principal no está asignado (retorna primer rol)
     */
    public function test_rol_principal_efectivo_sin_rol_principal_asignado()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user->assignRole('Docente');
        $user->assignRole('Admin');

        $this->assertEquals('Docente', $user->rolPrincipalEfectivo());
    }

    /**
     * Test rolPrincipalEfectivo() cuando no tiene roles
     */
    public function test_rol_principal_efectivo_sin_roles()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertNull($user->rolPrincipalEfectivo());
    }
}
