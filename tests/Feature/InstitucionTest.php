<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\Institucion;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstitucionTest extends TestCase
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

    public function test_admin_puede_ver_listado_instituciones(): void
    {
        Institucion::create([
            'codigo_plantel' => 'TEST01',
            'nombre' => 'Liceo de Prueba',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.institucion.index'));

        $response->assertOk();
        $response->assertSee('Liceo de Prueba');
        $response->assertSee('TEST01');
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_instituciones(): void
    {
        $response = $this->actingAs($this->userSinPermiso)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.institucion.index'));

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_institucion(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.institucion.store'), [
                'codigo_plantel' => 'OD12345',
                'nombre' => 'Nuevo Liceo Experimental',
                'direccion' => 'Calle 10 con Carrera 5',
                'telefono' => '0255-1234567',
                'zona_educativa' => 'Portuguesa',
                'cdcee' => 'CDCEE Araure',
                'dependencia' => 'Pública',
                'activo' => '1',
            ]);

        $response->assertRedirect(route('admin.institucion.index'));
        $this->assertDatabaseHas('instituciones', [
            'codigo_plantel' => 'OD12345',
            'nombre' => 'Nuevo Liceo Experimental',
            'activo' => 1,
        ]);
    }

    public function test_regla_de_negocio_solo_una_institucion_activa(): void
    {
        $institucion1 = Institucion::create([
            'codigo_plantel' => 'INST01',
            'nombre' => 'Plantel Inicial',
            'activo' => true,
        ]);

        // Registrar una segunda institución como activa
        $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.institucion.store'), [
                'codigo_plantel' => 'INST02',
                'nombre' => 'Plantel Secundario',
                'activo' => '1',
            ]);

        // La anterior debe quedar inactiva
        $institucion1->refresh();
        $this->assertFalse($institucion1->activo);

        // La nueva debe ser la única activa
        $this->assertDatabaseHas('instituciones', [
            'codigo_plantel' => 'INST02',
            'activo' => 1,
        ]);
        $this->assertEquals(1, Institucion::where('activo', true)->count());
    }

    public function test_admin_puede_actualizar_institucion(): void
    {
        $institucion = Institucion::create([
            'codigo_plantel' => 'INST03',
            'nombre' => 'Nombre Viejo',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->put(route('admin.institucion.update', $institucion), [
                'codigo_plantel' => 'INST03',
                'nombre' => 'Nombre Actualizado',
                'telefono' => '0414-9998877',
                'activo' => '1',
            ]);

        $response->assertRedirect(route('admin.institucion.index'));
        $this->assertDatabaseHas('instituciones', [
            'id' => $institucion->id,
            'nombre' => 'Nombre Actualizado',
            'telefono' => '0414-9998877',
        ]);
    }

    public function test_eliminar_desactiva_institucion(): void
    {
        $institucion = Institucion::create([
            'codigo_plantel' => 'INST04',
            'nombre' => 'Plantel a Desactivar',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->delete(route('admin.institucion.destroy', $institucion));

        $response->assertRedirect(route('admin.institucion.index'));
        $institucion->refresh();
        $this->assertFalse($institucion->activo);
    }
}

