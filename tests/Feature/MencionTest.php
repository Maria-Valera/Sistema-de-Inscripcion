<?php

namespace Tests\Feature;

use App\Models\Grado;
use App\Models\Mencion;
use App\Models\Pensum;
use App\Models\Seccion;
use App\Models\User;
use Database\Seeders\GradoSeeder;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MencionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $userSinPermiso;

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
    }

    public function test_admin_puede_ver_listado_menciones(): void
    {
        Mencion::create([
            'codigo' => '01',
            'nombre' => 'Educación Media General',
            'ciclo'  => '50',
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.menciones.index'));

        $response->assertStatus(200);
        $response->assertSee('Educación Media General');
        $response->assertSee('01');
    }

    public function test_admin_puede_crear_mencion(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.menciones.store'), [
                'codigo' => '02',
                'nombre' => 'Ciencia y Tecnología',
                'ciclo'  => '50',
                'status' => '1',
            ]);

        $response->assertRedirect(route('admin.menciones.index'));
        $this->assertDatabaseHas('menciones', [
            'codigo' => '02',
            'nombre' => 'Ciencia y Tecnología',
        ]);
    }

    public function test_admin_puede_actualizar_mencion(): void
    {
        $mencion = Mencion::create([
            'codigo' => '01',
            'nombre' => 'Nombre Inicial',
            'ciclo'  => '50',
            'status' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.menciones.update', $mencion), [
                'codigo' => '01',
                'nombre' => 'Nombre Actualizado',
                'ciclo'  => '51',
                'status' => '1',
            ]);

        $response->assertRedirect(route('admin.menciones.index'));
        $this->assertDatabaseHas('menciones', [
            'id'     => $mencion->id,
            'nombre' => 'Nombre Actualizado',
            'ciclo'  => '51',
        ]);
    }

    public function test_asignacion_de_mencion_y_pensum_a_seccion(): void
    {
        $this->seed(GradoSeeder::class);

        $pensum = Pensum::create([
            'codigo'      => '31060',
            'nombre'      => 'Ciencia y Tecnología',
            'anio_inicio' => 2023,
            'status'      => true,
        ]);

        $mencion = Mencion::create([
            'codigo' => '02',
            'nombre' => 'Ciencia y Tecnología',
            'ciclo'  => '50',
            'status' => true,
        ]);

        $seccion = Seccion::create([
            'nombre'          => 'A',
            'grado_id'        => 1,
            'cantidad_actual' => 0,
            'status'          => true,
        ]);

        $this->assertEquals('*****', $seccion->nombre_mencion_boletin);
        $this->assertEquals('50', $seccion->ciclo_boletin);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.secciones.asignar-mencion', $seccion), [
                'pensum_id'  => $pensum->id,
                'mencion_id' => $mencion->id,
            ]);

        $response->assertSessionHasNoErrors();
        $seccion->refresh();

        $this->assertEquals($pensum->id, $seccion->pensum_id);
        $this->assertEquals($mencion->id, $seccion->mencion_id);
        $this->assertEquals('Ciencia y Tecnología', $seccion->nombre_mencion_boletin);
    }
}
