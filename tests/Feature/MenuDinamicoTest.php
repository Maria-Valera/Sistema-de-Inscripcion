<?php

namespace Tests\Feature;

use App\Models\AnioEscolar;
use App\Models\Estado;
use App\Models\Genero;
use App\Models\Localidad;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Persona;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MenuDinamicoTest extends TestCase
{
    use RefreshDatabase;

    protected TipoDocumento $tipoV;
    protected Genero $genero;
    protected Localidad $localidad;
    protected AnioEscolar $anioEscolar;

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

        $this->anioEscolar = AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonths(1)->format('Y-m-d'),
            'cierre_anio_escolar' => now()->addMonths(11)->format('Y-m-d'),
            'status' => 'Activo',
        ]);
    }

    private function crearUsuario(string $nombre, string $email, string $rol): User
    {
        $persona = Persona::create([
            'primer_nombre' => $nombre,
            'primer_apellido' => 'Test',
            'numero_documento' => (string) rand(10000000, 99999999),
            'fecha_nacimiento' => '1990-01-01',
            'tipo_documento_id' => $this->tipoV->id,
            'genero_id' => $this->genero->id,
            'localidad_id' => $this->localidad->id,
            'email' => $email,
            'status' => 1,
        ]);

        $user = User::create([
            'name' => $nombre,
            'persona_id' => $persona->id,
            'email' => $email,
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'rol_principal' => $rol,
            'password' => bcrypt('Password123!'),
        ]);

        $user->assignRole($rol);

        return $user;
    }

    /**
     * Un Representante NO ve en el HTML la opción de menú "Alumnos" ni "Horarios" de admin,
     * ni headers huérfanos.
     * Un Representante SÍ ve la opción "Mi Carnet" y el header "PORTAL DEL REPRESENTANTE".
     */
    public function test_representante_ve_solo_sus_modulos_y_no_ve_alumnos(): void
    {
        $representante = $this->crearUsuario('Representante User', 'rep@test.com', 'Representante');

        $response = $this->actingAs($representante)->get(route('portal-representante.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Alumnos');
        $response->assertDontSee('Horarios');
        $response->assertDontSee('INSCRIPCIONES');
        $response->assertDontSee('ASISTENCIA');
        $response->assertDontSee('INFORMACION ACADÉMICA');
        $response->assertDontSee('GESTIÓN DE DOCENTES');
        $response->assertDontSee('CONFIGURACIÓN GENERAL');
        $response->assertDontSee('SEGURIDAD Y ACCESOS');
        $response->assertSee('PORTAL DEL REPRESENTANTE');
        $response->assertSee('Mi Carnet');
        $response->assertSee('Inscripción por Prosecución');
    }

    /**
     * Un Docente ve "Horarios" pero no ve "Gestión de Usuarios".
     */
    public function test_docente_ve_horarios_pero_no_usuarios_y_accesos(): void
    {
        $docente = $this->crearUsuario('Docente User', 'docente@test.com', 'Docente');

        $response = $this->actingAs($docente)->get(route('dashboard.docente'));

        $response->assertStatus(200);
        $response->assertSee('Horarios');
        $response->assertDontSee('Gestión de Usuarios');
    }

    /**
     * Un Admin ve todo (módulos clave del sistema).
     */
    public function test_admin_ve_todos_los_modulos(): void
    {
        $admin = $this->crearUsuario('Admin User', 'admin@test.com', 'Admin');

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Alumnos');
        $response->assertSee('Gestión de Usuarios');
        $response->assertSee('Horarios');
        $response->assertSee('Calendario Escolar');
    }

    /**
     * Un usuario con un módulo extra asignado lo ve en su menú.
     */
    public function test_usuario_con_modulo_extra_asignado_lo_ve_en_su_menu(): void
    {
        $representante = $this->crearUsuario('Rep Extra User', 'rep_extra@test.com', 'Representante');

        // Inicialmente el representante no tiene el módulo de Aulas
        $responseInicial = $this->actingAs($representante)->get(route('portal-representante.index'));
        $responseInicial->assertDontSee('Aulas');

        // Asignamos el módulo extra "aulas" (permiso aulas.ver)
        $permisoAulas = Permission::where('name', 'aulas.ver')->first();
        $this->assertNotNull($permisoAulas);
        $representante->givePermissionTo($permisoAulas);

        // Limpiamos permisos en caché para el usuario
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Ahora al cargar la página, debe aparecer el botón "Aulas"
        $responseConExtra = $this->actingAs($representante->fresh())->get(route('portal-representante.index'));
        $responseConExtra->assertSee('Aulas');
    }
}
