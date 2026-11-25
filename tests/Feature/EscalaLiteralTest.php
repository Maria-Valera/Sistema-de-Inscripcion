<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\EscalaLiteral;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EscalaLiteralTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $userSinPermiso;
    protected AnioEscolar $anioActivo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Admin',
        ]);
        $this->adminUser->assignRole('Admin');

        $this->userSinPermiso = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Docente',
        ]);
        $this->userSinPermiso->assignRole('Docente');

        $this->anioActivo = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(2)->toDateString(),
            'cierre_anio_escolar' => now()->addMonths(10)->toDateString(),
            'status' => 'Activo',
        ]);
    }

    public function test_admin_puede_ver_listado_escala_literal(): void
    {
        EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'A',
            'descripcion' => 'Excelente',
            'nota_desde' => 19,
            'nota_hasta' => 20,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.escala-literal.index'));

        $response->assertOk();
        $response->assertSee('Excelente');
        $response->assertSee('19');
        $response->assertSee('20');
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_escala_literal(): void
    {
        $response = $this->actingAs($this->userSinPermiso)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.escala-literal.index'));

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_escala_literal(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.escala-literal.store'), [
                'letra' => 'A',
                'descripcion' => 'Excelente',
                'nota_desde' => 19,
                'nota_hasta' => 20,
            ]);

        $response->assertRedirect(route('admin.escala-literal.index'));
        $this->assertDatabaseHas('escala_literals', [
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'A',
            'nota_desde' => 19,
            'nota_hasta' => 20,
        ]);
    }

    public function test_falla_si_nota_hasta_es_menor_a_nota_desde(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.escala-literal.store'), [
                'letra' => 'B',
                'descripcion' => 'Invalida',
                'nota_desde' => 18,
                'nota_hasta' => 15,
            ]);

        $response->assertSessionHasErrors(['nota_hasta']);
    }

    public function test_regla_de_negocio_detecta_solapamiento_de_rango(): void
    {
        EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'A',
            'descripcion' => 'Excelente',
            'nota_desde' => 19,
            'nota_hasta' => 20,
        ]);

        // Intentar registrar un rango que colisiona (ej. 18 a 19 pts)
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.escala-literal.store'), [
                'letra' => 'B',
                'descripcion' => 'Bueno',
                'nota_desde' => 18,
                'nota_hasta' => 19,
            ]);

        $response->assertSessionHasErrors(['nota_desde']);
    }

    public function test_admin_puede_actualizar_escala_literal_sin_falso_solapamiento(): void
    {
        $escala = EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'B',
            'descripcion' => 'Bueno',
            'nota_desde' => 16,
            'nota_hasta' => 18,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->put(route('admin.escala-literal.update', $escala), [
                'letra' => 'B',
                'descripcion' => 'Muy Bueno',
                'nota_desde' => 16,
                'nota_hasta' => 18,
            ]);

        $response->assertRedirect(route('admin.escala-literal.index'));
        $this->assertDatabaseHas('escala_literals', [
            'id' => $escala->id,
            'descripcion' => 'Muy Bueno',
        ]);
    }

    public function test_admin_puede_eliminar_escala_literal(): void
    {
        $escala = EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'C',
            'descripcion' => 'Aprobado',
            'nota_desde' => 12,
            'nota_hasta' => 15,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->delete(route('admin.escala-literal.destroy', $escala));

        $response->assertRedirect(route('admin.escala-literal.index'));
        $this->assertDatabaseMissing('escala_literals', [
            'id' => $escala->id,
        ]);
    }

    public function test_crear_escala_literal_ajax_retorna_201_json(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->postJson(route('admin.escala-literal.store'), [
                'letra' => 'A',
                'descripcion' => 'Excelente',
                'nota_desde' => 19,
                'nota_hasta' => 20,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Escala literal registrada exitosamente.',
        ]);
        $this->assertDatabaseHas('escala_literals', [
            'letra' => 'A',
            'nota_desde' => 19,
            'nota_hasta' => 20,
        ]);
    }

    public function test_error_validacion_ajax_retorna_422_json_con_mensajes_en_espanol(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->postJson(route('admin.escala-literal.store'), [
                'letra' => '',
                'nota_desde' => 25,
                'nota_hasta' => 10,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['letra', 'nota_desde', 'nota_hasta']);
        $json = $response->json();
        $this->assertStringContainsString('obligatori', $json['errors']['letra'][0]);
        $this->assertStringContainsString('mayor a 20', $json['errors']['nota_desde'][0]);
        $this->assertStringContainsString('mayor o igual', $json['errors']['nota_hasta'][0]);
    }

    public function test_error_solapamiento_ajax_retorna_422_con_mensaje_en_espanol(): void
    {
        EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'A',
            'descripcion' => 'Excelente',
            'nota_desde' => 17,
            'nota_hasta' => 20,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->postJson(route('admin.escala-literal.store'), [
                'letra' => 'B',
                'descripcion' => 'Bueno',
                'nota_desde' => 15,
                'nota_hasta' => 18,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nota_desde']);
        $this->assertStringContainsString('se cruza con la escala [A]', $response->json()['errors']['nota_desde'][0]);
    }

    public function test_actualizar_escala_literal_ajax_retorna_200_json(): void
    {
        $escala = EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'C',
            'descripcion' => 'Aprobado',
            'nota_desde' => 10,
            'nota_hasta' => 13,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->putJson(route('admin.escala-literal.update', $escala), [
                'letra' => 'C',
                'descripcion' => 'Aprobado Regular',
                'nota_desde' => 10,
                'nota_hasta' => 14,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Escala literal actualizada exitosamente.',
        ]);
        $this->assertDatabaseHas('escala_literals', [
            'id' => $escala->id,
            'descripcion' => 'Aprobado Regular',
            'nota_hasta' => 14,
        ]);
    }

    public function test_error_validacion_actualizar_escala_literal_ajax_retorna_422_json(): void
    {
        $escala = EscalaLiteral::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'letra' => 'D',
            'descripcion' => 'Deficiente',
            'nota_desde' => 1,
            'nota_hasta' => 9,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->putJson(route('admin.escala-literal.update', $escala), [
                'letra' => '',
                'nota_desde' => 12,
                'nota_hasta' => 5,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['letra', 'nota_hasta']);
    }
}
