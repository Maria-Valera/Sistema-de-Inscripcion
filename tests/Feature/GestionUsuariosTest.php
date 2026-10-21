<?php

namespace Tests\Feature;

use App\Actions\Usuarios\CorregirCorreoAction;
use App\Actions\Usuarios\CrearUsuarioParaPersonaAction;
use App\Actions\Usuarios\GestionarModulosUsuarioAction;
use App\Livewire\Admin\Usuarios\GestionarUsuarios;
use App\Models\Auditoria;
use App\Models\Docente;
use App\Models\Genero;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GestionUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected TipoDocumento $tipoV;
    protected Genero $genero;
    protected Localidad $localidad;
    protected PrefijoTelefono $prefijo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);

        // Asegurar existencia del permiso usuarios.gestionar_modulos
        Permission::firstOrCreate(['name' => 'usuarios.gestionar_modulos', 'guard_name' => 'web']);

        $this->tipoV = TipoDocumento::firstOrCreate(
            ['id' => 1],
            ['nombre' => 'V', 'status' => 1]
        );

        $this->genero = Genero::firstOrCreate(['id' => 1], [
            'genero' => 'Masculino',
            'status' => 1,
        ]);

        $pais = \App\Models\Pais::firstOrCreate(
            ['id' => 1],
            ['nameES' => 'Venezuela', 'iso2' => 'VE', 'status' => 1]
        );

        $estado = \App\Models\Estado::firstOrCreate(
            ['id' => 1],
            ['nombre_estado' => 'Lara', 'pais_id' => $pais->id, 'status' => 1]
        );

        $municipio = \App\Models\Municipio::firstOrCreate(
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
    }

    protected function crearPersona(array $atributos = []): Persona
    {
        return Persona::create(array_merge([
            'primer_nombre'     => 'Juan',
            'primer_apellido'   => 'Pérez',
            'tipo_documento_id' => $this->tipoV->id,
            'numero_documento'  => '12345678',
            'fecha_nacimiento'  => '1990-01-01',
            'direccion'         => 'Caracas',
            'email'             => 'juan.perez@example.com',
            'telefono'          => '1234567',
            'genero_id'         => $this->genero->id,
            'localidad_id'      => $this->localidad->id,
            'prefijo_id'        => $this->prefijo->id,
            'status'            => 1,
        ], $atributos));
    }

    /**
     * 1. Un usuario sin el permiso usuarios.gestionar_modulos recibe 403 al intentar acceder a la ruta.
     */
    public function test_usuario_sin_permiso_gestionar_modulos_recibe_403()
    {
        $userSinPermiso = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $userSinPermiso->assignRole('Representante');

        $response = $this->actingAs($userSinPermiso)->get(route('admin.usuarios.index'));
        $response->assertStatus(403);
    }

    /**
     * 2. Un admin con el permiso usuarios.gestionar_modulos puede ver la lista de usuarios (assertStatus 200).
     */
    public function test_admin_puede_ver_lista_de_usuarios()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');

        $response = $this->actingAs($admin)->get(route('admin.usuarios.index'));
        $response->assertStatus(200);
        $response->assertSee('Gestión de Usuarios y Accesos');
    }

    /**
     * 3. Asignar un módulo extra (llamando a la Action correspondiente) y verificar que el usuario lo adquiere.
     */
    public function test_asignar_modulo_extra_y_verificar_que_usuario_lo_adquiere()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $usuario = User::factory()->create([
            'email' => 'representante.prueba@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $usuario->assignRole('Representante');

        $this->assertFalse(in_array('horarios', $usuario->modulosEfectivos()));

        $action = new GestionarModulosUsuarioAction();
        $action->asignar($usuario, 'horarios');

        $this->assertTrue(in_array('horarios', $usuario->fresh()->modulosEfectivos()));
        $this->assertTrue($usuario->hasDirectPermission('horarios.ver'));

        // Auditar acción
        $auditoria = Auditoria::where('accion', 'modulo_asignado')
            ->where('auditable_id', $usuario->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($auditoria);
        $this->assertStringContainsString('horarios', $auditoria->descripcion);
    }

    /**
     * 4. Retirar un módulo extra; si el rol no lo tenía, el usuario lo pierde.
     */
    public function test_retirar_modulo_extra_y_usuario_lo_pierde_si_no_lo_tenia_por_rol()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $usuario = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $usuario->assignRole('Representante');

        $action = new GestionarModulosUsuarioAction();
        $action->asignar($usuario, 'horarios');
        $this->assertTrue(in_array('horarios', $usuario->modulosEfectivos()));

        $conserva = $action->retirar($usuario, 'horarios');
        $this->assertFalse($conserva);
        $this->assertFalse(in_array('horarios', $usuario->fresh()->modulosEfectivos()));

        $auditoria = Auditoria::where('accion', 'modulo_retirado')
            ->where('auditable_id', $usuario->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($auditoria);
        $this->assertStringContainsString('horarios', $auditoria->descripcion);
    }

    /**
     * 5. Intentar retirar un módulo que viene del rol (debe conservarlo).
     */
    public function test_intentar_retirar_modulo_que_viene_del_rol_lo_conserva()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $usuario = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $usuario->assignRole('Representante');

        // Representante tiene por rol permisos del módulo portal_carnets
        $action = new GestionarModulosUsuarioAction();
        $conserva = $action->retirar($usuario, 'portal_carnets');

        $this->assertTrue($conserva);
        $this->assertTrue(in_array('portal_carnets', $usuario->fresh()->modulosEfectivos()));
    }

    /**
     * 6. Un admin puede corregir el correo de un usuario; el correo se actualiza y email_verified_at se vuelve null.
     */
    public function test_admin_puede_corregir_correo_y_resetea_email_verified_at()
    {
        $persona = $this->crearPersona([
            'email' => 'correo.antiguo@example.com',
            'numero_documento' => '11998877',
        ]);

        $usuario = User::factory()->create([
            'persona_id' => $persona->id,
            'email' => 'correo.antiguo@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);

        $this->assertNotNull($usuario->email_verified_at);

        $action = new CorregirCorreoAction();
        $action->ejecutar($persona, 'correo.nuevo@example.com');

        $personaRefrescada = $persona->fresh();
        $usuarioRefrescado = $usuario->fresh();

        $this->assertEquals('correo.nuevo@example.com', $personaRefrescada->email);
        $this->assertEquals('correo.nuevo@example.com', $usuarioRefrescado->email);
        $this->assertNull($usuarioRefrescado->email_verified_at);
        $this->assertFalse($usuarioRefrescado->hasVerifiedEmail());

        $auditoria = Auditoria::where('accion', 'correo_corregido')
            ->where('auditable_id', $usuario->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals('correo.antiguo@example.com', $auditoria->valores_anteriores['email']);
        $this->assertEquals('correo.nuevo@example.com', $auditoria->valores_nuevos['email']);
    }

    /**
     * 7. Cada acción deja registro en la tabla auditorias.
     */
    public function test_acciones_dejan_registro_en_tabla_auditorias()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $usuario = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $usuario->assignRole('Representante');

        $actionModulos = new GestionarModulosUsuarioAction();
        $actionModulos->asignar($usuario, 'horarios');
        $actionModulos->retirar($usuario, 'horarios');

        $persona = $this->crearPersona(['numero_documento' => '55667788']);
        $actionCorreo = new CorregirCorreoAction();
        $actionCorreo->ejecutar($persona, 'nuevo5566@example.com');

        $this->assertTrue(Auditoria::where('accion', 'modulo_asignado')->exists());
        $this->assertTrue(Auditoria::where('accion', 'modulo_retirado')->exists());
        $this->assertTrue(Auditoria::where('accion', 'correo_corregido')->exists());
    }

    /**
     * 8. Crear un Admin/Subdirectora nuevo: El sistema crea la Persona y el User sin redirigir a otros formularios.
     */
    public function test_crear_admin_o_subdirectora_nuevo_crea_persona_y_user_sin_redirigir()
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $action = new CrearUsuarioParaPersonaAction();
        $resultado = $action->ejecutar([
            'tipo_documento_id' => $this->tipoV->id,
            'numero_documento'  => '99887766',
            'primer_nombre'     => 'Beatriz',
            'primer_apellido'   => 'Mendoza',
            'email'             => 'beatriz.subdirectora@example.com',
            'rol'               => 'Subdirectora',
            'rol_principal'     => 'Subdirectora',
        ]);

        $this->assertFalse($resultado['redirigir_a_docente']);
        $this->assertFalse($resultado['redirigir_a_representante']);
        $this->assertNotNull($resultado['user']);
        $this->assertNotNull($resultado['password_temporal']);

        $user = $resultado['user'];
        $this->assertTrue($user->hasRole('Subdirectora'));
        $this->assertEquals('Subdirectora', $user->rol_principal);
        $this->assertDatabaseHas('personas', [
            'numero_documento' => '99887766',
            'primer_nombre'    => 'Beatriz',
            'primer_apellido'  => 'Mendoza',
        ]);
    }

    /**
     * 9. Crear un Docente con nueva cédula: El sistema crea el User, le envía la contraseña y devuelve la señal de redirección a la ficha del docente.
     */
    public function test_crear_docente_nueva_cedula_crea_user_y_devuelve_senal_redireccion_docente()
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        // Prueba a través de la Action
        $action = new CrearUsuarioParaPersonaAction();
        $resultado = $action->ejecutar([
            'tipo_documento_id' => $this->tipoV->id,
            'numero_documento'  => '33445566',
            'primer_nombre'     => 'Marcos',
            'primer_apellido'   => 'Rivas',
            'email'             => 'marcos.docente@example.com',
            'rol'               => 'Docente',
            'rol_principal'     => 'Docente',
        ]);

        $this->assertTrue($resultado['redirigir_a_docente']);
        $this->assertFalse($resultado['redirigir_a_representante']);
        $this->assertNotNull($resultado['user']);
        $this->assertTrue($resultado['user']->hasRole('Docente'));
        $this->assertNotNull($resultado['password_temporal']);

        // Probar también a través del componente Livewire
        Livewire::test(GestionarUsuarios::class)
            ->set('tipo_documento_id', $this->tipoV->id)
            ->set('numero_documento', '77889900')
            ->set('primer_nombre', 'Elena')
            ->set('primer_apellido', 'Gómez')
            ->set('email_crear', 'elena.docente@example.com')
            ->set('roles_crear', ['Docente'])
            ->set('rol_principal_crear', 'Docente')
            ->call('crearUsuario')
            ->assertRedirect(route('admin.docente.create', ['persona_id' => Persona::where('numero_documento', '77889900')->first()->id]));
    }

    /**
     * 10. Crear un Docente que YA existe en la tabla docentes: Solo crea el User sin señal de redirección.
     */
    public function test_crear_docente_que_ya_existe_en_docentes_solo_crea_user()
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        $persona = $this->crearPersona(['numero_documento' => '44556677']);
        Docente::create([
            'persona_id' => $persona->id,
            'status'     => 1,
        ]);

        $action = new CrearUsuarioParaPersonaAction();
        $resultado = $action->ejecutar([
            'tipo_documento_id' => $persona->tipo_documento_id,
            'numero_documento'  => '44556677',
            'primer_nombre'     => $persona->primer_nombre,
            'primer_apellido'   => $persona->primer_apellido,
            'email'             => 'docente.existente@example.com',
            'roles'             => ['Docente'],
            'rol_principal'     => 'Docente',
        ]);

        $this->assertFalse($resultado['redirigir_a_docente']);
        $this->assertFalse($resultado['redirigir_a_representante']);
        $this->assertNotNull($resultado['user']);
        $this->assertTrue($resultado['user']->hasRole('Docente'));
        $this->assertEquals($persona->id, $resultado['user']->persona_id);
    }

    /**
     * 11. Admin puede crear un usuario con DOS roles a la vez (Docente y Representante) y elegir uno de ellos como principal.
     */
    public function test_admin_puede_crear_usuario_con_multiples_roles_y_rol_principal()
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        // Persona previa que ya es Docente y Representante
        $persona = $this->crearPersona(['numero_documento' => '66778899']);
        Docente::create(['persona_id' => $persona->id, 'status' => 1]);
        $ocupacion = \App\Models\Ocupacion::firstOrCreate(['id' => 1], ['nombre_ocupacion' => 'Docente', 'status' => 1]);
        \App\Models\Representante::create([
            'persona_id'                          => $persona->id,
            'pais_id'                             => 1,
            'estado_id'                           => 1,
            'municipio_id'                        => 1,
            'parroquia_id'                        => $this->localidad->id,
            'ocupacion_representante'             => $ocupacion->id,
            'convivenciaestudiante_representante' => 'Si',
            'status'                              => 1,
        ]);

        Livewire::test(GestionarUsuarios::class)
            ->set('tipo_documento_id', $persona->tipo_documento_id)
            ->set('numero_documento', '66778899')
            ->set('primer_nombre', 'Raúl')
            ->set('primer_apellido', 'Paredes')
            ->set('email_crear', 'raul.doblerol@example.com')
            ->set('roles_crear', ['Docente', 'Representante'])
            ->set('rol_principal_crear', 'Representante')
            ->call('crearUsuario')
            ->assertHasNoErrors();

        $user = User::where('email', 'raul.doblerol@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Docente'));
        $this->assertTrue($user->hasRole('Representante'));
        $this->assertEquals('Representante', $user->rol_principal);
        $this->assertEquals(2, $user->roles()->count());
    }

    /**
     * 12. Detección en tiempo real y autocompletado de Persona existente al tipear cédula.
     */
    public function test_autocompletado_en_tiempo_real_al_ingresar_cedula_de_persona_existente()
    {
        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        // Persona existente previamente registrada
        $persona = $this->crearPersona([
            'numero_documento' => '44332211',
            'primer_nombre'    => 'Carolina',
            'primer_apellido'  => 'Mendoza',
            'email'            => 'carolina.docente@example.com',
        ]);
        Docente::create(['persona_id' => $persona->id, 'status' => 1]);

        // Simular que el usuario abre el modal e ingresa el tipo y número de documento
        Livewire::test(GestionarUsuarios::class)
            ->call('abrirModalCrear')
            ->set('tipo_documento_id', $persona->tipo_documento_id)
            ->set('numero_documento', '44332211')
            ->assertSet('personaEncontrada', true)
            ->assertSet('primer_nombre', 'Carolina')
            ->assertSet('primer_apellido', 'Mendoza')
            ->assertSet('email_crear', 'carolina.docente@example.com')
            ->assertHasNoErrors('numero_documento')
            // Ahora simular que el usuario borra la cédula
            ->set('numero_documento', '')
            ->assertSet('personaEncontrada', false)
            ->assertSet('primer_nombre', '')
            ->assertSet('primer_apellido', '')
            ->assertSet('email_crear', '')
            ->assertHasNoErrors('numero_documento');
    }

    /**
     * 13. Crear un Representante nuevo crea user y redirige a la edición individual del representante.
     */
    public function test_crear_representante_nuevo_crea_user_y_redirige_a_representante_editar()
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $admin->assignRole('Admin');
        $admin->givePermissionTo('usuarios.gestionar_modulos');
        $this->actingAs($admin);

        Livewire::test(GestionarUsuarios::class)
            ->set('tipo_documento_id', $this->tipoV->id)
            ->set('numero_documento', '99887766')
            ->set('primer_nombre', 'Manuel')
            ->set('primer_apellido', 'Rivas')
            ->set('email_crear', 'manuel.rep@example.com')
            ->set('roles_crear', ['Representante'])
            ->set('rol_principal_crear', 'Representante')
            ->call('crearUsuario')
            ->assertRedirect();

        $persona = Persona::where('numero_documento', '99887766')->first();
        $this->assertNotNull($persona);
        $representante = \App\Models\Representante::where('persona_id', $persona->id)->first();
        $this->assertNotNull($representante);

        // Verificar que la ruta de edición cargue correctamente con el id del representante
        $response = $this->get(route('representante.editar', ['id' => $representante->id]));
        $response->assertStatus(200);
        $response->assertSee('Manuel');
        $response->assertSee('Rivas');
    }
}
