<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\GrupoEstable;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GrupoEstableCodigoTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
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

        $this->anioActivo = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(2)->toDateString(),
            'cierre_anio_escolar' => now()->addMonths(10)->toDateString(),
            'status' => 'Activo',
        ]);
    }

    public function test_admin_puede_crear_grupo_estable_con_codigo(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.area_formacion.modalesGrupoEstable.storeGrupoEstable'), [
                'codigo' => 'AP 041',
                'nombre_grupo_estable' => 'Ajedrez Avanzado',
            ]);

        $response->assertRedirect(route('admin.area_formacion.index'));
        $this->assertDatabaseHas('grupo_estables', [
            'codigo' => 'AP 041',
            'nombre_grupo_estable' => 'Ajedrez Avanzado',
            'status' => 1,
        ]);
    }

    public function test_admin_puede_actualizar_codigo_de_grupo_estable(): void
    {
        $grupo = GrupoEstable::create([
            'codigo' => 'AP 001',
            'nombre_grupo_estable' => 'Robótica Inicial',
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post(route('admin.area_formacion.modalesGrupoEstable.updateGrupoEstable', $grupo->id), [
                'id' => $grupo->id,
                'codigo' => 'AP 099',
                'nombre_grupo_estable' => 'Robótica Avanzada',
            ]);

        $response->assertRedirect(route('admin.area_formacion.index'));
        $this->assertDatabaseHas('grupo_estables', [
            'id' => $grupo->id,
            'codigo' => 'AP 099',
            'nombre_grupo_estable' => 'Robótica Avanzada',
        ]);
    }

    public function test_codigo_se_muestra_en_el_listado_de_area_formacion(): void
    {
        GrupoEstable::create([
            'codigo' => 'GE-TEST',
            'nombre_grupo_estable' => 'Teatro Estudiantil',
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->get(route('admin.area_formacion.index'));

        $response->assertOk();
        $response->assertSee('GE-TEST');
        $response->assertSee('Teatro Estudiantil');
    }
}

