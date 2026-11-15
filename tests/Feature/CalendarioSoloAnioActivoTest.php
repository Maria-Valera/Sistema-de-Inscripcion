<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// El calendario académico solo se crea para el año activo, y cada año tiene uno solo.
class CalendarioSoloAnioActivoTest extends TestCase
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

    public function test_se_crea_para_el_anio_activo_aunque_manden_otro(): void
    {
        $viejo = $this->anio('2024-09-15', '2025-07-31', 'Inactivo');
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');

        $this->actingAs($this->admin)->get(route('admin.calendario_academico.index'))
            ->assertOk()->assertSee('Crear nuevo calendario');
        $this->actingAs($this->admin)->get(route('admin.calendario_academico.create_manual'))
            ->assertOk()->assertSee('15/09/2026');

        // Aunque alguien mande el id del año viejo, se usa el activo
        $this->actingAs($this->admin)
            ->post(route('admin.calendario_academico.store_manual'), ['anio_escolar_id' => $viejo->id])
            ->assertRedirect();

        $this->assertTrue(CalendarioAcademico::where('anio_escolar_id', $activo->id)->exists());
        $this->assertFalse(CalendarioAcademico::where('anio_escolar_id', $viejo->id)->exists());
    }

    public function test_si_el_anio_activo_ya_tiene_calendario_no_se_crea_otro(): void
    {
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');
        CalendarioAcademico::create(['anio_escolar_id' => $activo->id, 'estado' => 'confirmado', 'fecha_confirmacion' => now()]);

        $this->actingAs($this->admin)->get(route('admin.calendario_academico.index'))
            ->assertOk()->assertDontSee('Crear nuevo calendario');
        $this->actingAs($this->admin)->get(route('admin.calendario_academico.create_manual'))
            ->assertRedirect(route('admin.calendario_academico.index'))
            ->assertSessionHas('error');
        $this->actingAs($this->admin)->post(route('admin.calendario_academico.store_manual'))
            ->assertSessionHas('error');

        $this->assertSame(1, CalendarioAcademico::count());
    }

    public function test_sin_anio_activo_no_se_crea_calendario(): void
    {
        $this->anio('2024-09-15', '2025-07-31', 'Inactivo');

        $this->actingAs($this->admin)->post(route('admin.calendario_academico.store_manual'))
            ->assertSessionHas('error');

        $this->assertSame(0, CalendarioAcademico::count());
    }

    public function test_editar_solo_cambia_el_estado_no_el_anio(): void
    {
        $viejo = $this->anio('2024-09-15', '2025-07-31', 'Inactivo');
        $activo = $this->anio('2026-09-15', '2027-07-31', 'Activo');
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $activo->id, 'estado' => 'pendiente_revision']);

        $this->actingAs($this->admin)
            ->put(route('admin.calendario_academico.update', $calendario), [
                'anio_escolar_id' => $viejo->id,
                'estado' => 'confirmado',
            ])
            ->assertSessionHasNoErrors();

        $calendario->refresh();
        $this->assertSame($activo->id, $calendario->anio_escolar_id);
        $this->assertSame('confirmado', $calendario->estado->value);
    }
}
