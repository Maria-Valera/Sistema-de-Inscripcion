<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\Docente;
use App\Models\Estado;
use App\Models\Genero;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Ocupacion;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\Representante;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteRepresentanteTest extends TestCase
{
    use RefreshDatabase;

    protected TipoDocumento $tipoV;
    protected Genero $genero;
    protected Localidad $localidad;
    protected PrefijoTelefono $prefijo;
    protected AnioEscolar $anioEscolar;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->tipoV = TipoDocumento::firstOrCreate(
            ['id' => 1],
            ['nombre' => 'V', 'status' => 1]
        );

        $this->genero = Genero::firstOrCreate(
            ['id' => 1],
            ['genero' => 'Femenino', 'status' => 1]
        );

        $pais = Pais::firstOrCreate(
            ['id' => 1],
            ['nameES' => 'Venezuela', 'iso2' => 'VE', 'status' => 1]
        );

        $estado = Estado::firstOrCreate(
            ['id' => 1],
            ['nombre_estado' => 'Lara', 'pais_id' => $pais->id, 'status' => 1]
        );

        $municipio = Municipio::firstOrCreate(
            ['id' => 1],
            ['nombre_municipio' => 'Iribarren', 'estado_id' => $estado->id, 'status' => 1]
        );

        $this->localidad = Localidad::firstOrCreate(
            ['id' => 1],
            ['nombre_localidad' => 'Catedral', 'municipio_id' => $municipio->id, 'status' => 1]
        );

        $this->prefijo = PrefijoTelefono::firstOrCreate(
            ['id' => 1],
            ['prefijo' => '0412', 'status' => 1]
        );

        $this->anioEscolar = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(1)->format('Y-m-d'),
            'cierre_anio_escolar' => now()->addMonths(11)->format('Y-m-d'),
            'status' => 'Activo',
        ]);

        $personaAdmin = Persona::create([
            'primer_nombre' => 'Super',
            'primer_apellido' => 'Admin',
            'numero_documento' => '99999999',
            'fecha_nacimiento' => '1985-01-01',
            'tipo_documento_id' => $this->tipoV->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'email' => 'admin_test@example.com',
            'status' => 1,
        ]);

        $this->adminUser = User::create([
            'name' => 'Super Admin',
            'persona_id' => $personaAdmin->id,
            'email' => 'admin_test@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'password' => bcrypt('Password123!'),
        ]);

        $this->adminUser->assignRole('Admin');
    }

    public function test_reutilizacion_de_persona_id_entre_representante_y_docente(): void
    {
        $cedula = '12345678';

        // 1. Crear un Representante con la cédula "V-12345678"
        $ocupacion = Ocupacion::firstOrCreate(
            ['nombre_ocupacion' => 'Educador'],
            ['status' => 1]
        );

        $persona = Persona::create([
            'primer_nombre' => 'Carla',
            'primer_apellido' => 'Mendoza',
            'numero_documento' => $cedula,
            'fecha_nacimiento' => '1988-05-10',
            'tipo_documento_id' => $this->tipoV->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'prefijo_id' => $this->prefijo->id,
            'telefono' => '1234567',
            'email' => 'carla.mendoza@example.com',
            'direccion' => 'Calle 10 entre 15 y 16',
            'status' => 1,
        ]);

        $representante = Representante::create([
            'persona_id' => $persona->id,
            'pais_id' => 1,
            'estado_id' => 1,
            'municipio_id' => 1,
            'parroquia_id' => 1,
            'ocupacion_representante' => $ocupacion->id,
            'convivenciaestudiante_representante' => 'Si',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('personas', [
            'id' => $persona->id,
            'numero_documento' => $cedula,
        ]);
        $this->assertDatabaseHas('representantes', [
            'id' => $representante->id,
            'persona_id' => $persona->id,
        ]);

        // 2. Ve al formulario de Crear Docente y crea un Docente con la misma cédula
        $response = $this->actingAs($this->adminUser)->post(route('admin.docente.store'), [
            'tipo_documento_id' => $this->tipoV->id,
            'numero_documento' => $cedula,
            'primer_nombre' => 'Carla',
            'segundo_nombre' => null,
            'tercer_nombre' => null,
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => null,
            'genero' => $this->genero->id,
            'fecha_nacimiento' => '1988-05-10',
            'correo' => 'carla.mendoza@example.com',
            'direccion' => 'Calle 10 entre 15 y 16',
            'prefijo_id' => $this->prefijo->id,
            'primer_telefono' => '1234567',
            'codigo' => '12345',
            'dependencia' => 'Departamento de Ciencias',
            'horas_academicas' => 12,
        ]);

        // 3. El sistema debe permitirlo y redirigir
        $response->assertRedirect();

        // 4. En la base de datos solo debe haber:
        //    - 1 registro en personas
        //    - 1 registro en representantes
        //    - 1 registro en docentes
        //    - Ambos registros deben compartir el mismo persona_id
        $this->assertEquals(2, Persona::count()); // 1 admin + 1 Carla
        $this->assertEquals(1, Persona::where('numero_documento', $cedula)->count());

        $this->assertEquals(1, Representante::where('persona_id', $persona->id)->count());
        $this->assertEquals(1, Docente::where('persona_id', $persona->id)->count());

        $docenteCreado = Docente::where('persona_id', $persona->id)->first();
        $this->assertNotNull($docenteCreado);
        $this->assertEquals($persona->id, $docenteCreado->persona_id);
        $this->assertEquals($representante->persona_id, $docenteCreado->persona_id);
    }

    public function test_no_permite_duplicar_docente_si_ya_esta_registrado(): void
    {
        $cedula = '87654321';

        $persona = Persona::create([
            'primer_nombre' => 'Marcos',
            'primer_apellido' => 'Perez',
            'numero_documento' => $cedula,
            'fecha_nacimiento' => '1985-02-15',
            'tipo_documento_id' => $this->tipoV->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'status' => 1,
        ]);

        Docente::create([
            'anio_escolar_id' => $this->anioEscolar->id,
            'codigo' => '999',
            'dependencia' => 'Historia',
            'persona_id' => $persona->id,
            'horas_academicas' => 10,
            'status' => true,
        ]);

        // Intentar registrar el mismo docente
        $response = $this->actingAs($this->adminUser)->post(route('admin.docente.store'), [
            'tipo_documento_id' => $this->tipoV->id,
            'numero_documento' => $cedula,
            'primer_nombre' => 'Marcos',
            'primer_apellido' => 'Perez',
            'genero' => $this->genero->id,
            'fecha_nacimiento' => '1985-02-15',
            'dependencia' => 'Historia',
            'horas_academicas' => 10,
        ]);

        $response->assertSessionHas('error', 'Este docente ya está registrado.');
        $this->assertEquals(1, Docente::where('persona_id', $persona->id)->count());
    }
}
