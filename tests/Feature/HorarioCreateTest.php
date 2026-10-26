<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\Aula;
use App\Models\ConfiguracionHorario;
use App\Models\DetalleDocenteEstudio;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\Grado;
use App\Models\Seccion;
use App\Models\User;
use App\Models\seccion_aula;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HorarioCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);
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

    /**
     * P6.5: Verifica que un usuario es redirigido a configuracion si intenta entrar a create sin configuracion_horario.
     */
    public function test_create_horario_redirige_si_no_existe_configuracion_horario(): void
    {
        $admin = $this->crearAdmin();

        // Año escolar activo sin registro en configuracion_horarios
        AnioEscolar::create([
            'inicio_anio_escolar' => '2026-09-15',
            'cierre_anio_escolar' => '2027-07-15',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.horario.create'));

        $response->assertRedirect(route('admin.horario.configuracion'));
        $response->assertSessionHas('warning', 'Debes configurar el marco horario (bloques y recesos) antes de crear un horario.');
    }

    /**
     * P6.5: Verifica que si hay errores críticos en el validador, redirige al dashboard de horarios.
     */
    public function test_create_horario_redirige_a_index_si_hay_errores_criticos_de_validacion(): void
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
            'numero_bloques' => 8,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        // Crear una sección sin aula regular asignada (produce error crítico de validación)
        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 35,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);

        Seccion::create([
            'nombre' => 'A',
            'grado_id' => $grado->id,
            'cantidad_actual' => 25,
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.horario.create'));

        $response->assertRedirect(route('admin.horario.index'));
        $response->assertSessionHas('warning', 'Hay errores en la carga académica que impiden generar el horario. Por favor, revisa el diagnóstico.');
        $response->assertSessionHas('errores_validacion');
    }

    /**
     * P6.5 & Subquery test: Carga sin error 500 y muestra el banner verde cuando los datos son válidos.
     */
    public function test_create_horario_carga_exitosamente_cuando_cumple_prerrequisitos_y_no_muestra_boton_validar(): void
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
            'numero_bloques' => 8,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

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
        $estudioId1 = \DB::table('estudios_realizados')->insertGetId([
            'estudios' => 'Licenciatura en Educación',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $estudioId2 = \DB::table('estudios_realizados')->insertGetId([
            'estudios' => 'Magíster en Matemáticas',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personaId = \DB::table('personas')->insertGetId([
            'primer_nombre' => 'Profesor',
            'primer_apellido' => 'MultiEstudios',
            'numero_documento' => 'V99887766',
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

        // Crear 2 registros en detalle_docente_estudios para el mismo docente (Simula caso Docente ID 14)
        $dde1 = DetalleDocenteEstudio::create([
            'docente_id' => $docente->id,
            'estudios_id' => $estudioId1,
            'status' => 1,
        ]);
        $dde2 = DetalleDocenteEstudio::create([
            'docente_id' => $docente->id,
            'estudios_id' => $estudioId2,
            'status' => 1,
        ]);

        $grado = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 35,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => 1,
        ]);
        $seccion = Seccion::create([
            'nombre' => 'A',
            'grado_id' => $grado->id,
            'cantidad_actual' => 25,
            'status' => 1,
        ]);

        // Asignar aula regular activa con capacidad suficiente
        $aula = Aula::create([
            'nombre_aula' => 'Aula 101',
            'tipo_aula' => Aula::TIPO_REGULAR,
            'capacidad_maxima' => 35,
            'status' => 1,
        ]);

        seccion_aula::create([
            'id_seccion' => $seccion->id,
            'id_aula' => $aula->id_aula,
            'status' => 1,
        ]);

        $area = AreaFormacion::create([
            'nombre_area_formacion' => 'Matemática',
            'codigo_area' => 'MAT-1',
            'siglas' => 'MAT',
            'bloques_semanales' => 4,
            'status' => 1,
        ]);
        $aer = AreaEstudioRealizado::create([
            'area_formacion_id' => $area->id,
            'estudios_id' => $estudioId1,
            'status' => 1,
        ]);

        DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $dde1->id,
            'area_estudio_realizado_id' => $aer->id,
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'status' => 1,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.horario.create'));

        $response->assertOk();
        $response->assertSee('Crear Horario');
        // Banner verde de validación
        $response->assertSee('Configuración validada correctamente. Puedes proceder a generar el horario.');
        // Botón independiente de validar ya no debe existir
        $response->assertDontSee('id="btnValidarHorario"', false);
        // Checkbox de forzar generación ya no debe existir
        $response->assertDontSee('checkForzarGeneracion');

        $response->assertViewHas('docentes', function ($docentes) use ($docente) {
            $docenteItem = $docentes->firstWhere('id', $docente->id);
            return $docenteItem !== null && count($docenteItem['secciones']) >= 1;
        });
    }

    /**
     * UX: Verifica que en el index el botón "+ Nuevo Horario" aparece deshabilitado
     * con el mensaje explicativo si hay errores críticos en la carga académica.
     */
    public function test_index_horario_muestra_boton_deshabilitado_con_explicacion_si_hay_errores_criticos(): void
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
            'numero_bloques' => 8,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        // Crear una sección sin aula (error crítico)
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 3, 'status' => 1]);
        Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.horario.index'));

        $response->assertOk();
        // Botón deshabilitado presente
        $response->assertSee('disabled', false);
        // Explicación visible bajo el botón
        $response->assertSee('No puedes crear un horario porque hay errores en la carga académica.');
        $response->assertSee('Corrige los errores en el panel inferior para habilitar este botón.');
        // Panel de errores visible
        $response->assertSee('Creación de Horarios Bloqueada');
        // Botón para ver detalles de los errores (Bootstrap collapse)
        $response->assertSee('Ver detalles de los errores');
        $response->assertSee('data-bs-toggle="collapse"', false);
        $response->assertSee('data-bs-target="#collapseErrores"', false);
        // Contenedor colapsable (oculto por defecto, sin la clase 'show')
        $response->assertSee('class="collapse mt-3" id="collapseErrores"', false);
        $response->assertDontSee('class="collapse mt-3 show" id="collapseErrores"', false);
        $response->assertSee('Errores críticos que deben solucionarse para habilitar la creación:');
    }

    /**
     * UX: Verifica que en el index el botón "+ Nuevo Horario" está habilitado si todo es válido.
     */
    public function test_index_horario_muestra_boton_habilitado_cuando_no_hay_errores_criticos(): void
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
            'numero_bloques' => 8,
            'duracion_bloque_min' => 90,
            'minutos_entre_bloques' => 0,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.horario.index'));

        $response->assertOk();
        // El enlace a create está activo
        $response->assertSee(route('admin.horario.create'), false);
        $response->assertDontSee('No puedes crear un horario porque hay errores en la carga académica.');
    }
}
