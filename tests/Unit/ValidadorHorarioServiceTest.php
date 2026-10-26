<?php

namespace Tests\Unit;

use App\Enums\Dia;
use App\Models\AnioEscolar;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\BloqueReservado;
use App\Models\ConfiguracionHorario;
use App\Models\DetalleDocenteEstudio;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\DocenteIndisponibilidad;
use App\Models\Grado;
use App\Models\GradoAreaFormacion;
use App\Models\Persona;
use App\Models\Seccion;
use App\Models\seccion_aula;
use App\Services\ValidadorHorarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidadorHorarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private ValidadorHorarioService $service;
    private int $tipoDocId;
    private int $generoId;
    private int $estudioId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ValidadorHorarioService();

        $this->tipoDocId = \DB::table('tipo_documentos')->insertGetId([
            'nombre' => 'CI',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->generoId = \DB::table('generos')->insertGetId([
            'genero' => 'M',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->estudioId = \DB::table('estudios_realizados')->insertGetId([
            'estudios' => 'Profesor de Educación Media',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    private function crearAnioEscolar(int $numeroBloques = 8): AnioEscolar
    {
        $anio = AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-15',
            'status' => 'Activo',
        ]);

        ConfiguracionHorario::create([
            'anio_escolar_id' => $anio->id,
            'hora_inicio' => '07:00:00',
            'numero_bloques' => $numeroBloques,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        return $anio;
    }

    private function crearDocente(string $nombre = 'Docente', string $apellido = 'Prueba', int $horas = 36): Docente
    {
        static $ciCounter = 1000000;
        $ciCounter++;

        $personaId = \DB::table('personas')->insertGetId([
            'primer_nombre' => $nombre,
            'primer_apellido' => $apellido,
            'numero_documento' => 'V' . $ciCounter,
            'tipo_documento_id' => $this->tipoDocId,
            'genero_id' => $this->generoId,
            'fecha_nacimiento' => '1985-05-10',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $docente = Docente::create([
            'persona_id' => $personaId,
            'horas_academicas' => $horas,
            'status' => 1,
        ]);

        DetalleDocenteEstudio::create([
            'docente_id' => $docente->id,
            'estudios_id' => $this->estudioId,
            'status' => 1,
        ]);

        return $docente;
    }

    private function crearAula(string $nombre = 'Aula 1', string $tipo = Aula::TIPO_REGULAR, int $capacidad = 35): Aula
    {
        return Aula::create([
            'nombre_aula' => $nombre,
            'tipo_aula' => $tipo,
            'capacidad_maxima' => $capacidad,
            'status' => true,
        ]);
    }

    private function crearGradoYSeccion(string $numGrado = '1', string $nomSeccion = 'A', int $cantidad = 25, ?Aula $aula = null): array
    {
        $grado = Grado::create([
            'numero_grado' => $numGrado,
            'capacidad_max' => 35,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);

        $seccion = Seccion::create([
            'nombre' => $nomSeccion,
            'grado_id' => $grado->id,
            'cantidad_actual' => $cantidad,
            'status' => 1,
        ]);

        if ($aula) {
            seccion_aula::create([
                'id_seccion' => $seccion->id,
                'id_aula' => $aula->id_aula,
                'status' => true,
            ]);
        }

        return [$grado, $seccion];
    }

    private function crearArea(string $nombre, ?int $bloques = 3, ?Aula $aula = null): AreaFormacion
    {
        static $codigoCounter = 100;
        $codigoCounter++;

        $area = AreaFormacion::create([
            'nombre_area_formacion' => $nombre,
            'codigo_area' => 'COD-' . $codigoCounter,
            'siglas' => substr($nombre, 0, 3),
            'bloques_semanales' => $bloques,
            'aula_id' => $aula?->id_aula,
            'status' => 1,
        ]);

        AreaEstudioRealizado::create([
            'area_formacion_id' => $area->id,
            'estudios_id' => $this->estudioId,
            'status' => 1,
        ]);

        return $area;
    }

    private function asignarCarga(Docente $docente, AreaFormacion $area, Seccion $seccion, Grado $grado): DocenteAreaGrado
    {
        $detalle = $docente->detalleDocenteEstudio()->first();
        $areaEstudio = AreaEstudioRealizado::where('area_formacion_id', $area->id)->first();

        // Vincular curricularmente si no está vinculada
        if (!GradoAreaFormacion::where('grado_id', $grado->id)->where('area_formacion_id', $area->id)->exists()) {
            GradoAreaFormacion::create([
                'grado_id' => $grado->id,
                'area_formacion_id' => $area->id,
                'status' => 1,
            ]);
        }

        return DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $detalle->id,
            'area_estudio_realizado_id' => $areaEstudio->id,
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'status' => 1,
        ]);
    }

    /**
     * Error 1: Área con bloques_semanales > 5 o <= 0
     */
    public function test_error_area_bloques_semanales_invalidos(): void
    {
        $anio = $this->crearAnioEscolar();
        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 25, $aula);

        // Área con 6 bloques (> 5 días lectivos)
        $areaExcesiva = $this->crearArea('Matemática Intensiva', 6);
        $docente = $this->crearDocente('Carlos', 'Pérez');
        $this->asignarCarga($docente, $areaExcesiva, $seccion, $grado);

        // Área con 0 bloques
        $areaCero = $this->crearArea('Taller Cero', 0);
        $this->asignarCarga($docente, $areaCero, $seccion, $grado);

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('area_bloques_invalidos', $codigosError);

        $mensajes = collect($resultado['errores'])->where('codigo', 'area_bloques_invalidos')->pluck('mensaje')->implode(' ');
        $this->assertStringContainsString('Matemática Intensiva', $mensajes);
        $this->assertStringContainsString('Taller Cero', $mensajes);
    }

    /**
     * Error 2: Sección cuyas áreas suman más bloques que los disponibles
     */
    public function test_error_seccion_exceso_bloques(): void
    {
        // 4 bloques diarios * 5 días = 20 bloques semanales
        $anio = $this->crearAnioEscolar(4);

        // 5 bloques reservados institucionalmente -> Disponibles = 15
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 1]);
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 2]);
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 3]);
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 4]);
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Jueves, 'bloque' => 1]);

        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('2', 'A', 25, $aula);
        $docente = $this->crearDocente();

        // 4 áreas de 4 bloques cada una = 16 bloques requeridos > 15 disponibles
        for ($i = 1; $i <= 4; $i++) {
            $area = $this->crearArea("Materia $i", 4);
            $this->asignarCarga($docente, $area, $seccion, $grado);
        }

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('seccion_exceso_bloques', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'seccion_exceso_bloques');
        $this->assertStringContainsString('requiere 16 bloques semanales', $error['mensaje']);
    }

    /**
     * Error 3.1: Docente con más de 18 bloques requeridos
     */
    public function test_error_docente_excede_18_bloques(): void
    {
        $anio = $this->crearAnioEscolar(8);
        $aula = $this->crearAula();
        [$grado, $seccion1] = $this->crearGradoYSeccion('1', 'A', 20, $aula);
        [, $seccion2] = $this->crearGradoYSeccion('1', 'B', 20, $aula);

        $docente = $this->crearDocente('Roberto', 'Cisneros', 36);

        // Asignar 20 bloques (5 materias de 4 bloques)
        $m1 = $this->crearArea('Física 1', 4);
        $m2 = $this->crearArea('Física 2', 4);
        $m3 = $this->crearArea('Física 3', 4);
        $m4 = $this->crearArea('Física 4', 4);
        $m5 = $this->crearArea('Física 5', 4);

        $this->asignarCarga($docente, $m1, $seccion1, $grado);
        $this->asignarCarga($docente, $m2, $seccion1, $grado);
        $this->asignarCarga($docente, $m3, $seccion1, $grado);
        $this->asignarCarga($docente, $m4, $seccion2, $grado);
        $this->asignarCarga($docente, $m5, $seccion2, $grado);

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('docente_excede_maximo_bloques', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'docente_excede_maximo_bloques');
        $this->assertStringContainsString('Roberto Cisneros', $error['mensaje']);
        $this->assertStringContainsString('20 bloques', $error['mensaje']);
    }

    /**
     * Error 3.2: Docente con más bloques requeridos que sus espacios libres (indisponibilidad y reservados)
     */
    public function test_error_docente_sin_espacios_suficientes(): void
    {
        // 4 bloques diarios * 5 días = 20 bloques
        $anio = $this->crearAnioEscolar(4);
        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('3', 'A', 20, $aula);

        $docente = $this->crearDocente('Laura', 'Méndez');
        $area = $this->crearArea('Química', 5);
        $this->asignarCarga($docente, $area, $seccion, $grado);

        // Reservar 2 bloques institucionalmente (Viernes bloques 3 y 4)
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 3]);
        BloqueReservado::create(['anio_escolar_id' => $anio->id, 'dia' => Dia::Viernes, 'bloque' => 4]);

        // Docente tiene indisponibilidad en 16 de los 20 bloques
        // Dejándole solo 2 bloques libres (20 - 2 reservados - 16 indisponibles = 2 libres)
        $dias = [Dia::Lunes, Dia::Martes, Dia::Miercoles, Dia::Jueves];
        foreach ($dias as $d) {
            for ($b = 1; $b <= 4; $b++) {
                DocenteIndisponibilidad::create([
                    'docente_id' => $docente->id,
                    'anio_escolar_id' => $anio->id,
                    'dias_semana_id' => 1,
                    'id_bloque_hora' => 1,
                    'dia' => $d,
                    'bloque' => $b,
                    'tipo' => 'prohibido',
                    'estado' => 'aprobada',
                ]);
            }
        }

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('docente_sin_espacios_suficientes', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'docente_sin_espacios_suficientes');
        $this->assertStringContainsString('Laura Méndez', $error['mensaje']);
        $this->assertStringContainsString('requiere 5 bloques pero solo dispone de 2', $error['mensaje']);
    }

    /**
     * Error 4.1: Sección sin aula regular asignada
     */
    public function test_error_seccion_sin_aula_regular(): void
    {
        $anio = $this->crearAnioEscolar();
        // Sección sin aula
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 20, null);

        $docente = $this->crearDocente();
        $area = $this->crearArea('Castellano', 3);
        $this->asignarCarga($docente, $area, $seccion, $grado);

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('seccion_sin_aula_regular', $codigosError);
    }

    /**
     * Error 4.2: Sección con cantidad_actual > capacidad_maxima del aula
     */
    public function test_error_seccion_supera_capacidad_aula(): void
    {
        $anio = $this->crearAnioEscolar();
        // Aula con capacidad 20
        $aulaPequena = $this->crearAula('Aula Pequeña', Aula::TIPO_REGULAR, 20);
        // Sección con 35 alumnos
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 35, $aulaPequena);

        $docente = $this->crearDocente();
        $area = $this->crearArea('Historia', 3);
        $this->asignarCarga($docente, $area, $seccion, $grado);

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('seccion_supera_capacidad_aula', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'seccion_supera_capacidad_aula');
        $this->assertStringContainsString('35 estudiantes, superando la capacidad máxima de 20', $error['mensaje']);
    }

    /**
     * Error 5: Demanda de aula especializada mayor que su oferta
     */
    public function test_error_demanda_aula_especializada_excede_oferta(): void
    {
        // 2 bloques diarios * 5 días = 10 bloques semanales
        $anio = $this->crearAnioEscolar(2);
        $aulaRegular = $this->crearAula();

        // 1 sola aula de tipo 'Laboratorio'
        $lab = $this->crearAula('Lab Química', 'Laboratorio', 30);

        [$grado1, $seccion1] = $this->crearGradoYSeccion('4', 'A', 20, $aulaRegular);
        [$grado2, $seccion2] = $this->crearGradoYSeccion('5', 'A', 20, $aulaRegular);

        $docente = $this->crearDocente();

        // Demanda = 6 + 6 = 12 bloques. Oferta = 10 bloques (1 aula * 10 bloques)
        $quimica1 = $this->crearArea('Prácticas Química 4to', 6, $lab);
        $quimica2 = $this->crearArea('Prácticas Química 5to', 6, $lab);

        $this->asignarCarga($docente, $quimica1, $seccion1, $grado1);
        $this->asignarCarga($docente, $quimica2, $seccion2, $grado2);

        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('demanda_aula_especializada_excedida', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'demanda_aula_especializada_excedida');
        $this->assertStringContainsString("'Laboratorio' (12 bloques) supera la oferta disponible (10 bloques)", $error['mensaje']);
    }

    /**
     * Error 6: Área asignada a una sección pero sin docente asignado
     */
    public function test_error_area_asignada_a_seccion_sin_docente(): void
    {
        $anio = $this->crearAnioEscolar();
        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 20, $aula);

        // El grado tiene 'Geografía' en su plan de estudio curricular
        $areaGeografia = $this->crearArea('Geografía', 3);
        GradoAreaFormacion::create([
            'grado_id' => $grado->id,
            'area_formacion_id' => $areaGeografia->id,
            'status' => 1,
        ]);

        // Pero NO se le asignó docente a la sección A en Carga Académica (DocenteAreaGrado)
        $resultado = $this->service->validar($anio->id);

        $codigosError = collect($resultado['errores'])->pluck('codigo')->all();
        $this->assertContains('area_sin_docente', $codigosError);

        $error = collect($resultado['errores'])->firstWhere('codigo', 'area_sin_docente');
        $this->assertStringContainsString("El área 'Geografía' en la sección A (1° año) no tiene docente asignado", $error['mensaje']);
    }

    /**
     * Avisos e Información útil
     */
    public function test_avisos_e_info_se_generan_correctamente(): void
    {
        // 4 bloques * 5 días = 20 bloques disponibles
        $anio = $this->crearAnioEscolar(4);
        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 20, $aula);

        // Docente con horas = 20 (max 10 bloques). Le asignamos 10 bloques (100% de capacidad -> margen < 10%)
        $docente = $this->crearDocente('Pedro', 'Alvarado', 20);
        $area1 = $this->crearArea('Matemática', 5);
        $area2 = $this->crearArea('Física', 5);

        // Área con bloques_semanales nulos
        $areaNula = $this->crearArea('Orientación', null);

        $this->asignarCarga($docente, $area1, $seccion, $grado);
        $this->asignarCarga($docente, $area2, $seccion, $grado);
        $this->asignarCarga($docente, $areaNula, $seccion, $grado);

        $resultado = $this->service->validar($anio->id);

        $codigosAvisos = collect($resultado['avisos'])->pluck('codigo')->all();

        // 1. Aviso de bloques nulos
        $this->assertContains('area_bloques_nulos', $codigosAvisos);

        // 2. Aviso de huecos en la sección (10 bloques asignados de 20 disponibles)
        $this->assertContains('seccion_con_huecos', $codigosAvisos);

        // 3. Aviso de docente casi saturado (Pedro Alvarado tiene 10 de 10 bloques)
        $this->assertContains('docente_casi_saturado', $codigosAvisos);

        // 4. Info presente
        $codigosInfo = collect($resultado['info'])->pluck('codigo')->all();
        $this->assertContains('resumen_carga_docentes', $codigosInfo);
        $this->assertContains('resumen_carga_secciones', $codigosInfo);
    }

    /**
     * Rendimiento: Validador debe correr en menos de 3 segundos
     */
    public function test_validador_ejecuta_en_menos_de_tres_segundos(): void
    {
        $anio = $this->crearAnioEscolar();
        $aula = $this->crearAula();
        [$grado, $seccion] = $this->crearGradoYSeccion('1', 'A', 20, $aula);

        $docente = $this->crearDocente();
        $area = $this->crearArea('Ciencias', 4);
        $this->asignarCarga($docente, $area, $seccion, $grado);

        $inicio = microtime(true);
        $resultado = $this->service->validar($anio->id);
        $duracion = microtime(true) - $inicio;

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('errores', $resultado);
        $this->assertArrayHasKey('avisos', $resultado);
        $this->assertArrayHasKey('info', $resultado);

        $this->assertLessThan(3.0, $duracion, "El validador tardó {$duracion}s, debe tardar menos de 3 segundos.");
    }
}
