<?php

namespace Tests\Feature;

use App\Livewire\Admin\TransaccionInscripcion\InscripcionProsecucion;
use App\Models\Alumno;
use App\Models\AnioEscolar;
use App\Models\Auditoria;
use App\Models\DetalleDocenteEstudio;
use App\Models\Docente;
use App\Models\DocenteAreaGrado;
use App\Models\Estado;
use App\Models\EstudiosRealizado;
use App\Models\Genero;
use App\Models\Grado;
use App\Models\Inscripcion;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Nota;
use App\Models\Ocupacion;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\Seccion;
use App\Models\Talla;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class ScopingPoliciesTest extends TestCase
{
    use RefreshDatabase;

    protected TipoDocumento $tipoDoc;
    protected Genero $genero;
    protected Pais $pais;
    protected Estado $estado;
    protected Municipio $municipio;
    protected Localidad $localidad;
    protected Ocupacion $ocupacion;
    protected Talla $talla;
    protected AnioEscolar $anioAnterior;
    protected AnioEscolar $anioActual;
    protected Grado $grado1;
    protected Grado $grado2;
    protected Seccion $seccionA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->tipoDoc = TipoDocumento::firstOrCreate(['id' => 1], ['nombre' => 'V', 'status' => 1]);
        $this->genero = Genero::firstOrCreate(['id' => 1], ['genero' => 'M', 'status' => 1]);
        $this->pais = Pais::firstOrCreate(['id' => 1], ['nameES' => 'Venezuela', 'iso2' => 'VE', 'status' => 1]);
        $this->estado = Estado::firstOrCreate(['id' => 1], ['nombre_estado' => 'Lara', 'pais_id' => $this->pais->id, 'status' => 1]);
        $this->municipio = Municipio::firstOrCreate(['id' => 1], ['nombre_municipio' => 'Iribarren', 'estado_id' => $this->estado->id, 'status' => 1]);
        $this->localidad = Localidad::firstOrCreate(['id' => 1], ['nombre_localidad' => 'Catedral', 'municipio_id' => $this->municipio->id, 'status' => 1]);
        $this->ocupacion = Ocupacion::firstOrCreate(['id' => 1], ['nombre_ocupacion' => 'Docente', 'status' => 1]);
        $this->talla = Talla::firstOrCreate(['id' => 1], ['nombre' => '10']);

        $this->anioAnterior = AnioEscolar::create([
            'inicio_anio_escolar' => '2024-09-15',
            'cierre_anio_escolar' => '2025-07-15',
            'status' => 'Inactivo',
        ]);

        $this->anioActual = AnioEscolar::create([
            'inicio_anio_escolar' => '2025-09-15',
            'cierre_anio_escolar' => '2026-07-15',
            'status' => 'Activo',
        ]);

        $this->grado1 = Grado::create([
            'numero_grado' => '1',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => true,
        ]);

        $this->grado2 = Grado::create([
            'numero_grado' => '2',
            'capacidad_max' => 30,
            'min_seccion' => 1,
            'max_seccion' => 3,
            'status' => true,
        ]);

        $this->seccionA = Seccion::create([
            'nombre' => 'A',
            'cantidad_actual' => 0,
            'grado_id' => $this->grado1->id,
            'status' => true,
        ]);
    }

    /**
     * Helpers para crear personas, representantes y alumnos
     */
    protected function crearPersona(string $nombre, string $cedula, string $email): Persona
    {
        return Persona::create([
            'primer_nombre' => $nombre,
            'primer_apellido' => 'Apellido',
            'numero_documento' => $cedula,
            'tipo_documento_id' => $this->tipoDoc->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'email' => $email,
            'fecha_nacimiento' => '1985-05-15',
            'direccion' => 'Calle Principal',
            'telefono' => '04141234567',
            'status' => 1,
        ]);
    }

    protected function crearRepresentante(Persona $persona): Representante
    {
        return Representante::create([
            'persona_id' => $persona->id,
            'pais_id' => $this->pais->id,
            'estado_id' => $this->estado->id,
            'municipio_id' => $this->municipio->id,
            'parroquia_id' => $this->localidad->id,
            'ocupacion_representante' => $this->ocupacion->id,
            'convivenciaestudiante_representante' => 'Si',
            'status' => 1,
        ]);
    }

    protected function crearAlumno(string $nombre, string $cedula, int $peso = 35, float $estatura = 1.40, string $tallaZapato = '34'): Alumno
    {
        $persona = Persona::create([
            'primer_nombre' => $nombre,
            'primer_apellido' => 'Estudiante',
            'numero_documento' => $cedula,
            'tipo_documento_id' => $this->tipoDoc->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'email' => strtolower($nombre) . '@alumno.test',
            'fecha_nacimiento' => '2015-03-20',
            'direccion' => 'Barrio Union',
            'telefono' => '04169876543',
            'status' => 1,
        ]);

        return Alumno::create([
            'persona_id' => $persona->id,
            'peso' => $peso,
            'estatura' => $estatura,
            'talla_zapato' => $tallaZapato,
            'talla_camisa_id' => $this->talla->id,
            'talla_pantalon_id' => $this->talla->id,
            'status' => 'Activo',
        ]);
    }

    protected function asociarAlumnoARepresentante(Alumno $alumno, Representante $rep, ?AnioEscolar $anio = null, ?Seccion $seccion = null): Inscripcion
    {
        $anio = $anio ?? $this->anioAnterior;
        return Inscripcion::create([
            'alumno_id' => $alumno->id,
            'padre_id' => $rep->id,
            'anio_escolar_id' => $anio->id,
            'grado_id' => $this->grado1->id,
            'seccion_id' => $seccion ? $seccion->id : $this->seccionA->id,
            'tipo_inscripcion' => 'prosecucion',
            'status' => 'Activo',
            'acepta_normas_contrato' => true,
        ]);
    }

    /**
     * 1. SEGURIDAD POR URL Y LIVEWIRE (Representantes)
     * - Simula Representante A y Representante B con sus respectivos alumnos.
     * - Representante A recibe 403 al acceder por URL a la ficha del alumno de Representante B.
     * - Representante A recibe 403 al invocar el componente Livewire de prosecución con el alumno B.
     */
    public function test_seguridad_por_url_y_livewire_representantes()
    {
        // Representante A
        $personaRepA = $this->crearPersona('Carlos', '11111111', 'repA@example.com');
        $repA = $this->crearRepresentante($personaRepA);
        $userRepA = User::factory()->create([
            'persona_id' => $personaRepA->id,
            'email' => 'repA@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userRepA->assignRole('Representante');

        $alumnoA = $this->crearAlumno('HijoA', '10111111');
        $this->asociarAlumnoARepresentante($alumnoA, $repA, $this->anioAnterior);

        // Representante B
        $personaRepB = $this->crearPersona('Maria', '22222222', 'repB@example.com');
        $repB = $this->crearRepresentante($personaRepB);
        $userRepB = User::factory()->create([
            'persona_id' => $personaRepB->id,
            'email' => 'repB@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userRepB->assignRole('Representante');

        $alumnoB = $this->crearAlumno('HijoB', '20222222');
        $this->asociarAlumnoARepresentante($alumnoB, $repB, $this->anioAnterior);

        // 1.1 Representante A recibe 403 al intentar acceder a la URL del alumno B
        $response = $this->actingAs($userRepA)->get('/admin/alumnos/' . $alumnoB->id);
        $response->assertStatus(403);

        // 1.2 Representante A recibe 403 al intentar invocar el componente Livewire pasando el ID del alumno B
        Livewire::actingAs($userRepA)
            ->test(InscripcionProsecucion::class)
            ->call('seleccionarAlumno', $alumnoB->id)
            ->assertStatus(403);

        // 1.3 Representante A SÍ puede acceder a la URL de su propio alumno
        $this->actingAs($userRepA)->get('/admin/alumnos/' . $alumnoA->id)->assertOk();
    }

    /**
     * 2. DATOS SENSIBLES (Docente)
     * - Autenticado como usuario que sea SOLO Docente.
     * - Petición a la vista/ficha del alumno.
     * - Verifica con assertDontSee que peso, estatura y talla_zapato NO se renderizan.
     */
    public function test_datos_sensibles_ocultos_para_docente()
    {
        // Crear docente
        $personaDoc = $this->crearPersona('Profesor', '33333333', 'docente@example.com');
        $docente = Docente::create([
            'persona_id' => $personaDoc->id,
            'anio_escolar_id' => $this->anioActual->id,
            'primer_telefono' => '04141112233',
            'status' => true,
        ]);

        $estudio = EstudiosRealizado::create(['estudios' => 'Licenciatura en Educación', 'status' => true]);
        $detalle = DetalleDocenteEstudio::create(['docente_id' => $docente->id, 'estudios_id' => $estudio->id, 'status' => true]);
        DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $detalle->id,
            'grado_id' => $this->grado1->id,
            'seccion_id' => $this->seccionA->id,
            'tipo_asignacion' => 'area',
            'status' => true,
        ]);

        $userDocente = User::factory()->create([
            'persona_id' => $personaDoc->id,
            'email' => 'docente@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userDocente->assignRole('Docente');

        // Alumno inscrito en la sección que atiende el docente
        $alumno = $this->crearAlumno('EstudianteDocente', '30333333', peso: 42, estatura: 1.55, tallaZapato: '38');
        $personaRep = $this->crearPersona('PadreFamilia', '44444444', 'padre@example.com');
        $rep = $this->crearRepresentante($personaRep);
        $this->asociarAlumnoARepresentante($alumno, $rep, $this->anioActual, $this->seccionA);

        // Verificar que el usuario es SOLO Docente
        $this->assertTrue($userDocente->esSoloDocente());

        // Petición a la vista de la ficha del alumno
        $response = $this->actingAs($userDocente)->get('/admin/alumnos/' . $alumno->id);
        $response->assertOk();

        // Confirmar que los datos sensibles NO se renderizan
        $response->assertDontSee('peso', false);
        $response->assertDontSee('estatura', false);
        $response->assertDontSee('talla_zapato', false);
        $response->assertDontSee('42 kg');
        $response->assertDontSee('1.55 cm');
    }

    /**
     * 3. CONFLICTO DE INTERESES (RNF-20)
     * - Autenticado como docente-representante@example.com.
     * - Intento de actualizar nota de su propio representado vía Gate::allows('update', $nota).
     * - Devuelve false y genera registro en auditorias con accion 'acceso_denegado' y motivo conflicto de intereses.
     */
    public function test_conflicto_de_intereses_rnf20()
    {
        // Usuario con ambos roles: Docente y Representante
        $personaDoble = $this->crearPersona('DocentePadre', '55555555', 'docente-representante@example.com');

        $docente = Docente::create([
            'persona_id' => $personaDoble->id,
            'anio_escolar_id' => $this->anioActual->id,
            'primer_telefono' => '04145556677',
            'status' => true,
        ]);

        $rep = $this->crearRepresentante($personaDoble);

        $userDoble = User::factory()->create([
            'persona_id' => $personaDoble->id,
            'email' => 'docente-representante@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userDoble->assignRole('Docente');
        $userDoble->assignRole('Representante');

        // Alumno que es hijo del docente-representante
        $alumnoHijo = $this->crearAlumno('HijoDocente', '50555555');
        $this->asociarAlumnoARepresentante($alumnoHijo, $rep, $this->anioActual, $this->seccionA);

        // Asignar al docente la sección
        $estudio = EstudiosRealizado::create(['estudios' => 'Docencia Integral', 'status' => true]);
        $detalle = DetalleDocenteEstudio::create(['docente_id' => $docente->id, 'estudios_id' => $estudio->id, 'status' => true]);
        DocenteAreaGrado::create([
            'docente_estudio_realizado_id' => $detalle->id,
            'grado_id' => $this->grado1->id,
            'seccion_id' => $this->seccionA->id,
            'tipo_asignacion' => 'area',
            'status' => true,
        ]);

        // Autenticar como docente-representante
        $this->actingAs($userDoble);

        // Crear instancia de nota vinculada al hijo
        $nota = new Nota();
        $nota->alumno_id = $alumnoHijo->id;
        $nota->setRelation('alumno', $alumnoHijo);

        $auditoriasAntes = Auditoria::where('accion', 'acceso_denegado')->count();

        // Evaluar Gate update
        $puedeActualizar = Gate::forUser($userDoble)->allows('update', $nota);

        $this->assertFalse($puedeActualizar, 'El docente-representante no debe poder modificar notas de su representado.');

        // Verificar que se haya registrado en auditorias
        $auditoria = Auditoria::where('accion', 'acceso_denegado')->latest('id')->first();
        $this->assertNotNull($auditoria, 'Debe existir un registro de auditoria por acceso denegado.');
        $this->assertStringContainsString(
            'conflicto de intereses',
            $auditoria->descripcion . json_encode($auditoria->valores_nuevos),
            'El registro de auditoría debe indicar conflicto de intereses.'
        );
        $this->assertGreaterThan($auditoriasAntes, Auditoria::where('accion', 'acceso_denegado')->count());
    }

    /**
     * 4. REGRESIÓN FUNCIONAL: FLUJO DE PROSECUCIÓN (CRÍTICO)
     * - Autenticado como Representante.
     * - GET /portal-representante/prosecucion devuelve 200.
     * - Livewire::test(InscripcionProsecucion::class) monta sin errores.
     * - seleccionarAlumno($alumno) tiene éxito y carga al representado.
     */
    public function test_regresion_funcional_flujo_de_prosecucion()
    {
        // Representante con alumno con inscripción previa en el año anterior
        $personaRep = $this->crearPersona('Ana', '66666666', 'ana.rep@example.com');
        $rep = $this->crearRepresentante($personaRep);

        $userRep = User::factory()->create([
            'persona_id' => $personaRep->id,
            'email' => 'ana.rep@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userRep->assignRole('Representante');

        $alumno = $this->crearAlumno('Pedro', '60666666');
        $this->asociarAlumnoARepresentante($alumno, $rep, $this->anioAnterior);

        // 4.1 GET a la ruta del portal de prosecución devuelve 200
        $response = $this->actingAs($userRep)->get('/portal-representante/prosecucion');
        $response->assertStatus(200);

        // 4.2 Montar componente Livewire de prosecución
        $componente = Livewire::actingAs($userRep)
            ->test(InscripcionProsecucion::class)
            ->assertSuccessful();

        // 4.3 Seleccionar alumno del representante
        $componente->call('seleccionarAlumno', $alumno->id)
            ->assertSuccessful()
            ->assertSet('alumnoId', $alumno->id);

        $this->assertNotNull($componente->get('alumnoSeleccionado'));
        $this->assertEquals($alumno->id, $componente->get('alumnoSeleccionado')->id);
    }
}
