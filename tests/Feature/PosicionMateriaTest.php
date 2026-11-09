<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\AreaFormacion;
use App\Models\AreaFormacionPosicion;
use App\Models\ConfiguracionHorario;
use App\Models\Grado;
use App\Models\Persona;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosicionMateriaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        $this->seed(PermisosSeeder::class);

        \DB::table('dias_semana')->insertOrIgnore([
            ['id' => 1, 'nombre_dia' => 'Lunes', 'status' => 1],
            ['id' => 2, 'nombre_dia' => 'Martes', 'status' => 1],
            ['id' => 3, 'nombre_dia' => 'Miercoles', 'status' => 1],
            ['id' => 4, 'nombre_dia' => 'Jueves', 'status' => 1],
            ['id' => 5, 'nombre_dia' => 'Viernes', 'status' => 1],
        ]);

        \DB::table('bloque_horarios')->insertOrIgnore([
            ['id' => 1, 'hora_inicio' => '07:00:00', 'hora_fin' => '07:45:00', 'status' => 1],
            ['id' => 2, 'hora_inicio' => '07:45:00', 'hora_fin' => '08:30:00', 'status' => 1],
        ]);
    }

    private function crearAdmin(): User
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        return $admin;
    }

    public function test_usuario_con_permiso_puede_ver_pantalla_posiciones(): void
    {
        $admin = $this->crearAdmin();

        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-15',
            'status' => 'Activo',
        ]);

        ConfiguracionHorario::create([
            'anio_escolar_id' => $anio->id,
            'hora_inicio' => '07:00:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        $area = AreaFormacion::create([
            'nombre_area_formacion' => 'Castellano',
            'codigo_area' => 'CAS-1',
            'siglas' => 'CAS',
            'bloques_semanales' => 4,
            'status' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.horario.posiciones'));

        $response->assertStatus(200);
        $response->assertSee('Posiciones Pedagógicas por Materia');
        $response->assertSee('Castellano');
    }

    public function test_usuario_puede_guardar_y_consultar_posiciones_por_materia(): void
    {
        $admin = $this->crearAdmin();

        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-15',
            'status' => 'Activo',
        ]);

        $area = AreaFormacion::create([
            'nombre_area_formacion' => 'Matemática',
            'codigo_area' => 'MAT-1',
            'siglas' => 'MAT',
            'bloques_semanales' => 4,
            'status' => true,
        ]);

        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => true,
        ]);

        // Guardar reglas para el área y grado
        $responseGuardar = $this->actingAs($admin)->postJson(route('admin.horario.posiciones.guardar'), [
            'area_formacion_id' => $area->id,
            'grado_id' => $grado->id,
            'posiciones' => [
                ['dia' => 1, 'bloque' => 1, 'tipo' => 'no_preferente'],
                ['dia' => 2, 'bloque' => 2, 'tipo' => 'no_preferente_fuerte'],
                ['dia' => 5, 'bloque' => 4, 'tipo' => 'prohibido'],
                ['dia' => 3, 'bloque' => 1, 'tipo' => 'disponible'], // Debe ignorarse / no guardarse
            ]
        ]);

        $responseGuardar->assertStatus(200);
        $responseGuardar->assertJson(['success' => true]);

        // Verificar en base de datos
        $this->assertDatabaseHas('area_formacion_posiciones', [
            'area_formacion_id' => $area->id,
            'grado_id' => $grado->id,
            'dia' => 1,
            'bloque' => 1,
            'tipo' => 'no_preferente',
        ]);

        $this->assertDatabaseHas('area_formacion_posiciones', [
            'area_formacion_id' => $area->id,
            'grado_id' => $grado->id,
            'dia' => 5,
            'bloque' => 4,
            'tipo' => 'prohibido',
        ]);

        $this->assertDatabaseMissing('area_formacion_posiciones', [
            'area_formacion_id' => $area->id,
            'dia' => 3,
            'bloque' => 1,
        ]);

        // Consultar vía API
        $responseConsultar = $this->actingAs($admin)->getJson(
            route('admin.horario.posiciones.consultar', [
                'area_formacion_id' => $area->id,
                'grado_id' => $grado->id,
            ])
        );

        $responseConsultar->assertStatus(200);
        $this->assertCount(3, $responseConsultar->json('posiciones'));
    }
}
