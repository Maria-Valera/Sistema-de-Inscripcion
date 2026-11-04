<?php

namespace Tests\Feature;

use App\Enums\Dia;
use App\Models\AnioEscolar;
use App\Models\BloqueReservado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_usuario_con_permiso_puede_ver_pantalla_de_configuracion_unificada(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.horario.configuracion'));

        $response->assertStatus(200);
        $response->assertSee('Configuración de Bloques Horarios');
        $response->assertSee('Hora de Inicio de Jornada');
        $response->assertSee('Descansos y Recesos');
        $response->assertSee('Bloques Reservados (Grupos Estables)');
        $response->assertSee('Agregar Bloque Reservado');
        $response->assertSee('Vista Previa en Vivo');
    }

    public function test_usuario_puede_ver_pantalla_con_bloques_reservados_existentes(): void
    {
        BloqueReservado::create([
            'anio_escolar_id' => $this->anio->id,
            'dia' => Dia::Lunes,
            'bloque' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('admin.horario.configuracion'));

        $response->assertStatus(200);
        $response->assertSee('Configuración de Bloques Horarios');
    }

    public function test_usuario_puede_actualizar_configuracion_recesos_y_reservados_en_un_solo_envio(): void
    {
        $payload = [
            'hora_inicio' => '07:30',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 5,
            'recesos' => [
                ['despues_de_bloque' => 2, 'duracion_min' => 20],
            ],
            'reservados' => [
                ['dia' => 1, 'desde' => 1, 'hasta' => 2],
                ['dia' => 5, 'desde' => 4, 'hasta' => 4],
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

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $this->anio->id,
            'dia' => 1,
            'bloque' => 1,
        ]);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $this->anio->id,
            'dia' => 1,
            'bloque' => 2,
        ]);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $this->anio->id,
            'dia' => 5,
            'bloque' => 4,
        ]);
    }

    public function test_usuario_recibe_error_si_reserva_rango_invertido(): void
    {
        $payload = [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
            'reservados' => [
                ['dia' => 2, 'desde' => 3, 'hasta' => 2],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('admin.horario.configuracion.update'), $payload);

        $response->assertSessionHas('error');
    }

    public function test_usuario_recibe_error_si_reserva_bloque_excede_limite(): void
    {
        $payload = [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
            'reservados' => [
                ['dia' => 1, 'desde' => 1, 'hasta' => 10],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('admin.horario.configuracion.update'), $payload);

        $response->assertSessionHas('error');
    }

    public function test_usuario_recibe_error_si_reserva_bloque_con_clases_existentes(): void
    {
        // Crear registros padres mínimos para cumplir con las claves foráneas de horario_asignaciones
        $tipoDocId = DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'V',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $generoId = DB::table('generos')->insertGetId([
            'genero' => 'M',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personaId = DB::table('personas')->insertGetId([
            'primer_nombre' => 'Docente',
            'primer_apellido' => 'Prueba',
            'numero_documento' => 'V-99999999',
            'fecha_nacimiento' => '1990-01-01',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docenteId = DB::table('docentes')->insertGetId([
            'persona_id' => $personaId,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gradoId = DB::table('grados')->insertGetId([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 5,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $seccionId = DB::table('seccions')->insertGetId([
            'nombre' => 'A',
            'cantidad_actual' => 0,
            'grado_id' => $gradoId,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $materiaId = DB::table('area_formacions')->insertGetId([
            'nombre_area_formacion' => 'Matemáticas',
            'codigo_area' => 101,
            'siglas' => 'MAT',
            'bloques_semanales' => 4,
            'aula_id' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $diaId = DB::table('dias_semana')->insertGetId([
            'nombre_dia' => 'Miércoles',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bloqueId = DB::table('bloque_horarios')->insertGetId([
            'hora_inicio' => '07:00:00',
            'hora_fin' => '08:30:00',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cargaId = DB::table('cargas_academicas')->insertGetId([
            'anio_escolar_id' => $this->anio->id,
            'docente_id' => $docenteId,
            'area_formacion_id' => $materiaId,
            'seccion_id' => $seccionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Simular una asignación en dia=3 (Miércoles), bloque=2
        DB::table('horario_asignaciones')->insert([
            'anio_escolar_id' => $this->anio->id,
            'carga_academica_id' => $cargaId,
            'aula_id' => null,
            'dia' => 3,
            'bloque' => 2,
            'dia_id' => $diaId,
            'bloque_id' => $bloqueId,
            'conflicto_manual' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
            'reservados' => [
                ['dia' => 3, 'bloque' => 2],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->put(route('admin.horario.configuracion.update'), $payload);

        $response->assertSessionHas('error');
    }
}
