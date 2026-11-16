<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\ConfiguracionEvaluacion;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// La configuración de evaluación solo se crea para el año activo, y cada año tiene una sola.
class ConfiguracionSoloAnioActivoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Admin',
        ]);
        $this->admin->assignRole('Admin');
    }

    private function anio(string $inicio, string $cierre, string $status): AnioEscolar
    {
        return AnioEscolar::create([
            'inicio_anio_escolar' => $inicio,
            'cierre_anio_escolar' => $cierre,
            'status' => $status,
        ]);
    }

    private function reglas(array $extra = []): array
    {
        return $extra + [
            'nota_minima' => 1,
            'nota_maxima' => 20,
            'nota_aprobatoria' => 10,
            'regla_redondeo' => 'half_up',
            'max_lapsos' => 3,
            'momentos_quedada' => 4,
            'max_areas_pendientes' => 2,
        ];
    }

    public function test_se_crea_para_el_anio_activo_aunque_manden_otro(): void
    {
        $viejo = $this->anio('2024-09-15', '2025-07-31', 'Inactivo');
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');

        $this->actingAs($this->admin)->get(route('admin.configuracion_evaluacion.index'))
            ->assertOk()->assertSee('Nueva configuración')->assertSee('2026 - 2027 (año activo)');

        // Aunque alguien mande el id del año viejo, se usa el activo
        $this->actingAs($this->admin)
            ->post(route('admin.configuracion_evaluacion.store'), $this->reglas(['anio_escolar_id' => $viejo->id]))
            ->assertSessionHasNoErrors();

        $this->assertTrue(ConfiguracionEvaluacion::where('anio_escolar_id', $activo->id)->exists());
        $this->assertFalse(ConfiguracionEvaluacion::where('anio_escolar_id', $viejo->id)->exists());
    }

    public function test_si_el_anio_activo_ya_tiene_configuracion_no_se_crea_otra(): void
    {
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');
        ConfiguracionEvaluacion::create($this->reglas(['anio_escolar_id' => $activo->id]));

        $this->actingAs($this->admin)->get(route('admin.configuracion_evaluacion.index'))
            ->assertOk()->assertDontSee('Nueva configuración');

        $this->actingAs($this->admin)->post(route('admin.configuracion_evaluacion.store'), $this->reglas())
            ->assertSessionHasErrors('anio_escolar_id');

        $this->assertSame(1, ConfiguracionEvaluacion::count());
    }

    public function test_sin_anio_activo_no_se_crea(): void
    {
        $this->anio('2024-09-15', '2025-07-31', 'Inactivo');

        $this->actingAs($this->admin)->post(route('admin.configuracion_evaluacion.store'), $this->reglas())
            ->assertSessionHasErrors('anio_escolar_id');

        $this->assertSame(0, ConfiguracionEvaluacion::count());
    }

    public function test_editar_no_cambia_el_anio(): void
    {
        $viejo = $this->anio('2024-09-15', '2025-07-31', 'Inactivo');
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');
        $config = ConfiguracionEvaluacion::create($this->reglas(['anio_escolar_id' => $activo->id]));

        $this->actingAs($this->admin)
            ->put(route('admin.configuracion_evaluacion.update', $config), $this->reglas(['anio_escolar_id' => $viejo->id, 'nota_aprobatoria' => 12]))
            ->assertSessionHasNoErrors();

        $config->refresh();
        $this->assertSame($activo->id, $config->anio_escolar_id);
        $this->assertSame(12, $config->nota_aprobatoria);
    }

    public function test_el_asistente_de_anio_nuevo_sigue_creando_la_configuracion(): void
    {
        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), [
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-31',
        ]);
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), [
            'nombre' => 'Lapso 1',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-12-15',
            'fecha_limite_carga' => '2026-12-20',
        ]);
        $this->actingAs($this->admin)->post(route('admin.asistente.guardar'));

        $anio = AnioEscolar::where('status', 'Activo')->first();
        $this->assertSame(1, ConfiguracionEvaluacion::where('anio_escolar_id', $anio->id)->count());

        // Y como ya la tiene, el CRUD ya no deja crear otra
        $this->actingAs($this->admin)->get(route('admin.configuracion_evaluacion.index'))
            ->assertOk()->assertDontSee('Nueva configuración');
    }
}
