<?php

namespace Tests\Unit;

use App\Enums\Dia;
use App\Models\AnioEscolar;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\BloqueReservado;
use App\Models\CargaAcademica;
use App\Models\Docente;
use App\Models\DocenteIndisponibilidad;
use App\Models\HorarioAsignacion;
use App\Models\Persona;
use App\Models\Seccion;
use App\Models\Grado;
use App\Services\GeneradorHorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneradorHorarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeneradorHorarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeneradorHorarioService();

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
            ['id' => 3, 'hora_inicio' => '08:30:00', 'hora_fin' => '09:15:00', 'status' => 1],
            ['id' => 4, 'hora_inicio' => '09:15:00', 'hora_fin' => '10:00:00', 'status' => 1],
            ['id' => 5, 'hora_inicio' => '10:00:00', 'hora_fin' => '10:45:00', 'status' => 1],
        ]);
    }

    public function test_calcular_max_bloques_usa_mitad_de_horas_y_tope_de_18(): void
    {
        // 36 horas = 18 bloques
        $this->assertEquals(18, $this->service->calcularMaxBloques(36));

        // 40 horas = 18 bloques (tope 18)
        $this->assertEquals(18, $this->service->calcularMaxBloques(40));

        // 12 horas = 6 bloques
        $this->assertEquals(6, $this->service->calcularMaxBloques(12));

        // 15 horas = 7 bloques (intdiv)
        $this->assertEquals(7, $this->service->calcularMaxBloques(15));
    }

    public function test_preparas_datos_usa_bloques_semanales_y_elimina_claves_obsoletas(): void
    {
        $tipoDocId = \DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'CI',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $generoId = \DB::table('generos')->insertGetId([
            'genero' => 'M',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $personaId = \DB::table('personas')->insertGetId([
            'primer_nombre' => 'María',
            'primer_apellido' => 'González',
            'numero_documento' => 'V11111111',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
            'fecha_nacimiento' => '1980-01-01',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docente = Docente::create([
            'persona_id' => $personaId,
            'horas_academicas' => 36,
            'status' => 1,
        ]);

        $estudioId = \DB::table('estudios_realizados')->insertGetId([
            'estudios' => 'Licenciatura',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $detalleEstudio = \App\Models\DetalleDocenteEstudio::create([
            'docente_id' => $docente->id,
            'estudios_id' => $estudioId,
            'status' => 1,
        ]);

        $area = AreaFormacion::create([
            'nombre_area_formacion' => 'Biología',
            'codigo_area' => 'BIO-1',
            'siglas' => 'BIO',
            'bloques_semanales' => 4,
            'status' => 1,
        ]);

        $areaEstudio = \App\Models\AreaEstudioRealizado::create([
            'area_formacion_id' => $area->id,
            'estudios_id' => $estudioId,
        ]);

        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);

        $seccion = Seccion::create([
            'nombre' => 'B',
            'grado_id' => $grado->id,
            'cantidad_actual' => 20,
            'status' => 1,
        ]);

        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-15',
            'status' => 'Activo',
        ]);

        $carga = CargaAcademica::create([
            'anio_escolar_id' => $anio->id,
            'docente_id' => $docente->id,
            'area_formacion_id' => $area->id,
            'seccion_id' => $seccion->id,
        ]);

        $datos = $this->service->PreparasDatos($grado->id);

        $this->assertCount(1, $datos);
        $clase = $datos->first();

        // Debe mapear bloques_semanales a 'area_bloque' y contener carga_academica_id
        $this->assertEquals(4, $clase['area_bloque']);
        $this->assertEquals($carga->id, $clase['carga_academica_id']);

        // Claves viejas eliminadas
        $this->assertArrayNotHasKey('bloques_max_dia', $clase);
        $this->assertArrayNotHasKey('dias_max_semana', $clase);
    }

    public function test_inicializar_matrices_filtra_solo_indisponibilidades_aprobadas_y_prohibidas_y_carga_reservados(): void
    {
        $tipoDocId = \DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'CI',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $generoId = \DB::table('generos')->insertGetId([
            'genero' => 'M',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('dias_semana')->insertOrIgnore([
            ['id' => 1, 'nombre_dia' => 'Lunes', 'status' => 1],
            ['id' => 2, 'nombre_dia' => 'Martes', 'status' => 1],
        ]);
        \DB::table('bloque_horarios')->insertOrIgnore([
            ['id' => 1, 'hora_inicio' => '07:00:00', 'hora_fin' => '07:45:00', 'status' => 1],
            ['id' => 2, 'hora_inicio' => '07:45:00', 'hora_fin' => '08:30:00', 'status' => 1],
        ]);

        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => now()->toDateString(),
            'cierre_anio_escolar' => now()->addYear()->toDateString(),
            'status' => 'Activo',
        ]);

        $personaId = \DB::table('personas')->insertGetId([
            'primer_nombre' => 'Carlos',
            'primer_apellido' => 'Perez',
            'numero_documento' => 'V12345678',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
            'fecha_nacimiento' => '1985-05-15',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docente = Docente::create([
            'persona_id' => $personaId,
            'horas_academicas' => 36,
            'status' => 1,
        ]);

        // 1. Indisponibilidad aprobada y prohibida (debe incluirse)
        DocenteIndisponibilidad::create([
            'docente_id' => $docente->id,
            'anio_escolar_id' => $anio->id,
            'dias_semana_id' => 1,
            'id_bloque_hora' => 1,
            'dia' => Dia::Lunes,
            'bloque' => 1,
            'tipo' => 'prohibido',
            'estado' => 'aprobada',
        ]);

        // 2. Indisponibilidad pendiente (NO debe incluirse)
        DocenteIndisponibilidad::create([
            'docente_id' => $docente->id,
            'anio_escolar_id' => $anio->id,
            'dias_semana_id' => 1,
            'id_bloque_hora' => 2,
            'dia' => Dia::Lunes,
            'bloque' => 2,
            'tipo' => 'prohibido',
            'estado' => 'pendiente',
        ]);

        // 3. Indisponibilidad no_preferente (NO debe incluirse)
        DocenteIndisponibilidad::create([
            'docente_id' => $docente->id,
            'anio_escolar_id' => $anio->id,
            'dias_semana_id' => 2,
            'id_bloque_hora' => 1,
            'dia' => Dia::Martes,
            'bloque' => 1,
            'tipo' => 'no_preferente',
            'estado' => 'aprobada',
        ]);

        // 4. Bloque reservado
        BloqueReservado::create([
            'anio_escolar_id' => $anio->id,
            'dia' => Dia::Martes,
            'bloque' => 2,
        ]);

        $matrices = $this->service->InicializarMatrices($anio->id);

        // Verifica docente ocupado
        $this->assertTrue(isset($matrices['docenteOcupado'][$docente->id . '_1_1']));
        $this->assertFalse(isset($matrices['docenteOcupado'][$docente->id . '_1_2']));
        $this->assertFalse(isset($matrices['docenteOcupado'][$docente->id . '_2_1']));

        // Verifica reservados
        $this->assertTrue(isset($matrices['reservado']['2_2']));
        $this->assertFalse(isset($matrices['reservado']['1_1']));
    }

    public function test_asignar_un_bloque_evita_bloques_reservados_y_no_repite_materia_en_mismo_dia(): void
    {
        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);

        $clase = [
            'docente_id' => 10,
            'area_id' => 5,
            'seccion_id' => $seccion->id,
            'area_aula' => null,
        ];

        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [10 => 0],
            'reservado' => ['1_1' => true], // Día 1 Bloque 1 reservado
            'materiaDia' => [],
        ];

        // Primer intento: Debe saltar 1_1 porque está reservado, y asignar 1_2
        $asig1 = $this->service->asignarUnBloque($clase, $matrices, totalDias: 2, totalBloques: 2, maxBloquesDocente: 18);

        $this->assertNotNull($asig1);
        $this->assertEquals(1, $asig1['dia']);
        $this->assertEquals(2, $asig1['bloque']);

        // Segundo intento para la misma materia y misma sección:
        // Como el Día 1 ya tiene la materia asignada, debe saltar al Día 2
        $asig2 = $this->service->asignarUnBloque($clase, $matrices, totalDias: 2, totalBloques: 2, maxBloquesDocente: 18);

        $this->assertNotNull($asig2);
        $this->assertEquals(2, $asig2['dia']);
        $this->assertEquals(1, $asig2['bloque']);
    }

    public function test_generar_no_borra_clases_fijadas(): void
    {
        $tipoDocId = \DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'CI',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $generoId = \DB::table('generos')->insertGetId([
            'genero' => 'M',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('dias_semana')->insertOrIgnore([
            ['id' => 1, 'nombre_dia' => 'Lunes', 'status' => 1],
            ['id' => 2, 'nombre_dia' => 'Martes', 'status' => 1],
        ]);
        \DB::table('bloque_horarios')->insertOrIgnore([
            ['id' => 1, 'hora_inicio' => '07:00:00', 'hora_fin' => '07:45:00', 'status' => 1],
            ['id' => 2, 'hora_inicio' => '07:45:00', 'hora_fin' => '08:30:00', 'status' => 1],
        ]);

        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => now()->toDateString(),
            'cierre_anio_escolar' => now()->addYear()->toDateString(),
            'status' => 'Activo',
        ]);

        $personaId = \DB::table('personas')->insertGetId([
            'primer_nombre' => 'Carlos',
            'primer_apellido' => 'Perez',
            'numero_documento' => 'V12345678',
            'tipo_documento_id' => $tipoDocId,
            'genero_id' => $generoId,
            'fecha_nacimiento' => '1985-05-15',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docente = Docente::create([
            'persona_id' => $personaId,
            'horas_academicas' => 36,
            'status' => 1,
        ]);

        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);
        $area = AreaFormacion::create([
            'nombre_area_formacion' => 'Matemática',
            'codigo_area' => 'MAT-1',
            'siglas' => 'MAT',
            'bloques_semanales' => 3,
            'status' => 1,
        ]);
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);

        $carga = CargaAcademica::create([
            'anio_escolar_id' => $anio->id,
            'docente_id' => $docente->id,
            'area_formacion_id' => $area->id,
            'seccion_id' => $seccion->id,
        ]);

        // Clase fijada
        $fijada = HorarioAsignacion::create([
            'anio_escolar_id' => $anio->id,
            'carga_academica_id' => $carga->id,
            'aula_id' => $aula->id_aula,
            'dia' => 1,
            'bloque' => 1,
            'fijada' => true,
        ]);

        // Clase NO fijada (debe ser borrada al regenerar)
        $noFijada = HorarioAsignacion::create([
            'anio_escolar_id' => $anio->id,
            'carga_academica_id' => $carga->id,
            'aula_id' => $aula->id_aula,
            'dia' => 2,
            'bloque' => 1,
            'fijada' => false,
        ]);

        // Llamamos a generar
        $this->service->generar($anio->id, totalDias: 5, totalBloques: 4);

        // La clase fijada debe persistir intacta
        $this->assertDatabaseHas('horario_asignaciones', [
            'id' => $fijada->id,
            'fijada' => true,
            'dia' => 1,
            'bloque' => 1,
        ]);

        // La no fijada anterior fue eliminada (su ID ya no existe)
        $this->assertDatabaseMissing('horario_asignaciones', [
            'id' => $noFijada->id,
        ]);
    }

    public function test_determinar_tipo_conflicto_devuelve_una_por_dia(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);

        $clase = [
            'docente_id' => 1,
            'docente_nombre' => 'Docente Prueba',
            'docente_horas_academicas' => 36,
            'area_id' => 10,
            'area_nombre' => 'Historia',
            'seccion_id' => $seccion->id,
            'seccion_nombre' => 'A',
            'bloques_restantes' => 1,
            'area_aula' => null,
        ];

        // Docente y sección libres, pero materia ya asignada en todos los días evaluados
        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [1 => 2],
            'reservado' => [],
            'materiaDia' => [
                '10_' . $seccion->id . '_1' => true,
                '10_' . $seccion->id . '_2' => true,
            ],
        ];

        $conflicto = $this->service->determinarTipoConflicto($clase, $matrices, totalDias: 2, totalBloques: 2);

        $this->assertEquals('una_por_dia', $conflicto['tipo']);
        $this->assertStringContainsString('ya tiene un bloque asignado', $conflicto['motivo']);
    }

    public function test_determinar_tipo_conflicto_devuelve_bloque_reservado(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);

        $clase = [
            'docente_id' => 1,
            'docente_nombre' => 'Docente Prueba',
            'docente_horas_academicas' => 36,
            'area_id' => 10,
            'area_nombre' => 'Historia',
            'seccion_id' => $seccion->id,
            'seccion_nombre' => 'A',
            'bloques_restantes' => 1,
            'area_aula' => null,
        ];

        // Docente y sección coinciden libres SOLO en 1_1 y 2_1, pero ambos están reservados globalmente
        $matrices = [
            'docenteOcupado' => [
                '1_1_2' => true,
                '1_2_2' => true,
            ],
            'seccionOcupado' => [
                $seccion->id . '_1_2' => true,
                $seccion->id . '_2_2' => true,
            ],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [1 => 2],
            'reservado' => [
                '1_1' => true,
                '2_1' => true,
            ],
            'materiaDia' => [],
        ];

        $conflicto = $this->service->determinarTipoConflicto($clase, $matrices, totalDias: 2, totalBloques: 2);

        $this->assertEquals('bloque_reservado', $conflicto['tipo']);
        $this->assertStringContainsString('reservaciones globales', $conflicto['motivo']);
    }

    public function test_identificar_espacios_fallidos_lista_razones_y_usa_labels_de_dia(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);

        $clase = [
            'docente_id' => 1,
            'docente_horas_academicas' => 36,
            'area_id' => 10,
            'seccion_id' => $seccion->id,
            'area_aula' => null,
        ];

        $matrices = [
            'docenteOcupado' => [
                '1_1_1' => true, // Día 1 Bloque 1: docente ocupado
            ],
            'seccionOcupado' => [
                $seccion->id . '_1_2' => true, // Día 1 Bloque 2: sección ocupada
            ],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [1 => 0],
            'reservado' => [
                '2_1' => true, // Día 2 Bloque 1: bloque reservado globalmente
            ],
            'materiaDia' => [
                '10_' . $seccion->id . '_2' => true, // Día 2: materia ya asignada
            ],
        ];

        $fallidos = $this->service->identificarEspaciosFallidos($clase, $matrices, totalDias: 2, totalBloques: 2);

        $this->assertNotEmpty($fallidos);

        // Mapear por clave dia_bloque
        $mapaFallidos = [];
        foreach ($fallidos as $f) {
            $mapaFallidos[$f['dia'] . '_' . $f['bloque']] = $f['razones'];
        }

        // Día 1 (Lunes), Bloque 1: docente ocupado
        $this->assertArrayHasKey('Lunes_1', $mapaFallidos);
        $this->assertContains('docente ocupado', $mapaFallidos['Lunes_1']);

        // Día 1 (Lunes), Bloque 2: sección ocupada
        $this->assertArrayHasKey('Lunes_2', $mapaFallidos);
        $this->assertContains('sección ocupada', $mapaFallidos['Lunes_2']);

        // Día 2 (Martes), Bloque 1: reservado y materia ya asignada
        $this->assertArrayHasKey('Martes_1', $mapaFallidos);
        $this->assertContains('bloque reservado globalmente', $mapaFallidos['Martes_1']);
        $this->assertContains('materia ya asignada ese día', $mapaFallidos['Martes_1']);

        // Día 2 (Martes), Bloque 2: materia ya asignada ese día
        $this->assertArrayHasKey('Martes_2', $mapaFallidos);
        $this->assertContains('materia ya asignada ese día', $mapaFallidos['Martes_2']);
    }

    public function test_determinar_tipo_conflicto_docente_saturado(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);

        $clase = [
            'docente_id' => 1,
            'docente_nombre' => 'Docente Saturado',
            'docente_horas_academicas' => 36, // max 18 bloques
            'area_id' => 10,
            'area_nombre' => 'Historia',
            'seccion_id' => $seccion->id,
            'seccion_nombre' => 'A',
            'bloques_restantes' => 1,
            'area_aula' => null,
        ];

        // Docente ya tiene 18 bloques asignados
        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [1 => 18],
            'reservado' => [],
            'materiaDia' => [],
        ];

        $conflicto = $this->service->determinarTipoConflicto($clase, $matrices, totalDias: 2, totalBloques: 2);
        $this->assertEquals('docente_saturado', $conflicto['tipo']);
    }

    public function test_determinar_tipo_conflicto_seccion_saturada(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 25, 'status' => 1]);

        $clase = [
            'docente_id' => 1,
            'docente_nombre' => 'Docente Libre',
            'docente_horas_academicas' => 36,
            'area_id' => 10,
            'area_nombre' => 'Historia',
            'seccion_id' => $seccion->id,
            'seccion_nombre' => 'A',
            'bloques_restantes' => 1,
            'area_aula' => null,
        ];

        // Sección ocupada en todos los bloques (2 días x 2 bloques)
        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [
                $seccion->id . '_1_1' => true,
                $seccion->id . '_1_2' => true,
                $seccion->id . '_2_1' => true,
                $seccion->id . '_2_2' => true,
            ],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [1 => 0],
            'reservado' => [],
            'materiaDia' => [],
        ];

        $conflicto = $this->service->determinarTipoConflicto($clase, $matrices, totalDias: 2, totalBloques: 2);
        $this->assertEquals('seccion_saturada', $conflicto['tipo']);
    }

    /**
     * M5 / P6: Demuestra que con un docente de 4 bloques en 5 días, el generador
     * prefiere poner los bloques en días distintos antes que dos en el mismo día por cargaDelDocenteEseDia.
     */
    public function test_generador_prefiere_dias_distintos_por_carga_del_docente_ese_dia(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 4, 'status' => 1]);

        $seccion1 = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);
        $seccion2 = Seccion::create(['nombre' => 'B', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);
        $seccion3 = Seccion::create(['nombre' => 'C', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);
        $seccion4 = Seccion::create(['nombre' => 'D', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);

        $docenteId = 99;

        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [$docenteId => 0],
            'reservado' => [],
            'materiaDia' => [],
            'docenteDia' => [],
            'docenteNoPreferente' => [],
        ];

        // 4 clases para el mismo docente en 4 secciones distintas
        $clase1 = ['docente_id' => $docenteId, 'area_id' => 10, 'seccion_id' => $seccion1->id, 'area_aula' => null];
        $clase2 = ['docente_id' => $docenteId, 'area_id' => 11, 'seccion_id' => $seccion2->id, 'area_aula' => null];
        $clase3 = ['docente_id' => $docenteId, 'area_id' => 12, 'seccion_id' => $seccion3->id, 'area_aula' => null];
        $clase4 = ['docente_id' => $docenteId, 'area_id' => 13, 'seccion_id' => $seccion4->id, 'area_aula' => null];

        $asig1 = $this->service->asignarUnBloque($clase1, $matrices, totalDias: 5, totalBloques: 8, maxBloquesDocente: 18);
        $asig2 = $this->service->asignarUnBloque($clase2, $matrices, totalDias: 5, totalBloques: 8, maxBloquesDocente: 18);
        $asig3 = $this->service->asignarUnBloque($clase3, $matrices, totalDias: 5, totalBloques: 8, maxBloquesDocente: 18);
        $asig4 = $this->service->asignarUnBloque($clase4, $matrices, totalDias: 5, totalBloques: 8, maxBloquesDocente: 18);

        $this->assertNotNull($asig1);
        $this->assertNotNull($asig2);
        $this->assertNotNull($asig3);
        $this->assertNotNull($asig4);

        $diasAsignados = [$asig1['dia'], $asig2['dia'], $asig3['dia'], $asig4['dia']];

        // Cada bloque debe haberse asignado a un día diferente para balancear la carga diaria del docente
        $this->assertCount(4, array_unique($diasAsignados));
        $this->assertEquals([1, 2, 3, 4], $diasAsignados);
    }

    /**
     * M5 / P6: Prueba que contarHuecos() devuelve 0 en una configuración ideal (clases pegadas desde el primer bloque).
     */
    public function test_contar_huecos_devuelve_cero_en_configuracion_ideal(): void
    {
        $seccionId = 1;
        $asignaciones = [
            ['seccion_id' => $seccionId, 'dia' => 1, 'bloque' => 1],
            ['seccion_id' => $seccionId, 'dia' => 1, 'bloque' => 2],
            ['seccion_id' => $seccionId, 'dia' => 1, 'bloque' => 3],
            ['seccion_id' => $seccionId, 'dia' => 2, 'bloque' => 1],
            ['seccion_id' => $seccionId, 'dia' => 2, 'bloque' => 2],
        ];

        $huecos = $this->service->contarHuecos($asignaciones, [], [$seccionId]);
        $this->assertEquals(0, $huecos);
    }

    /**
     * M5 / P6: Prueba que contarHuecos() detecta huecos reales y cuenta celdas reservadas como ocupadas.
     */
    public function test_contar_huecos_detecta_huecos_y_respeta_celdas_reservadas(): void
    {
        $seccionId = 1;

        // Configuración con hueco en el bloque 2 del día 1
        $asignaciones = [
            ['seccion_id' => $seccionId, 'dia' => 1, 'bloque' => 1],
            ['seccion_id' => $seccionId, 'dia' => 1, 'bloque' => 3],
        ];

        // 1 hueco detectado
        $huecosSinReserva = $this->service->contarHuecos($asignaciones, [], [$seccionId]);
        $this->assertEquals(1, $huecosSinReserva);

        // Si el bloque intermedio está reservado (ej. grupos estables), cuenta como ocupado y el total de huecos es 0
        $reservado = ['1_2' => true];
        $huecosConReserva = $this->service->contarHuecos($asignaciones, $reservado, [$seccionId]);
        $this->assertEquals(0, $huecosConReserva);
    }

    /**
     * M5 / P6: Prueba que costoPosicion penaliza celdas no_preferente y prefiere celdas libres.
     */
    public function test_asignar_un_bloque_considera_costo_posicion_no_preferente(): void
    {
        $aula = Aula::create(['nombre_aula' => 'Aula 1', 'tipo_aula' => Aula::TIPO_REGULAR, 'capacidad_maxima' => 30, 'status' => 1]);
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 1, 'status' => 1]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);

        $docenteId = 77;

        // El docente tiene indisponibilidad no_preferente en Día 1 Bloque 1
        $matrices = [
            'docenteOcupado' => [],
            'seccionOcupado' => [],
            'aulaEspecialOcupada' => [],
            'bloquesPorDocente' => [$docenteId => 0],
            'reservado' => [],
            'materiaDia' => [],
            'docenteDia' => [],
            'docenteNoPreferente' => [$docenteId . '_1_1' => true],
        ];

        $clase = ['docente_id' => $docenteId, 'area_id' => 10, 'seccion_id' => $seccion->id, 'area_aula' => null];

        // En Día 1 Bloque 1 el costo es 1 (costoPosicion = 1).
        // En Día 1 Bloque 2 el costo es 0 (no hay hueco previo, no es no_preferente, carga = 0).
        $asig = $this->service->asignarUnBloque($clase, $matrices, totalDias: 2, totalBloques: 2, maxBloquesDocente: 18);

        $this->assertNotNull($asig);
        // Debe evitar el bloque 1 y elegir el bloque 2 por tener menor costo
        $this->assertEquals(1, $asig['dia']);
        $this->assertEquals(2, $asig['bloque']);
    }
}


