<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\AutoridadFirmante;
use App\Models\Genero;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutoridadFirmanteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $userSinPermiso;
    protected AnioEscolar $anioActivo;
    protected Persona $persona1;
    protected Persona $persona2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $tipoDoc = TipoDocumento::create(['nombre' => 'Venezolano', 'status' => true]);
        $genero = Genero::create(['genero' => 'Masculino', 'status' => true]);

        $this->persona1 = Persona::create([
            'primer_nombre' => 'Carlos',
            'primer_apellido' => 'Mendoza',
            'numero_documento' => '12345678',
            'fecha_nacimiento' => '1980-01-01',
            'tipo_documento_id' => $tipoDoc->id,
            'genero_id' => $genero->id,
            'status' => true,
        ]);

        $this->persona2 = Persona::create([
            'primer_nombre' => 'María',
            'primer_apellido' => 'González',
            'numero_documento' => '87654321',
            'fecha_nacimiento' => '1985-05-15',
            'tipo_documento_id' => $tipoDoc->id,
            'genero_id' => $genero->id,
            'status' => true,
        ]);

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
            'inicio_anio_escolar' => '2026-09-01',
            'cierre_anio_escolar' => '2027-07-31',
            'status' => 'Activo',
        ]);
    }

    public function test_admin_puede_ver_listado_autoridades(): void
    {
        AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'gaceta' => 'Gaceta Oficial N° 40.000',
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.autoridad-firmante.index'));

        $response->assertOk();
        $response->assertSee('Director(a)');
        $response->assertSee('Carlos Mendoza');
        $response->assertSee('Gaceta Oficial N° 40.000');
    }

    public function test_usuario_sin_permiso_no_puede_acceder(): void
    {
        $response = $this->actingAs($this->userSinPermiso)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.autoridad-firmante.index'));

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_autoridad_firmante(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.autoridad-firmante.store'), [
                'cargo' => 'director',
                'persona_id' => $this->persona1->id,
                'encargado' => '1',
                'gaceta' => 'Resolución N° 123',
                'desde' => '2026-09-01',
                'hasta' => null,
            ]);

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertDatabaseHas('autoridad_firmantes', [
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => true,
            'gaceta' => 'Resolución N° 123',
            'desde' => '2026-09-01',
        ]);
    }

    public function test_solapamiento_bloquea_registro_mismo_cargo_fechas_solapadas(): void
    {
        // Existe director desde 2026-09-01 sin fecha de fin
        AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        // Intento de registrar otro director en el mismo periodo
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.autoridad-firmante.store'), [
                'cargo' => 'director',
                'persona_id' => $this->persona2->id,
                'desde' => '2026-10-01',
                'hasta' => '2027-01-31',
            ]);

        $response->assertSessionHasErrors('cargo');
        $this->assertEquals(1, AutoridadFirmante::where('cargo', 'director')->count());
    }

    public function test_solapamiento_permite_cargos_diferentes_mismas_fechas(): void
    {
        AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        // Subdirector puede registrarse para las mismas fechas
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.autoridad-firmante.store'), [
                'cargo' => 'subdirector_academico',
                'persona_id' => $this->persona2->id,
                'desde' => '2026-09-01',
                'hasta' => null,
            ]);

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertDatabaseHas('autoridad_firmantes', [
            'cargo' => 'subdirector_academico',
            'persona_id' => $this->persona2->id,
        ]);
    }

    public function test_solapamiento_permite_multiples_docentes_firmantes(): void
    {
        AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'docente_firmante_mppe',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        // Puede haber otro docente firmante en las mismas fechas
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.autoridad-firmante.store'), [
                'cargo' => 'docente_firmante_mppe',
                'persona_id' => $this->persona2->id,
                'desde' => '2026-09-01',
                'hasta' => null,
            ]);

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertEquals(2, AutoridadFirmante::where('cargo', 'docente_firmante_mppe')->count());
    }

    public function test_solapamiento_permite_mismo_cargo_fechas_no_solapadas(): void
    {
        AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'desde' => '2026-09-01',
            'hasta' => '2026-12-31',
        ]);

        // Nuevo director a partir del 2027-01-01 (no se solapan)
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.autoridad-firmante.store'), [
                'cargo' => 'director',
                'persona_id' => $this->persona2->id,
                'desde' => '2027-01-01',
                'hasta' => '2027-07-31',
            ]);

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertEquals(2, AutoridadFirmante::where('cargo', 'director')->count());
    }

    public function test_admin_puede_actualizar_autoridad_firmante_sin_error_de_solapamiento_propio(): void
    {
        $autoridad = AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'gaceta' => 'Gaceta 1',
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->put(route('admin.autoridad-firmante.update', $autoridad), [
                'cargo' => 'director',
                'persona_id' => $this->persona1->id,
                'encargado' => false,
                'gaceta' => 'Gaceta Actualizada',
                'desde' => '2026-09-01',
                'hasta' => null,
            ]);

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertDatabaseHas('autoridad_firmantes', [
            'id' => $autoridad->id,
            'gaceta' => 'Gaceta Actualizada',
        ]);
    }

    public function test_admin_puede_eliminar_autoridad_firmante(): void
    {
        $autoridad = AutoridadFirmante::create([
            'anio_escolar_id' => $this->anioActivo->id,
            'cargo' => 'director',
            'persona_id' => $this->persona1->id,
            'encargado' => false,
            'desde' => '2026-09-01',
            'hasta' => null,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->delete(route('admin.autoridad-firmante.destroy', $autoridad));

        $response->assertRedirect(route('admin.autoridad-firmante.index'));
        $this->assertDatabaseMissing('autoridad_firmantes', ['id' => $autoridad->id]);
    }
}

