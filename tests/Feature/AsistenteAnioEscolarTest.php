<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\CalendarioAcademico;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Lapso;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// Asistente de año nuevo: año escolar → lapso 1 → calendario. Nada se guarda hasta el último paso.
class AsistenteAnioEscolarTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->admin = $this->usuario('Admin');
    }

    private function usuario(string $rol): User
    {
        $u = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => $rol,
        ]);
        $u->assignRole($rol);

        return $u;
    }

    private array $anio = [
        'inicio_anio_escolar' => '2026-09-15',
        'cierre_anio_escolar' => '2027-07-31',
    ];

    private array $lapso = [
        'nombre' => 'Lapso 1',
        'fecha_inicio' => '2026-09-15',
        'fecha_fin' => '2026-12-15',
        'fecha_limite_carga' => '2026-12-20',
    ];

    public function test_seleccionar_anio_lleva_al_asistente(): void
    {
        $this->actingAs($this->admin)->get(route('admin.anio-escolar.seleccionar'))
            ->assertOk()
            ->assertSee(route('admin.asistente.anio'));
    }

    public function test_los_pasos_1_y_2_no_guardan_nada(): void
    {
        $this->actingAs($this->admin)->get(route('admin.asistente.anio'))->assertOk();

        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio)
            ->assertRedirect(route('admin.asistente.lapso'));

        // El lapso 1 propuesto: el primer tercio del año
        $this->actingAs($this->admin)->get(route('admin.asistente.lapso'))
            ->assertOk()->assertSee('2026-09-15')->assertSee('4 cortes de 20 puntos');

        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), $this->lapso)
            ->assertRedirect(route('admin.asistente.calendario'));

        $this->actingAs($this->admin)->get(route('admin.asistente.calendario'))
            ->assertOk()->assertSee('15/12/2026');

        $this->assertSame(0, AnioEscolar::count());
        $this->assertSame(0, Lapso::count());
        $this->assertSame(0, CalendarioAcademico::count());
    }

    public function test_atras_conserva_lo_escrito(): void
    {
        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio);
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), ['nombre' => 'Primer lapso'] + $this->lapso);

        $this->actingAs($this->admin)->get(route('admin.asistente.anio'))
            ->assertOk()->assertSee('value="2026-09-15"', false)->assertSee('value="2027-07-31"', false);
        $this->actingAs($this->admin)->get(route('admin.asistente.lapso'))
            ->assertOk()->assertSee('value="Primer lapso"', false);
    }

    public function test_guardar_crea_todo_junto(): void
    {
        $viejo = AnioEscolar::create(['inicio_anio_escolar' => '2025-09-15', 'cierre_anio_escolar' => '2026-07-31', 'status' => 'Activo']);
        ConfiguracionEvaluacion::create([
            'anio_escolar_id' => $viejo->id, 'nota_minima' => 1, 'nota_maxima' => 20, 'nota_aprobatoria' => 12,
            'regla_redondeo' => 'half_up', 'max_lapsos' => 3, 'momentos_quedada' => 4, 'max_areas_pendientes' => 2,
        ]);

        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio);
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), $this->lapso);
        $respuesta = $this->actingAs($this->admin)->post(route('admin.asistente.guardar'));

        $nuevo = AnioEscolar::whereDate('inicio_anio_escolar', '2026-09-15')->first();
        $this->assertNotNull($nuevo);
        $this->assertSame('Activo', $nuevo->status);
        $this->assertSame('Inactivo', $viejo->fresh()->status);

        // La configuración se copió del año anterior (aprueba con 12)
        $this->assertSame(12, ConfiguracionEvaluacion::where('anio_escolar_id', $nuevo->id)->value('nota_aprobatoria'));

        // Un solo lapso, con 4 cortes de 20, enlazado al calendario
        $calendario = CalendarioAcademico::where('anio_escolar_id', $nuevo->id)->first();
        $lapsos = Lapso::where('anio_escolar_id', $nuevo->id)->get();
        $this->assertCount(1, $lapsos);
        $this->assertSame($calendario->id, $lapsos->first()->calendario_academico_id);
        $this->assertSame(4, $lapsos->first()->cortes()->count());
        $this->assertSame(80.0, $lapsos->first()->puntosTotales());

        $respuesta->assertRedirect(route('admin.calendario_academico.show', $calendario));
        $this->assertSame($nuevo->id, session('anio_escolar_id'));
        $this->assertNull(session('asistente_anio'));
    }

    public function test_no_se_salta_pasos(): void
    {
        $this->actingAs($this->admin)->get(route('admin.asistente.lapso'))->assertRedirect(route('admin.asistente.anio'));
        $this->actingAs($this->admin)->get(route('admin.asistente.calendario'))->assertRedirect(route('admin.asistente.anio'));
        $this->actingAs($this->admin)->post(route('admin.asistente.guardar'))->assertRedirect(route('admin.asistente.anio'));

        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio);
        $this->actingAs($this->admin)->get(route('admin.asistente.calendario'))->assertRedirect(route('admin.asistente.lapso'));

        $this->assertSame(0, AnioEscolar::count());
    }

    public function test_validaciones(): void
    {
        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), [
            'inicio_anio_escolar' => '2026-09-15', 'cierre_anio_escolar' => '2026-09-01',
        ])->assertSessionHasErrors('cierre_anio_escolar');

        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio);
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), ['fecha_fin' => '2027-08-30'] + $this->lapso)
            ->assertSessionHasErrors('fecha_fin');
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), ['fecha_limite_carga' => '2026-12-01'] + $this->lapso)
            ->assertSessionHasErrors('fecha_limite_carga');
    }

    public function test_si_cambian_las_fechas_del_anio_y_el_lapso_no_cabe_se_vuelve_a_proponer(): void
    {
        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), $this->anio);
        $this->actingAs($this->admin)->post(route('admin.asistente.lapso.store'), $this->lapso);

        // El año ahora empieza en octubre: el lapso guardado (desde septiembre) ya no cabe
        $this->actingAs($this->admin)->post(route('admin.asistente.anio.store'), ['inicio_anio_escolar' => '2026-10-01'] + $this->anio);

        $this->actingAs($this->admin)->get(route('admin.asistente.calendario'))->assertRedirect(route('admin.asistente.lapso'));
        $this->actingAs($this->admin)->get(route('admin.asistente.lapso'))->assertSee('value="2026-10-01"', false);
    }

    public function test_funciona_aunque_el_anio_seleccionado_sea_uno_viejo(): void
    {
        $viejo = AnioEscolar::create(['inicio_anio_escolar' => '2024-09-15', 'cierre_anio_escolar' => '2025-07-31', 'status' => 'Inactivo']);

        $this->actingAs($this->admin)->withSession(['anio_escolar_id' => $viejo->id])
            ->post(route('admin.asistente.anio.store'), $this->anio)
            ->assertRedirect(route('admin.asistente.lapso'));
    }

    public function test_sin_permiso_no_entra(): void
    {
        $this->actingAs($this->usuario('Representante'))->get(route('admin.asistente.anio'))->assertForbidden();
    }
}
