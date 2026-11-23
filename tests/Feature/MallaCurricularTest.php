<?php

namespace Tests\Feature;

use App\Models\AreaFormacion;
use App\Models\Grado;
use App\Models\GradoAreaFormacion;
use App\Models\Pensum;
use App\Models\User;
use Database\Seeders\AreaFormacionSeeder;
use Database\Seeders\AulaSeeder;
use Database\Seeders\GradoSeeder;
use Database\Seeders\JoseMaestrosSeeder;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MallaCurricularTest extends TestCase
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

    public function test_admin_puede_ver_malla_curricular(): void
    {
        $this->seed(GradoSeeder::class);
        $this->seed(AulaSeeder::class);
        $this->seed(AreaFormacionSeeder::class);
        $this->seed(JoseMaestrosSeeder::class);

        $pensum31059 = Pensum::where('codigo', '31059')->firstOrFail();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.malla-curricular.index', [
                'pensum_id' => $pensum31059->id,
                'grado_id'  => 1,
            ]));

        $response->assertStatus(200);
        $response->assertSee('Malla Curricular');
        $response->assertSee('Castellano');
    }

    public function test_admin_puede_agregar_asignatura_a_la_malla(): void
    {
        $this->seed(GradoSeeder::class);
        $this->seed(AulaSeeder::class);
        $this->seed(AreaFormacionSeeder::class);

        $pensum = Pensum::create([
            'codigo'      => '31059',
            'nombre'      => 'Educación Media General',
            'anio_inicio' => 2017,
            'status'      => true,
        ]);

        $area = AreaFormacion::first();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.malla-curricular.store'), [
                'pensum_id'         => $pensum->id,
                'grado_id'          => 1,
                'area_formacion_id' => $area->id,
                'horas'             => 4,
                'nota_aprobatoria'  => 10.00,
                'escala'            => 'numerica',
                'componente'        => 'Formación General',
                'orden'             => 1,
                'status'            => '1',
            ]);

        $response->assertRedirect(route('admin.malla-curricular.index', [
            'pensum_id' => $pensum->id,
            'grado_id'  => 1,
        ]));

        $this->assertDatabaseHas('grado_area_formacions', [
            'pensum_id'         => $pensum->id,
            'grado_id'          => 1,
            'area_formacion_id' => $area->id,
            'escala'            => 'numerica',
        ]);
    }

    public function test_admin_puede_actualizar_asignatura_en_la_malla(): void
    {
        $this->seed(GradoSeeder::class);
        $this->seed(AulaSeeder::class);
        $this->seed(AreaFormacionSeeder::class);

        $pensum = Pensum::create([
            'codigo'      => '31059',
            'nombre'      => 'Educación Media General',
            'anio_inicio' => 2017,
            'status'      => true,
        ]);

        $area = AreaFormacion::first();

        $malla = GradoAreaFormacion::create([
            'pensum_id'         => $pensum->id,
            'grado_id'          => 1,
            'area_formacion_id' => $area->id,
            'horas'             => 4,
            'nota_aprobatoria'  => 10.00,
            'escala'            => 'numerica',
            'componente'        => 'Formación General',
            'orden'             => 1,
            'status'            => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.malla-curricular.update', $malla), [
                'pensum_id'         => $pensum->id,
                'grado_id'          => 1,
                'area_formacion_id' => $area->id,
                'horas'             => 6,
                'nota_aprobatoria'  => 10.00,
                'escala'            => 'numerica',
                'componente'        => 'Formación General',
                'orden'             => 2,
                'status'            => '1',
            ]);

        $response->assertRedirect(route('admin.malla-curricular.index', [
            'pensum_id' => $pensum->id,
            'grado_id'  => 1,
        ]));

        $this->assertDatabaseHas('grado_area_formacions', [
            'id'     => $malla->id,
            'horas'  => 6,
            'escala' => 'numerica',
            'orden'  => 2,
        ]);
    }
}
