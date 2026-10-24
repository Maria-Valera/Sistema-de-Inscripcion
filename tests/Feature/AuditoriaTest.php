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

        // Ejecutar PermisosSeeder y RoleSeeder para tener roles y permisos disponibles
        $this->seed(\Database\Seeders\PermisosSeeder::class);
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

    /**
     * Test que los modelos clave (Persona) generan registros en auditorias al ser creados, actualizados y eliminados
     */
    public function test_persona_genera_auditoria_en_crear_actualizar_y_eliminar()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        // Crear dependencias mínimas para Persona
        $tipoDocId = DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'Cédula',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $generoId = DB::table('generos')->insertGetId([
            'genero' => 'Masculino',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Crear Persona
        $persona = \App\Models\Persona::create([
            'primer_nombre' => 'Carlos',
            'primer_apellido' => 'Perez',
            'numero_documento' => 'V-12345678',
            'fecha_nacimiento' => '1990-01-01',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
            'email' => 'carlos@example.com',
        ]);

        $auditoriaCrear = Auditoria::where('auditable_type', \App\Models\Persona::class)
            ->where('auditable_id', $persona->id)
            ->where('accion', 'crear')
            ->first();

        $this->assertNotNull($auditoriaCrear);
        $this->assertEquals('Carlos', $auditoriaCrear->valores_nuevos['primer_nombre'] ?? null);

        // 2. Actualizar Persona
        $persona->update([
            'primer_nombre' => 'Carlos Alberto',
        ]);

        $auditoriaActualizar = Auditoria::where('auditable_type', \App\Models\Persona::class)
            ->where('auditable_id', $persona->id)
            ->where('accion', 'actualizar')
            ->first();

        $this->assertNotNull($auditoriaActualizar);
        $this->assertEquals('Carlos Alberto', $auditoriaActualizar->valores_nuevos['primer_nombre'] ?? null);

        // 3. Eliminar Persona
        $personaId = $persona->id;
        $persona->delete();

        $auditoriaEliminar = Auditoria::where('auditable_type', \App\Models\Persona::class)
            ->where('auditable_id', $personaId)
            ->where('accion', 'eliminar')
            ->first();

        $this->assertNotNull($auditoriaEliminar);
    }

    /**
     * Test que los modelos clave (Alumno) generan registros en auditorias
     */
    public function test_alumno_genera_auditoria_en_crear_y_actualizar()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin);

        $tipoDocId = DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'Cédula Estudiantil',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $generoId = DB::table('generos')->insertGetId([
            'genero' => 'Femenino',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $persona = \App\Models\Persona::create([
            'primer_nombre' => 'Maria',
            'primer_apellido' => 'Gomez',
            'numero_documento' => 'V-87654321',
            'fecha_nacimiento' => '2010-05-15',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
        ]);

        $alumno = \App\Models\Alumno::create([
            'persona_id' => $persona->id,
            'talla_zapato' => '36',
            'peso' => 45,
            'estatura' => 1.50,
        ]);

        $auditoriaAlumno = Auditoria::where('auditable_type', \App\Models\Alumno::class)
            ->where('auditable_id', $alumno->id)
            ->where('accion', 'crear')
            ->first();

        $this->assertNotNull($auditoriaAlumno);
        $this->assertEquals(45, $auditoriaAlumno->valores_nuevos['peso'] ?? null);

        // Actualizar Alumno
        $alumno->update(['peso' => 47]);
        $auditoriaUpdate = Auditoria::where('auditable_type', \App\Models\Alumno::class)
            ->where('auditable_id', $alumno->id)
            ->where('accion', 'actualizar')
            ->first();

        $this->assertNotNull($auditoriaUpdate);
        $this->assertEquals(47, $auditoriaUpdate->valores_nuevos['peso'] ?? null);
    }

    /**
     * Test que la pantalla de auditoría solo es accesible para usuarios con permiso auditoria.ver (Admin)
     */
    public function test_pantalla_auditoria_solo_accesible_para_admin_con_permiso()
    {
        // 1. Invitado es redirigido a login
        $response = $this->get(route('admin.auditoria.index'));
        $response->assertRedirect(route('login'));

        // 2. Usuario sin permiso auditoria.ver recibe 403
        $docenteUser = User::factory()->create();
        $docenteUser->assignRole('Docente');

        $responseDocente = $this->actingAs($docenteUser)->get(route('admin.auditoria.index'));
        $responseDocente->assertStatus(403);

        // 3. Usuario Admin con permiso auditoria.ver tiene acceso 200
        $adminUser = User::factory()->create();
        $adminUser->assignRole('Admin');

        $responseAdmin = $this->actingAs($adminUser)->get(route('admin.auditoria.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Auditoría del Sistema');
    }

    /**
     * Test que la pantalla de auditoría es estrictamente de solo lectura y no tiene botones de acción destructiva
     */
    public function test_pantalla_auditoria_es_de_solo_lectura_sin_botones_de_accion()
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('Admin');

        Auditoria::registrar('crear', null, null, ['dato' => 'ejemplo'], 'Registro de prueba para vista');

        $response = $this->actingAs($adminUser)->get(route('admin.auditoria.index'));
        $response->assertStatus(200);

        // No debe contener botones ni enlaces de acción destructiva o edición
        $response->assertDontSee('btn-danger', false);
        $response->assertDontSee('Eliminar');
        $response->assertDontSee('Editar');
        $response->assertDontSee('Borrar');
    }
}
