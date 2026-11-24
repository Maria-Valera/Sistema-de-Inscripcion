<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\ConfiguracionEvaluacion;
use App\Models\Corte;
use App\Models\Lapso;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

// La cantidad de lapsos y cortes ya no está en la configuración: se cuenta en los maestros.
class EstructuraLapsosCortesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected AnioEscolar $anio;

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

        $this->anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-31',
            'status' => 'Activo',
        ]);

        ConfiguracionEvaluacion::create([
            'anio_escolar_id' => $this->anio->id,
            'nota_minima' => 1,
            'nota_maxima' => 20,
            'nota_aprobatoria' => 10,
            'regla_redondeo' => 'half_up',
            'max_lapsos' => 3,
            'momentos_quedada' => 4,
            'max_areas_pendientes' => 2,
        ]);
    }

    private function lapso(int $numero, string $inicio, string $fin, string $estado = 'planificado'): Lapso
    {
        return Lapso::create([
            'anio_escolar_id' => $this->anio->id,
            'numero' => $numero,
            'nombre' => "Lapso {$numero}",
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'fecha_limite_carga' => $fin . ' 23:59:59',
            'estado' => $estado,
        ]);
    }

    private function corte(Lapso $lapso, int $numero, float $peso = 20): Corte
    {
        return $lapso->cortes()->create([
            'numero' => $numero,
            'nombre' => "Corte {$numero}",
            'peso_lapso' => $peso,
            'fecha_limite_carga' => $lapso->fecha_fin->copy()->endOfDay(),
            'estado' => 'planificado',
        ]);
    }

    private function datosCorte(int $numero, $peso = 20): array
    {
        return [
            'numero' => $numero,
            'nombre' => "Corte {$numero}",
            'peso_lapso' => $peso,
            'fecha_limite_carga' => '2026-12-20',
            'estado' => 'planificado',
        ];
    }

    public function test_las_pantallas_cargan(): void
    {
        $lapso = $this->lapso(1, '2026-09-15', '2026-12-15');
        $this->corte($lapso, 1);

        $this->actingAs($this->admin)->get(route('admin.configuracion_evaluacion.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.lapsos.index'))->assertOk()->assertSee('20 pts');
        $this->actingAs($this->admin)->get(route('admin.cortes.index', $lapso))->assertOk()->assertSee('el lapso vale 20 puntos');
    }

    public function test_la_configuracion_ya_no_guarda_cantidades_de_lapsos_ni_cortes(): void
    {
        $this->assertFalse(Schema::hasColumn('configuracion_evaluacions', 'num_lapsos'));
        $this->assertFalse(Schema::hasColumn('configuracion_evaluacions', 'num_cortes'));
        $this->assertFalse(Schema::hasColumn('configuracion_evaluacions', 'puntos_corte'));

        $config = ConfiguracionEvaluacion::first();
        $this->actingAs($this->admin)
            ->put(route('admin.configuracion_evaluacion.update', $config), [
                'anio_escolar_id' => $this->anio->id,
                'nota_minima' => 1,
                'nota_maxima' => 20,
                'nota_aprobatoria' => 10,
                'regla_redondeo' => 'half_up',
                'max_lapsos' => 3,
                'momentos_quedada' => 4,
                'max_areas_pendientes' => 3,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $config->fresh()->max_areas_pendientes);
    }

    private function datosLapso(int $numero): array
    {
        return [
            'numero' => $numero,
            'nombre' => "Lapso {$numero}",
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-10-15',
            'fecha_limite_carga' => '2026-10-20',
            'estado' => 'planificado',
        ];
    }

    public function test_un_anio_tiene_como_maximo_3_lapsos(): void
    {
        $this->actingAs($this->admin)->post(route('admin.lapsos.store'), $this->datosLapso(4))
            ->assertSessionHasErrors('numero');
    }

    public function test_el_maximo_de_lapsos_se_cambia_en_la_configuracion(): void
    {
        ConfiguracionEvaluacion::first()->update(['max_lapsos' => 4]);

        $this->actingAs($this->admin)->post(route('admin.lapsos.store'), $this->datosLapso(4))
            ->assertSessionHasNoErrors();
    }

    public function test_el_maximo_no_baja_de_los_lapsos_que_ya_existen(): void
    {
        $this->lapso(1, '2026-09-15', '2026-12-15');
        $this->lapso(2, '2027-01-07', '2027-04-01');
        $config = ConfiguracionEvaluacion::first();

        $this->actingAs($this->admin)
            ->put(route('admin.configuracion_evaluacion.update', $config), [
                'anio_escolar_id' => $this->anio->id,
                'nota_minima' => 1,
                'nota_maxima' => 20,
                'nota_aprobatoria' => 10,
                'regla_redondeo' => 'half_up',
                'max_lapsos' => 1,
                'momentos_quedada' => 4,
                'max_areas_pendientes' => 2,
            ])
            ->assertSessionHas('error');

        $this->assertSame(3, $config->fresh()->max_lapsos);
    }

    public function test_no_se_abre_un_lapso_sin_cortes(): void
    {
        $lapso = $this->lapso(1, '2026-09-15', '2026-12-15');
        $datos = [
            'numero' => 1,
            'nombre' => 'Lapso 1',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-12-15',
            'fecha_limite_carga' => '2026-12-20',
            'estado' => 'abierto',
        ];

        $this->actingAs($this->admin)->put(route('admin.lapsos.update', $lapso), $datos)
            ->assertSessionHasErrors('estado');

        $this->corte($lapso, 1);
        $this->actingAs($this->admin)->put(route('admin.lapsos.update', $lapso), $datos)
            ->assertSessionHasNoErrors();
    }

    public function test_el_total_del_lapso_es_la_suma_de_sus_cortes(): void
    {
        $lapso = $this->lapso(1, '2026-09-15', '2026-12-15');
        foreach ([1, 2, 3, 4] as $n) {
            $this->corte($lapso, $n);
        }

        $this->assertSame(80.0, $lapso->puntosTotales());
    }

    public function test_un_corte_no_vale_mas_que_la_nota_maxima(): void
    {
        $lapso = $this->lapso(1, '2026-09-15', '2026-12-15');

        $this->actingAs($this->admin)->post(route('admin.cortes.store', $lapso), $this->datosCorte(1, 25))
            ->assertSessionHasErrors('peso_lapso');
        $this->actingAs($this->admin)->post(route('admin.cortes.store', $lapso), $this->datosCorte(1, 20))
            ->assertSessionHasNoErrors();
    }

    public function test_con_el_lapso_abierto_no_se_agregan_ni_se_borran_cortes(): void
    {
        $lapso = $this->lapso(1, '2026-09-15', '2026-12-15', 'abierto');
        $corte = $this->corte($lapso, 1);

        $this->actingAs($this->admin)->post(route('admin.cortes.store', $lapso), $this->datosCorte(2))
            ->assertSessionHasErrors('numero');

        $this->actingAs($this->admin)->delete(route('admin.cortes.destroy', [$lapso, $corte]))
            ->assertSessionHas('error');
        $this->assertNotNull($corte->fresh());
    }
}
