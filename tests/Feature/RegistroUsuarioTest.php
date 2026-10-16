<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\TipoDocumento;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistroUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected TipoDocumento $tipoV;
    protected int $generoId;
    protected int $paisId;
    protected int $estadoId;
    protected int $municipioId;
    protected int $localidadId;
    protected int $ocupacionId;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles básicos requeridos
        Role::firstOrCreate(['name' => 'Representante', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']);

        // Tipo de documento de prueba
        $this->tipoV = TipoDocumento::firstOrCreate(
            ['id' => 1],
            ['nombre' => 'V', 'status' => 1]
        );

        // Genero de prueba
        $genero = \App\Models\Genero::firstOrCreate(
            ['id' => 1],
            ['genero' => 'M', 'status' => 1]
        );
        $this->generoId = $genero->id;

        // Catálogos para Representante
        $pais = \App\Models\Pais::firstOrCreate(
            ['id' => 1],
            ['nameES' => 'Venezuela', 'iso2' => 'VE', 'status' => 1]
        );
        $this->paisId = $pais->id;

        $estado = \App\Models\Estado::firstOrCreate(
            ['id' => 1],
            ['nombre_estado' => 'Lara', 'pais_id' => $this->paisId, 'status' => 1]
        );
        $this->estadoId = $estado->id;

        $municipio = \App\Models\Municipio::firstOrCreate(
            ['id' => 1],
            ['nombre_municipio' => 'Iribarren', 'estado_id' => $this->estadoId, 'status' => 1]
        );
        $this->municipioId = $municipio->id;

        $localidad = \App\Models\Localidad::firstOrCreate(
            ['id' => 1],
            ['nombre_localidad' => 'Catedral', 'municipio_id' => $this->municipioId, 'status' => 1]
        );
        $this->localidadId = $localidad->id;

        $ocupacion = \App\Models\Ocupacion::firstOrCreate(
            ['id' => 1],
            ['nombre_ocupacion' => 'Comerciante', 'status' => 1]
        );
        $this->ocupacionId = $ocupacion->id;
    }

    /**
     * Helper para crear una Persona base
     */
    protected function crearPersona(array $atributos = []): Persona
    {
        return Persona::create(array_merge([
            'primer_nombre'     => 'Carlos',
            'primer_apellido'   => 'Perez',
            'numero_documento'  => '12345678',
            'tipo_documento_id' => $this->tipoV->id,
            'genero_id'         => $this->generoId,
            'status'            => 1,
            'email'             => 'carlos@example.com',
            'fecha_nacimiento'  => '1990-01-01',
            'direccion'         => 'Calle Principal',
            'telefono'          => '12345678',
        ], $atributos));
    }

    /**
     * Helper para crear un Representante válido
     */
    protected function crearRepresentante(int $personaId, array $atributos = []): Representante
    {
        return Representante::create(array_merge([
            'persona_id'                        => $personaId,
            'pais_id'                           => $this->paisId,
            'estado_id'                         => $this->estadoId,
            'municipio_id'                      => $this->municipioId,
            'parroquia_id'                      => $this->localidadId,
            'ocupacion_representante'           => $this->ocupacionId,
            'convivenciaestudiante_representante' => 'Si',
            'status'                            => 1,
        ], $atributos));
    }

    /**
     * Registro exitoso con cédula válida de Representante -> user creado con persona_id, rol Representante
     */
    public function test_registro_exitoso_representante()
    {
        $persona = $this->crearPersona(['numero_documento' => '11223344']);

        $this->crearRepresentante($persona->id);

        $response = $this->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '11223344',
            'email'                 => 'representante.nuevo@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'representante.nuevo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($persona->id, $user->persona_id);
        $this->assertEquals('Representante', $user->rol_principal);
        $this->assertTrue($user->hasRole('Representante'));
        $this->assertFalse($user->hasRole('Docente'));
        $this->assertFalse($user->debe_cambiar_password);
        $this->assertTrue($user->activo);

        // Redirige al dashboard/portal del Representante
        $response->assertRedirect($user->rutaDashboard());

        // Auditoría
        $auditoria = Auditoria::where('accion', 'registro_exitoso')->first();
        $this->assertNotNull($auditoria);
        $this->assertStringContainsString('11223344', $auditoria->descripcion);
    }

    /**
     * Registro exitoso con cédula válida de Docente -> rol Docente
     */
    public function test_registro_exitoso_docente()
    {
        $persona = $this->crearPersona(['numero_documento' => '22334455']);

        Docente::create([
            'persona_id' => $persona->id,
            'status'     => 1,
        ]);

        $response = $this->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '22334455',
            'email'                 => 'docente.nuevo@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'docente.nuevo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($persona->id, $user->persona_id);
        $this->assertEquals('Docente', $user->rol_principal);
        $this->assertTrue($user->hasRole('Docente'));
        $this->assertFalse($user->hasRole('Representante'));

        $response->assertRedirect($user->rutaDashboard());
    }

    /**
     * Cédula inexistente -> ValidationException, mensaje genérico, auditoría 'registro_rechazado'
     */
    public function test_cedula_inexistente_rechazada_con_mensaje_generico_y_auditoria()
    {
        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '99887766',
            'email'                 => 'desconocido@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['numero_documento']);

        $errores = session('errors')->get('numero_documento');
        $this->assertContains('No pudimos validar tus datos. Verifica la cédula o contacta a la institución.', $errores);

        $auditoria = Auditoria::where('accion', 'registro_rechazado')->first();
        $this->assertNotNull($auditoria);
        $this->assertStringContainsString('99887766', $auditoria->descripcion);
    }

    /**
     * Cédula válida pero con cuenta ya existente -> rechazado
     */
    public function test_cedula_con_cuenta_ya_existente_rechazada()
    {
        $persona = $this->crearPersona(['numero_documento' => '33445566']);

        $this->crearRepresentante($persona->id);

        // Ya existe usuario asociado
        User::create([
            'name'                  => 'Usuario Ya Registrado',
            'email'                 => 'ya_registrado@example.com',
            'password'              => bcrypt('Password123'),
            'persona_id'            => $persona->id,
            'rol_principal'         => 'Representante',
            'activo'                => true,
            'debe_cambiar_password' => false,
        ]);

        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '33445566',
            'email'                 => 'otro_correo@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['numero_documento']);

        $errores = session('errors')->get('numero_documento');
        $this->assertContains('No pudimos validar tus datos. Verifica la cédula o contacta a la institución.', $errores);
    }

    /**
     * Cédula de persona que no es ni Representante ni Docente -> rechazado
     */
    public function test_cedula_de_persona_sin_rol_representante_o_docente_rechazada()
    {
        $this->crearPersona(['numero_documento' => '44556677']);

        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '44556677',
            'email'                 => 'sin.rol@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['numero_documento']);

        $errores = session('errors')->get('numero_documento');
        $this->assertContains('No pudimos validar tus datos. Verifica la cédula o contacta a la institución.', $errores);
    }

    /**
     * Persona con ambos roles -> user con 2 roles, rol_principal = Docente
     */
    public function test_persona_con_ambos_roles_crea_usuario_con_dos_roles_y_rol_principal_docente()
    {
        $persona = $this->crearPersona(['numero_documento' => '55667788']);

        Docente::create([
            'persona_id' => $persona->id,
            'status'     => 1,
        ]);

        $this->crearRepresentante($persona->id);

        $response = $this->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '55667788',
            'email'                 => 'ambos.roles@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $this->assertAuthenticated();

        $user = User::where('email', 'ambos.roles@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($persona->id, $user->persona_id);
        $this->assertEquals('Docente', $user->rol_principal);
        $this->assertTrue($user->hasRole('Docente'));
        $this->assertTrue($user->hasRole('Representante'));
        $this->assertEquals(2, $user->roles()->count());
    }

    /**
     * Contraseña débil -> rechazada
     */
    public function test_contrasena_debil_rechazada()
    {
        $persona = $this->crearPersona(['numero_documento' => '66778899']);

        Docente::create([
            'persona_id' => $persona->id,
            'status'     => 1,
        ]);

        // Caso 1: menos de 8 caracteres
        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '66778899',
            'email'                 => 'pass.corta@example.com',
            'password'              => 'Pass1',
            'password_confirmation' => 'Pass1',
        ]);
        $response->assertSessionHasErrors(['password']);

        // Caso 2: solo letras
        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '66778899',
            'email'                 => 'pass.letras@example.com',
            'password'              => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ]);
        $response->assertSessionHasErrors(['password']);

        // Caso 3: solo números
        $response = $this->from(route('register'))->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '66778899',
            'email'                 => 'pass.numeros@example.com',
            'password'              => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Se envía correo de verificación al registrarse
     */
    public function test_se_envia_correo_de_verificacion_al_registrarse()
    {
        Notification::fake();

        $persona = $this->crearPersona(['numero_documento' => '77889900']);
        $this->crearRepresentante($persona->id);

        $this->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '77889900',
            'email'                 => 'notif.test@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $user = User::where('email', 'notif.test@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * Usuario recién registrado tiene email_verified_at = null
     */
    public function test_usuario_recien_registrado_tiene_email_verified_at_null()
    {
        $persona = $this->crearPersona(['numero_documento' => '88990011']);
        $this->crearRepresentante($persona->id);

        $this->post(route('register'), [
            'tipo_documento_id'     => $this->tipoV->id,
            'numero_documento'      => '88990011',
            'email'                 => 'verified.null@example.com',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $user = User::where('email', 'verified.null@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->hasVerifiedEmail());
    }

    /**
     * Usuario sin verificar es redirigido a /email/verify al intentar acceder al portal
     */
    public function test_usuario_sin_verificar_es_redirigido_a_email_verify_al_intentar_acceder_al_portal()
    {
        $persona = $this->crearPersona(['numero_documento' => '99001122']);
        $this->crearRepresentante($persona->id);

        $user = User::create([
            'name'                  => 'Usuario No Verificado',
            'email'                 => 'no.verificado@example.com',
            'password'              => bcrypt('Password123'),
            'persona_id'            => $persona->id,
            'rol_principal'         => 'Representante',
            'activo'                => true,
            'debe_cambiar_password' => false,
            'email_verified_at'     => null,
        ]);
        $user->assignRole('Representante');

        // Al intentar acceder a una ruta protegida con verified, debe redirigir a verification.notice
        $response = $this->actingAs($user)->get(route('portal-representante.index'));
        $response->assertRedirect(route('verification.notice'));

        // También al intentar acceder al dashboard o home
        $responseHome = $this->actingAs($user)->get(route('home'));
        $responseHome->assertRedirect(route('verification.notice'));
    }
}
