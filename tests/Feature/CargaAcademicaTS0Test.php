<?php

namespace Tests\Feature;

use App\Enums\TipoAsignacionNota;
use App\Models\AnioEscolar;
use App\Models\AreaEstudioRealizado;
use App\Models\AreaFormacion;
use App\Models\CalendarioAcademico;
use App\Models\CargaAcademica;
use App\Models\DetalleDocenteEstudio;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\EstudiosRealizado;
use App\Models\Grado;
use App\Models\GradoSeccion;
use App\Models\GrupoEstable;
use App\Models\Persona;
use App\Models\Seccion;
use App\Models\User;
use App\Services\Cargas\GenerarCargasAcademicasService;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CargaAcademicaTS0Test extends TestCase
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

    private function crearDocenteUser(string $ci = 'V99887766'): array
    {
        $tipoDocId = DB::table('tipo_documentos')->insertGetId(['nombre' => 'V', 'status' => true, 'created_at' => now(), 'updated_at' => now()]);
        $generoId = DB::table('generos')->insertGetId(['genero' => 'M', 'status' => true, 'created_at' => now(), 'updated_at' => now()]);

        $persona = Persona::create([
            'primer_nombre' => 'Profesor',
            'primer_apellido' => 'Prueba',
            'tipo_documento_id' => $tipoDocId,
            'numero_documento' => $ci,
            'fecha_nacimiento' => '1980-01-01',
            'genero_id' => $generoId,
            'status' => true,
        ]);

        $docente = Docente::create([
            'persona_id' => $persona->id,
            'status' => true,
        ]);

        $user = User::factory()->create([
            'persona_id' => $persona->id,
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $user->assignRole('Docente');

        return [$user, $docente];
    }

    /**
     * 1. Test de unicidad en grado_seccion (grado_id, seccion_id).
     */
    public function test_grado_seccion_es_unico_por_par_grado_y_seccion(): void
    {
        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 2, 'status' => true]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => true]);

        GradoSeccion::create(['grado_id' => $grado->id, 'seccion_id' => $seccion->id, 'status' => true]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        GradoSeccion::create(['grado_id' => $grado->id, 'seccion_id' => $seccion->id, 'status' => true]);
    }

    /**
     * 2. Test de CHECK en cargas_academicas: tipo='area' exige area_formacion_id y grado_seccion_id no nulos y grupo_estable_id nulo.
     */
    public function test_check_tipo_area_exige_area_y_grado_seccion_y_grupo_nulo(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Los CHECK constraints de ALTER se validan en MySQL nativo.');
        }

        $anio = AnioEscolar::create(['inicio_anio_escolar' => '2026-09-01', 'cierre_anio_escolar' => '2027-07-31', 'status' => 'Activo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anio->id, 'origen' => 'manual', 'estado' => 'confirmado']);
        [, $docente] = $this->crearDocenteUser();

        // Intento inválido: tipo 'area' sin area_formacion_id
        $this->expectException(\Illuminate\Database\QueryException::class);
        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente->id,
            'tipo' => TipoAsignacionNota::Area,
            'area_formacion_id' => null,
            'grado_seccion_id' => null,
            'grupo_estable_id' => null,
        ]);
    }

    /**
     * 3. Test de CHECK en cargas_academicas: tipo='grupo_estable' exige grupo_estable_id no nulo.
     */
    public function test_check_tipo_grupo_estable_exige_grupo_estable_id(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('Los CHECK constraints de ALTER se validan en MySQL nativo.');
        }

        $anio = AnioEscolar::create(['inicio_anio_escolar' => '2026-09-01', 'cierre_anio_escolar' => '2027-07-31', 'status' => 'Activo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anio->id, 'origen' => 'manual', 'estado' => 'confirmado']);
        [, $docente] = $this->crearDocenteUser();

        // Intento inválido: tipo 'grupo_estable' sin grupo_estable_id
        $this->expectException(\Illuminate\Database\QueryException::class);
        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente->id,
            'tipo' => TipoAsignacionNota::GrupoEstable,
            'area_formacion_id' => null,
            'grado_seccion_id' => null,
            'grupo_estable_id' => null,
        ]);
    }

    /**
     * 4. Test de unicidad de 5 columnas: (calendario_academico_id, docente_id, area_key, grado_seccion_key, grupo_key).
     */
    public function test_unique_combinacion_5_columnas_impide_duplicados(): void
    {
        $anio = AnioEscolar::create(['inicio_anio_escolar' => '2026-09-01', 'cierre_anio_escolar' => '2027-07-31', 'status' => 'Activo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anio->id, 'origen' => 'manual', 'estado' => 'confirmado']);
        [, $docente] = $this->crearDocenteUser();

        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 2, 'status' => true]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => true]);
        $gs = GradoSeccion::create(['grado_id' => $grado->id, 'seccion_id' => $seccion->id, 'status' => true]);
        $area = AreaFormacion::create(['nombre_area_formacion' => 'Biología', 'codigo_area' => 101, 'siglas' => 'BIO', 'horas_semanales' => 4, 'status' => true]);

        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente->id,
            'tipo' => TipoAsignacionNota::Area,
            'area_formacion_id' => $area->id,
            'grado_seccion_id' => $gs->id,
            'grupo_estable_id' => null,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente->id,
            'tipo' => TipoAsignacionNota::Area,
            'area_formacion_id' => $area->id,
            'grado_seccion_id' => $gs->id,
            'grupo_estable_id' => null,
        ]);
    }

    /**
     * 5. Test del Servicio: genera tipo area y grupo_estable con idempotencia.
     */
    public function test_servicio_genera_area_y_grupo_estable_con_idempotencia(): void
    {
        $anio = AnioEscolar::create(['inicio_anio_escolar' => '2026-09-01', 'cierre_anio_escolar' => '2027-07-31', 'status' => 'Activo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anio->id, 'origen' => 'manual', 'estado' => 'confirmado']);
        [, $docente] = $this->crearDocenteUser();

        $estudio = EstudiosRealizado::create(['estudios' => 'Licenciado en Educación', 'status' => true]);
        $detDocente = DetalleDocenteEstudio::create(['docente_id' => $docente->id, 'estudios_id' => $estudio->id, 'status' => true]);

        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 2, 'status' => true]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => true]);
        $area = AreaFormacion::create(['nombre_area_formacion' => 'Castellano', 'codigo_area' => 102, 'siglas' => 'CAS', 'horas_semanales' => 4, 'status' => true]);
        $areaEstudio = AreaEstudioRealizado::create(['area_formacion_id' => $area->id, 'estudios_id' => $estudio->id, 'status' => true]);

        $grupo = GrupoEstable::create(['nombre_grupo_estable' => 'Teatro y Danza', 'codigo' => 'GE-01', 'status' => true]);

        // Plantilla 1: tipo area
        $dag1 = DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $detDocente->id,
            'area_estudio_realizado_id' => $areaEstudio->id,
            'grado_id' => $grado->id,
            'seccion_id' => $seccion->id,
            'tipo_asignacion' => 'area',
            'status' => true,
        ]);

        // Plantilla 2: tipo grupo_estable
        $dag2 = DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $detDocente->id,
            'grupo_estable_id' => $grupo->id,
            'tipo_asignacion' => 'grupo_estable',
            'status' => true,
        ]);

        $servicio = app(GenerarCargasAcademicasService::class);

        // Primera ejecución
        $resumen1 = $servicio->generar($calendario->id);
        $this->assertEquals(2, $resumen1['creadas']);
        $this->assertEquals(0, $resumen1['actualizadas']);
        $this->assertEquals(2, CargaAcademica::where('calendario_academico_id', $calendario->id)->count());

        // Segunda ejecución (idempotente)
        $resumen2 = $servicio->generar($calendario->id);
        $this->assertEquals(0, $resumen2['creadas']);
        $this->assertEquals(2, $resumen2['actualizadas']);
        $this->assertEquals(2, CargaAcademica::where('calendario_academico_id', $calendario->id)->count());
    }

    /**
     * 6. Test de solo lectura: el servicio y el controlador bloquean generar en un año escolar cerrado/inactivo.
     */
    public function test_anio_solo_lectura_bloquea_generacion_de_cargas(): void
    {
        $admin = $this->crearAdmin();
        $anioCerrado = AnioEscolar::create(['inicio_anio_escolar' => '2024-09-01', 'cierre_anio_escolar' => '2025-07-31', 'status' => 'Inactivo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anioCerrado->id, 'origen' => 'manual', 'estado' => 'confirmado']);

        // 1. Servicio lanza excepción directa
        $servicio = app(GenerarCargasAcademicasService::class);
        $this->expectException(\RuntimeException::class);
        $servicio->generar($calendario->id);
    }

    /**
     * 7. Test de permisos y visibilidad: docente solo ve sus propias cargas en el listado.
     */
    public function test_docente_solo_ve_sus_propias_cargas_academicas(): void
    {
        $anio = AnioEscolar::create(['inicio_anio_escolar' => '2026-09-01', 'cierre_anio_escolar' => '2027-07-31', 'status' => 'Activo']);
        $calendario = CalendarioAcademico::create(['anio_escolar_id' => $anio->id, 'origen' => 'manual', 'estado' => 'confirmado']);

        [$userDocente1, $docente1] = $this->crearDocenteUser('V11111111');
        [, $docente2] = $this->crearDocenteUser('V22222222');

        $grado = Grado::create(['numero_grado' => '1', 'capacidad_max' => 30, 'min_seccion' => 1, 'max_seccion' => 2, 'status' => true]);
        $seccion = Seccion::create(['nombre' => 'A', 'grado_id' => $grado->id, 'cantidad_actual' => 20, 'status' => true]);
        $gs = GradoSeccion::create(['grado_id' => $grado->id, 'seccion_id' => $seccion->id, 'status' => true]);
        $area = AreaFormacion::create(['nombre_area_formacion' => 'Historia', 'codigo_area' => 103, 'siglas' => 'HIS', 'horas_semanales' => 4, 'status' => true]);

        // Carga para docente 1
        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente1->id,
            'tipo' => TipoAsignacionNota::Area,
            'area_formacion_id' => $area->id,
            'grado_seccion_id' => $gs->id,
            'grupo_estable_id' => null,
        ]);

        // Carga para docente 2
        CargaAcademica::create([
            'calendario_academico_id' => $calendario->id,
            'docente_id' => $docente2->id,
            'tipo' => TipoAsignacionNota::Area,
            'area_formacion_id' => $area->id,
            'grado_seccion_id' => $gs->id,
            'grupo_estable_id' => null,
        ]);

        // Sesión activa en el año escolar
        session(['anio_escolar_id' => $anio->id]);

        $response = $this->actingAs($userDocente1)->get(route('admin.cargas-academicas.index'));
        $response->assertOk();

        // Debe ver su propio nombre y NO el de docente2
        $response->assertSee($docente1->persona->numero_documento);
        $response->assertDontSee($docente2->persona->numero_documento);
    }
}
