<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AnioEscolarTest extends TestCase
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
            'inicio_anio_escolar' => '2025-09-01',
            'cierre_anio_escolar' => '2026-06-01',
            'status' => 'Activo',
        ]);
    }

    public function test_admin_puede_ver_listado_anios_escolares(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.anio-escolar.index'));

        $response->assertOk();
        $response->assertSee('Años Escolares');
        $response->assertSee('2025');
        $response->assertSee('2026');
        $response->assertSee('Activo');
    }

    public function test_usuario_sin_permiso_recibe_403(): void
    {
        $response = $this->actingAs($this->userSinPermiso)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.anio-escolar.index'));

        $response->assertStatus(403);

        $responseStore = $this->actingAs($this->userSinPermiso)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.anio-escolar.store'), [
                'inicio_anio_escolar' => '2026-09-01',
                'cierre_anio_escolar' => '2027-06-01',
                'status' => 'Activo',
            ]);

        $responseStore->assertStatus(403);
    }

    public function test_al_crear_anio_nuevo_con_estado_activo_el_anterior_pasa_a_inactivo(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.anio-escolar.store'), [
                'inicio_anio_escolar' => '2026-09-01',
                'cierre_anio_escolar' => '2027-06-01',
                'status' => 'Activo',
            ]);

        $response->assertRedirect(route('admin.anio-escolar.index'));

        // El nuevo año debe estar Activo
        $nuevoAnio = AnioEscolar::where('status', 'Activo')->first();
        $this->assertNotNull($nuevoAnio);
        $this->assertEquals('2026-09-01', $nuevoAnio->inicio_anio_escolar->format('Y-m-d'));
        $this->assertEquals('2027-06-01', $nuevoAnio->cierre_anio_escolar->format('Y-m-d'));

        // El año anterior debe haber pasado a Inactivo
        $this->assertEquals('Inactivo', $this->anioActivo->fresh()->status);

        // Solo debe haber un año activo
        $this->assertEquals(1, AnioEscolar::where('status', 'Activo')->count());
    }

    public function test_al_actualizar_anio_a_estado_activo_los_demas_pasan_a_inactivo(): void
    {
        $anioInactivo = AnioEscolar::create([
            'inicio_anio_escolar' => '2024-09-01',
            'cierre_anio_escolar' => '2025-06-01',
            'status' => 'Inactivo',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->put(route('admin.anio-escolar.update', $anioInactivo), [
                'inicio_anio_escolar' => '2024-09-01',
                'cierre_anio_escolar' => '2025-06-01',
                'status' => 'Activo',
            ]);

        $response->assertRedirect(route('admin.anio-escolar.index'));

        // El año actualizado ahora es Activo
        $this->assertEquals('Activo', $anioInactivo->fresh()->status);

        // El año que antes estaba activo ahora es Inactivo
        $this->assertEquals('Inactivo', $this->anioActivo->fresh()->status);

        // Solo debe haber un año activo
        $this->assertEquals(1, AnioEscolar::where('status', 'Activo')->count());
    }

    public function test_no_se_puede_crear_anio_con_fecha_cierre_menor_o_igual_a_inicio(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.anio-escolar.store'), [
                'inicio_anio_escolar' => '2026-09-01',
                'cierre_anio_escolar' => '2026-08-15',
                'status' => 'Activo',
            ]);

        $response->assertSessionHasErrors('cierre_anio_escolar');
        $this->assertDatabaseMissing('anio_escolars', [
            'inicio_anio_escolar' => '2026-09-01',
        ]);
    }

    public function test_admin_puede_eliminar_anio_escolar(): void
    {
        $anioAEliminar = AnioEscolar::create([
            'inicio_anio_escolar' => '2020-09-01',
            'cierre_anio_escolar' => '2021-06-01',
            'status' => 'Inactivo',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->delete(route('admin.anio-escolar.destroy', $anioAEliminar));

        $response->assertRedirect(route('admin.anio-escolar.index'));
        $this->assertDatabaseMissing('anio_escolars', [
            'id' => $anioAEliminar->id,
        ]);
    }
}
