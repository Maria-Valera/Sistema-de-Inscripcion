<?php

namespace Tests\Unit;

use App\Models\AnioEscolar;
use App\Models\ConfiguracionHorario;
use App\Models\RecesoHorario;
use App\Services\ConfiguracionHorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConfiguracionHorarioServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ConfiguracionHorarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ConfiguracionHorarioService();
    }

    protected function crearAnioActivo(): AnioEscolar
    {
        return AnioEscolar::create([
            'inicio_anio_escolar' => now()->toDateString(),
            'cierre_anio_escolar' => now()->addYear()->toDateString(),
            'status' => 'Activo',
        ]);
    }

    /**
     * Valida el cálculo del marco horario con receso según el ejemplo GHC:
     * Inicio 07:00, 4 bloques de 90 min, receso de 20 min tras el 2.º bloque.
     */
    public function test_calculo_bloques_con_recesos_ejemplo_ghc(): void
    {
        $config = new ConfiguracionHorario([
            'hora_inicio' => '07:00:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        $receso = new RecesoHorario([
            'despues_de_bloque' => 2,
            'duracion_min' => 20,
        ]);

        $config->setRelation('recesos', collect([$receso]));

        $bloques = $this->service->bloques($config);

        $this->assertCount(4, $bloques);

        $this->assertEquals([
            1 => ['inicio' => '07:00', 'fin' => '08:30'],
            2 => ['inicio' => '08:30', 'fin' => '10:00'],
            3 => ['inicio' => '10:20', 'fin' => '11:50'],
            4 => ['inicio' => '11:50', 'fin' => '13:20'],
        ], $bloques->all());
    }

    /**
     * Valida que reducir numero_bloques por debajo de asignaciones existentes lance excepción.
     */
    public function test_lanza_excepcion_al_reducir_bloques_con_asignaciones_existentes(): void
    {
        $anio = $this->crearAnioActivo();

        // Insertar bloque reservado simulado en el bloque 3
        DB::table('bloque_reservados')->insert([
            'anio_escolar_id' => $anio->id,
            'dia' => 1,
            'bloque' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/No se puede reducir el número de bloques a 2 porque ya existen registros asignados hasta el bloque 3/i');

        // Intentar guardar con solo 2 bloques
        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 2,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);
    }

    /**
     * Valida que guardar() persista la configuración y sincronice los recesos correctamente.
     */
    public function test_guardar_persiste_configuracion_y_sincroniza_recesos(): void
    {
        $anio = $this->crearAnioActivo();

        $datos = [
            'hora_inicio' => '07:15',
            'numero_bloques' => 5,
            'duracion_bloque_min' => 85,
            'minutos_entre_bloques' => 5,
            'recesos' => [
                ['despues_de_bloque' => 2, 'duracion_min' => 15],
                ['despues_de_bloque' => 3, 'duracion_min' => 20],
            ],
        ];

        $guardada = $this->service->guardar($anio->id, $datos);

        $this->assertEquals('07:15', substr($guardada->hora_inicio, 0, 5));
        $this->assertEquals(5, $guardada->numero_bloques);
        $this->assertEquals(85, $guardada->duracion_bloque_min);
        $this->assertEquals(5, $guardada->minutos_entre_bloques);
        $this->assertCount(2, $guardada->recesos);
    }

    /**
     * Valida que no se permitan recesos en posiciones inválidas (ej. después del último bloque).
     */
    public function test_lanza_excepcion_si_receso_es_invalido(): void
    {
        $anio = $this->crearAnioActivo();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/El receso tras el bloque 4 no es válido/i');

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'recesos' => [
                ['despues_de_bloque' => 4, 'duracion_min' => 20],
            ],
        ]);
    }

    /**
     * Valida que guardar() persista la configuración, recesos y bloques reservados en la misma llamada.
     */
    public function test_guardar_persiste_configuracion_recesos_y_reservados_en_una_sola_llamada(): void
    {
        $anio = $this->crearAnioActivo();

        $datos = [
            'hora_inicio' => '07:30',
            'numero_bloques' => 5,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
            'recesos' => [
                ['despues_de_bloque' => 2, 'duracion_min' => 20],
            ],
            'reservados' => [
                ['dia' => 1, 'bloque' => 2],
                ['dia' => 4, 'bloque' => 5],
            ],
        ];

        $guardada = $this->service->guardar($anio->id, $datos);

        $this->assertEquals(5, $guardada->numero_bloques);
        $this->assertCount(1, $guardada->recesos);
        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 1,
            'bloque' => 2,
        ]);
        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 4,
            'bloque' => 5,
        ]);
    }

    /**
     * Valida que guardar con rango (desde..hasta) inserte múltiples registros para ese día.
     */
    public function test_guardar_bloques_reservados_con_rango_inserta_multiples_registros(): void
    {
        $anio = $this->crearAnioActivo();

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'reservados' => [
                ['dia' => 2, 'desde' => 2, 'hasta' => 3],
            ],
        ]);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 2,
            'bloque' => 2,
        ]);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 2,
            'bloque' => 3,
        ]);

        $this->assertDatabaseCount('bloque_reservados', 2);
    }

    /**
     * Valida que enviar un rango invertido (desde > hasta) lance excepción.
     */
    public function test_lanza_excepcion_al_reservar_rango_invertido(): void
    {
        $anio = $this->crearAnioActivo();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/El bloque inicial \(3\) no puede ser mayor al bloque final \(2\)/i');

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
            'reservados' => [
                ['dia' => 2, 'desde' => 3, 'hasta' => 2],
            ],
        ]);
    }

    /**
     * Valida que guardarReservados persista los bloques asociados al año escolar correcto.
     */
    public function test_guardar_bloques_reservados_asocia_al_anio_escolar(): void
    {
        $anio = $this->crearAnioActivo();

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
        ]);

        $this->service->guardarReservados($anio->id, ['1_2', '5_4']);

        $mapa = $this->service->reservadosMapa($anio->id);

        $this->assertEquals([
            '1_2' => true,
            '5_4' => true,
        ], $mapa);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 1,
            'bloque' => 2,
        ]);

        $this->assertDatabaseHas('bloque_reservados', [
            'anio_escolar_id' => $anio->id,
            'dia' => 5,
            'bloque' => 4,
        ]);
    }

    /**
     * Valida que no se pueda reservar un bloque mayor a numero_bloques.
     */
    public function test_lanza_excepcion_al_reservar_bloque_mayor_a_numero_bloques(): void
    {
        $anio = $this->crearAnioActivo();

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/No se puede reservar el bloque 5/i');

        $this->service->guardarReservados($anio->id, ['1_2', '2_5']);
    }

    /**
     * Valida que no se pueda reservar un bloque si ya tiene clases en horario_asignaciones.
     */
    public function test_lanza_excepcion_al_reservar_bloque_con_clases_existentes(): void
    {
        $anio = $this->crearAnioActivo();

        $this->service->guardar($anio->id, [
            'hora_inicio' => '07:00',
            'numero_bloques' => 4,
            'duracion_bloque_min' => 90,
        ]);

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

        // Simular una asignación en dia=3 (Miércoles), bloque=2
        DB::table('horario_asignaciones')->insert([
            'anio_escolar_id' => $anio->id,
            'docente_id' => $docenteId,
            'materia_id' => $materiaId,
            'seccion_id' => $seccionId,
            'aula_id' => null,
            'dia' => 3,
            'bloque' => 2,
            'dia_id' => $diaId,
            'bloque_id' => $bloqueId,
            'conflicto_manual' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/existen clases asignadas en: Miércoles Bloque 2/i');

        $this->service->guardarReservados($anio->id, ['3_2']);
    }
}
