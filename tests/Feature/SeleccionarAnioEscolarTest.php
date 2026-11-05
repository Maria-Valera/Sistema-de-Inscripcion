<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\User;
use App\Support\AnioEscolarSeleccionado;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SeleccionarAnioEscolarTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected AnioEscolar $anioActivo;
    protected AnioEscolar $anioAnterior;

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

        $this->anioAnterior = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subYears(2)->toDateString(),
            'cierre_anio_escolar' => now()->subYear()->toDateString(),
            'status' => 'Inactivo',
        ]);
    }

    public function test_post_login_redirige_a_seleccionar_anio_si_no_hay_sesion(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect(route('admin.anio-escolar.seleccionar'));
    }

    public function test_home_redirige_a_seleccionar_anio_si_no_hay_sesion(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('home'));

        $response->assertRedirect(route('admin.anio-escolar.seleccionar'));
    }

    public function test_pantalla_seleccion_muestra_anios_escolares(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.anio-escolar.seleccionar'));

        $response->assertStatus(200);
        $response->assertSee('Seleccionar Año Escolar');
        $response->assertSee('Solo consulta');
    }

    public function test_guardar_anio_escolar_guarda_en_sesion_y_redirige_a_dashboard(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.anio-escolar.guardar'), [
            'anio_escolar_id' => $this->anioAnterior->id,
        ]);

        $response->assertSessionHas('anio_escolar_id', $this->anioAnterior->id);
        $response->assertRedirect($this->adminUser->rutaDashboard());
    }

    public function test_middleware_solo_lectura_bloquea_post_en_anio_anterior(): void
    {
        // Registrar ruta de prueba temporal para POST
        Route::post('/test-modificar-datos', function () {
            return response()->json(['success' => true]);
        })->middleware(['web']);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->post('/test-modificar-datos');

        $response->assertStatus(403);
    }

    public function test_middleware_solo_lectura_permite_post_en_anio_activo(): void
    {
        Route::post('/test-modificar-datos-activo', function () {
            return response()->json(['success' => true]);
        })->middleware(['web']);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioActivo->id])
            ->post('/test-modificar-datos-activo');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_middleware_solo_lectura_permite_guardar_nuevo_anio_aun_en_anio_anterior(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->post(route('admin.anio-escolar.guardar'), [
                'anio_escolar_id' => $this->anioActivo->id,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('anio_escolar_id', $this->anioActivo->id);
    }

    public function test_soporte_anio_escolar_seleccionado_devuelve_sesion_o_activo(): void
    {
        // Sin sesión, devuelve el activo
        $this->assertEquals($this->anioActivo->id, AnioEscolarSeleccionado::id());
        $this->assertTrue(AnioEscolarSeleccionado::esActual());

        // Con sesión del año anterior
        session(['anio_escolar_id' => $this->anioAnterior->id]);
        $this->assertEquals($this->anioAnterior->id, AnioEscolarSeleccionado::id());
        $this->assertFalse(AnioEscolarSeleccionado::esActual());
        $this->assertEquals($this->anioAnterior->id, AnioEscolarSeleccionado::modelo()->id);
    }

    public function test_modulos_cargan_correctamente_con_anio_seleccionado(): void
    {
        $admin = $this->adminUser;

        // Probar acceso a Inscripciones con sesión en año anterior
        $responseInscripcion = $this->actingAs($admin)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.transacciones.inscripcion.index'));
        $responseInscripcion->assertOk();

        // Probar acceso a Alumnos con sesión en año anterior
        $responseAlumnos = $this->actingAs($admin)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.alumnos.index'));
        $responseAlumnos->assertOk();

        // Probar acceso a Histórico con sesión en año anterior
        $responseHistorico = $this->actingAs($admin)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.historico.index'));
        $responseHistorico->assertOk();

        // Probar acceso a Prosecución con sesión en año anterior
        $responseProsecucion = $this->actingAs($admin)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.transacciones.inscripcion_prosecucion.index'));
        $responseProsecucion->assertOk();

        // Probar acceso a Percentil con sesión en año anterior
        $responsePercentil = $this->actingAs($admin)
            ->withSession(['anio_escolar_id' => $this->anioAnterior->id])
            ->get(route('admin.transacciones.percentil.index'));
        $responsePercentil->assertOk();
    }

    public function test_usuario_sin_permiso_redirige_directamente_a_dashboard_y_asigna_anio_activo_en_login(): void
    {
        $docenteUser = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Docente',
        ]);
        $docenteUser->assignRole('Docente');

        $response = $this->post(route('login'), [
            'email' => $docenteUser->email,
            'password' => 'Password123',
        ]);

        $response->assertRedirect($docenteUser->rutaDashboard());
        $response->assertSessionHas('anio_escolar_id', $this->anioActivo->id);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_pantalla_de_seleccion(): void
    {
        $docenteUser = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => Hash::make('Password123'),
            'activo' => true,
            'rol_principal' => 'Docente',
        ]);
        $docenteUser->assignRole('Docente');

        $response = $this->actingAs($docenteUser)->get(route('admin.anio-escolar.seleccionar'));
        $response->assertStatus(403);

        $responsePost = $this->actingAs($docenteUser)->post(route('admin.anio-escolar.guardar'), [
            'anio_escolar_id' => $this->anioAnterior->id,
        ]);
        $responsePost->assertStatus(403);
    }

    public function test_es_actual_retorna_true_si_no_hay_anios_activos_en_la_bd(): void
    {
        $this->anioActivo->update(['status' => 'Inactivo']);
        session()->forget('anio_escolar_id');

        $this->assertTrue(AnioEscolarSeleccionado::esActual());
    }
}
