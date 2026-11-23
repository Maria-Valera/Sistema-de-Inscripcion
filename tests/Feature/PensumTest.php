<?php

namespace Tests\Feature;

use App\Models\Pensum;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PensumTest extends TestCase
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

    public function test_admin_puede_ver_listado_de_pensums(): void
    {
        Pensum::create([
            'codigo'      => '31059',
            'nombre'      => 'Educación Media General',
            'anio_inicio' => 2017,
            'status'      => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.pensums.index'));

        $response->assertStatus(200);
        $response->assertSee('31059');
        $response->assertSee('Educación Media General');
    }

    public function test_usuario_sin_permiso_no_puede_ver_pensums(): void
    {
        $response = $this->actingAs($this->userSinPermiso)
            ->get(route('admin.pensums.index'));

        $response->assertStatus(403);
    }

    public function test_admin_puede_crear_un_pensum(): void
    {
        $data = [
            'codigo'      => '31060',
            'nombre'      => 'Ciencia y Tecnología',
            'anio_inicio' => 2023,
            'status'      => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.pensums.store'), $data);

        $response->assertRedirect(route('admin.pensums.index'));
        $this->assertDatabaseHas('pensums', [
            'codigo' => '31060',
            'nombre' => 'Ciencia y Tecnología',
        ]);
    }

    public function test_codigo_de_pensum_debe_ser_unico(): void
    {
        Pensum::create([
            'codigo'      => '31059',
            'nombre'      => 'Existente',
            'anio_inicio' => 2017,
            'status'      => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.pensums.store'), [
                'codigo'      => '31059',
                'nombre'      => 'Duplicado',
                'anio_inicio' => 2024,
                'status'      => '1',
            ]);

        $response->assertSessionHasErrors(['codigo']);
    }

    public function test_admin_puede_actualizar_un_pensum(): void
    {
        $pensum = Pensum::create([
            'codigo'      => '31059',
            'nombre'      => 'Nombre Original',
            'anio_inicio' => 2017,
            'status'      => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.pensums.update', $pensum), [
                'codigo'      => '31059',
                'nombre'      => 'Nombre Modificado',
                'anio_inicio' => 2017,
                'status'      => '1',
            ]);

        $response->assertRedirect(route('admin.pensums.index'));
        $this->assertDatabaseHas('pensums', [
            'id'     => $pensum->id,
            'nombre' => 'Nombre Modificado',
        ]);
    }
}
