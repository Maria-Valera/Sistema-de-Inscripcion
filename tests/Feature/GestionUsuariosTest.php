<?php

namespace Tests\Feature;

use App\Actions\Usuarios\CorregirCorreoAction;
use App\Actions\Usuarios\GestionarModulosUsuarioAction;
use App\Models\Auditoria;
use App\Models\Genero;
use App\Models\Localidad;
use App\Models\Persona;
use App\Models\PrefijoTelefono;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
