<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConfiguracionHorarioControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected AnioEscolar $anio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->anio = AnioEscolar::create([
            'inicio_anio_escolar' => now()->toDateString(),
            'cierre_anio_escolar' => now()->addYear()->toDateString(),
            'status' => 'Activo',
        ]);

        $permiso = Permission::firstOrCreate(['name' => 'horarios.gestionar', 'guard_name' => 'web']);
        $role = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $role->givePermissionTo($permiso);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
        ]);
        $this->user->assignRole($role);
    }

    public function test_usuario_con_permiso_puede_ver_pantalla_de_configuracion(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.horario.configuracion'));

        $response->assertStatus(200);
        $response->assertSee('Configuración de Bloques Horarios');
        $response->assertSee('Hora de Inicio de Jornada');
        $response->assertSee('Vista Previa en Vivo');
    }

    public function test_usuario_puede_actualizar_configuracion_y_recesos(): void
    {
        $payload = [
            'hora_inicio' => '07:30',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 5,
            'recesos' => [
                ['despues_de_bloque' => 2, 'duracion_min' => 20],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('admin.horario.configuracion.update'), $payload);

        $response->assertRedirect(route('admin.horario.configuracion'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('configuracion_horario', [
            'anio_escolar_id' => $this->anio->id,
            'hora_inicio' => '07:30:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 5,
        ]);

        $this->assertDatabaseHas('receso_horario', [
            'despues_de_bloque' => 2,
            'duracion_min' => 20,
        ]);
    }
}
