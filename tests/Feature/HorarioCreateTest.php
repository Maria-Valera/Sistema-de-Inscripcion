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

    public function test_create_horario_carga_sin_error_cuando_docente_tiene_multiples_estudios(): void
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');

        // Año escolar activo
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

        // Al acceder a /admin/horario/create no debe arrojar error 500 por subquery returns more than 1 row
        $response = $this->actingAs($admin)->get(route('admin.horario.create'));

        $response->assertOk();
        $response->assertSee('Crear Horario');
        $response->assertSee('Validar');
        $response->assertViewHas('docentes', function ($docentes) use ($docente) {
            $docenteItem = $docentes->firstWhere('id', $docente->id);
            return $docenteItem !== null && count($docenteItem['secciones']) >= 1;
        });
    }
}
