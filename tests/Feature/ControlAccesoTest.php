<?php

namespace Tests\Feature;

use App\Models\Auditoria;
use App\Models\User;
use Database\Seeders\PermisosSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SeguridadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ControlAccesoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermisosSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    /**
     * Helper para crear usuario con rol, verificado y sin cambio de contraseña pendiente
     */
    protected function crearUsuarioConRol(string $rol): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $user->assignRole($rol);
        return $user;
    }

    /**
     * Helper para crear usuario sin roles
     */
    protected function crearUsuarioLimpio(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
    }

    /**
     * 1. Cobertura de rutas:
     * Toda ruta web no pública y que no sea solo-sesión debe tener middleware role, permission o role_or_permission.
     */
    public function test_cobertura_de_rutas_protegidas()
    {
        $rutasExcluidas = [
            'login', 'login.post', 'logout', 'register', 'register.post',
            'password.request', 'password.email', 'password.reset', 'password.update',
            'password.confirm', 'password.confirm.post',
            'password.change', 'password.change.post',
            'verification.notice', 'verification.verify', 'verification.resend',
            'adminlte.darkmode.toggle',
            'sanctum.csrf-cookie',
            'livewire.update', 'livewire.upload-file', 'livewire.preview-file',
            'ignition.healthCheck', 'ignition.executeSolution', 'ignition.shareReport',
            'home.principal', 'home.vidaEstudiantil', 'up', '/', 'vidaEstudiantil',
            'password/confirm', 'sin-acceso',
        ];

        $rutas = Route::getRoutes();

        foreach ($rutas as $route) {
            $uri = $route->uri();
            $name = $route->getName();

            // Filtrar rutas internas de laravel / vendor / storage / flux
            if (
                str_starts_with($uri, '_debugbar') ||
                str_starts_with($uri, '_ignition') ||
                str_starts_with($uri, 'sanctum') ||
                str_starts_with($uri, 'livewire') ||
                str_starts_with($uri, 'storage') ||
                str_starts_with($uri, 'flux')
            ) {
                continue;
            }

            if ($name && in_array($name, $rutasExcluidas, true)) {
                continue;
            }

            if (in_array($uri, $rutasExcluidas, true)) {
                continue;
            }

            // Selectores API de apoyo que solo requieren sesión y auth
            $selectoresSesionApi = [
                'api/grado',
                'api/seccion',
                'api/bloques-horario',
                'api/dias-semana',
                'api/areas-formacion/grado',
                'api/anio-escolar/activo',
            ];
            if (in_array($uri, $selectoresSesionApi, true)) {
                continue;
            }

            $middleware = $route->gatherMiddleware();

            $tieneProteccionRolOPermiso = false;
            foreach ($middleware as $m) {
                if (
                    str_starts_with($m, 'role:') ||
                    str_starts_with($m, 'permission:') ||
                    str_starts_with($m, 'role_or_permission:')
                ) {
                    $tieneProteccionRolOPermiso = true;
                    break;
                }
            }

            $this->assertTrue(
                $tieneProteccionRolOPermiso,
                "La ruta [{$uri}] (nombre: '{$name}') debe estar protegida con un middleware role, permission o role_or_permission."
            );
        }
    }

    /**
     * 2. Home: Admin accede, mientras que Docente, Subdirectora y Representante son redirigidos a su rutaDashboard()
     * y no ven métricas en /home.
     */
    public function test_home_admin_accede_y_otros_roles_son_redirigidos()
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $this->actingAs($admin)
            ->get(route('home'))
            ->assertOk();

        $subdirectora = $this->crearUsuarioConRol('Subdirectora');
        $this->actingAs($subdirectora)
            ->get(route('home'))
            ->assertRedirect($subdirectora->rutaDashboard());

        $docente = $this->crearUsuarioConRol('Docente');
        $this->actingAs($docente)
            ->get(route('home'))
            ->assertRedirect($docente->rutaDashboard());

        $representante = $this->crearUsuarioConRol('Representante');
        $this->actingAs($representante)
            ->get(route('home'))
            ->assertRedirect($representante->rutaDashboard());
    }

    /**
     * 3. Búsquedas por cédula y autocompletado exigen .gestionar.
     * Un usuario con solo personal.ver recibe 403 en las búsquedas por cédula de docente.
     */
    public function test_busqueda_cedula_docente_falla_con_solo_ver()
    {
        $user = $this->crearUsuarioLimpio();
        $user->givePermissionTo('personal.ver');

        $this->actingAs($user)
            ->get(route('admin.docente.verificar_cedula', ['numero_documento' => '12345678']))
            ->assertForbidden();
    }

    /**
     * Un usuario con personal.gestionar puede acceder a la búsqueda por cédula de docente.
     */
    public function test_busqueda_cedula_docente_autorizada_con_gestionar()
    {
        $user = $this->crearUsuarioLimpio();
        $user->givePermissionTo('personal.gestionar');

        $response = $this->actingAs($user)
            ->get(route('admin.docente.verificar_cedula', ['numero_documento' => '12345678']));
        
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Búsquedas de representante exigen representantes.gestionar, inscripciones.gestionar o prosecucion.gestionar.
     */
    public function test_busqueda_cedula_representante_permite_gestionar_asociados()
    {
        $userSoloVer = $this->crearUsuarioLimpio();
        $userSoloVer->givePermissionTo('representantes.ver');

        $this->actingAs($userSoloVer)
            ->get(route('representante.verificar_numero_documento', ['numero_documento' => '12345678']))
            ->assertForbidden();

        $this->actingAs($userSoloVer)
            ->get(route('representante.buscar_numero_documento', ['numero_documento' => '12345678']))
            ->assertForbidden();

        $userInscripcion = $this->crearUsuarioLimpio();
        $userInscripcion->givePermissionTo('inscripciones.gestionar');

        $response = $this->actingAs($userInscripcion)
            ->get(route('representante.verificar_numero_documento', ['numero_documento' => '12345678']));
        $this->assertNotEquals(403, $response->status());

        $userProsecucion = $this->crearUsuarioLimpio();
        $userProsecucion->givePermissionTo('prosecucion.gestionar');

        $responsePros = $this->actingAs($userProsecucion)
            ->get(route('representante.verificar_numero_documento', ['numero_documento' => '12345678']));
        $this->assertNotEquals(403, $responsePros->status());
    }

    /**
     * 4. Rutas api/*:
     * Sin sesión y sin auth responden 401 JSON.
     * check-email, api/persona y api/user deben responder 404 (eliminadas).
     */
    public function test_rutas_api_eliminadas_y_protegidas()
    {
        // Rutas eliminadas dan 404
        $this->getJson('/check-email?email=test@example.com')->assertNotFound();
        $this->getJson('/api/user')->assertNotFound();
        $this->getJson('/api/persona')->assertNotFound();

        // Endpoints existentes sin sesión responden 401 en JSON
        $this->getJson('/api/docente')->assertStatus(401);
        $this->getJson('/api/grado')->assertStatus(401);
        $this->getJson('/api/seccion')->assertStatus(401);
        $this->getJson('/api/bloques-horario')->assertStatus(401);
    }

    /**
     * 5. api/docente responde con permission horarios.ver|personal.ver
     */
    public function test_api_docente_permiso()
    {
        $userSinPermiso = $this->crearUsuarioLimpio();
        $this->actingAs($userSinPermiso)
            ->getJson('/api/docente')
            ->assertForbidden();

        $userConPermiso = $this->crearUsuarioLimpio();
        $userConPermiso->givePermissionTo('horarios.ver');
        $this->actingAs($userConPermiso)
            ->getJson('/api/docente')
            ->assertOk();
    }

    /**
     * 6. Matriz de roles:
     * - Admin accede a todo.
     * - Subdirectora puede ver/gestionar bloques y aulas, ver personal y horarios, pero recibe 403 en alumnos y horarios.gestionar.
     * - Docente y Representante reciben 403 en el panel admin.
     */
    public function test_matriz_roles_acceso()
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $this->actingAs($admin)->get(route('aulas.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.alumnos.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.horario.index'))->assertOk();

        $subdirectora = $this->crearUsuarioConRol('Subdirectora');
        // Subdirectora gestiona aulas
        $this->actingAs($subdirectora)->get(route('aulas.index'))->assertOk();
        $this->actingAs($subdirectora)->post(route('aulas.store'), [
            'nombre_aula' => 'Aula Test 101',
            'tipo_aula' => 'Teoría',
        ])->assertRedirect(route('aulas.index'));
        // Subdirectora tiene horarios.ver pero no horarios.gestionar
        $this->actingAs($subdirectora)->get(route('admin.horario.index'))->assertOk();
        $this->actingAs($subdirectora)->get(route('admin.horario.create'))->assertForbidden();
        // Subdirectora no tiene permisos en alumnos
        $this->actingAs($subdirectora)->get(route('admin.alumnos.index'))->assertForbidden();

        // Representante y Docente reciben 403 en el panel administrativo
        $representante = $this->crearUsuarioConRol('Representante');
        $this->actingAs($representante)->get(route('aulas.index'))->assertForbidden();
        $this->actingAs($representante)->get(route('admin.alumnos.index'))->assertForbidden();

        $docente = $this->crearUsuarioConRol('Docente');
        $this->actingAs($docente)->get(route('aulas.index'))->assertForbidden();
        $this->actingAs($docente)->get(route('admin.alumnos.index'))->assertForbidden();
    }

    /**
     * 7. Registro de auditoría ante un 403 (acceso_denegado).
     */
    public function test_acceso_denegado_registra_auditoria()
    {
        $userSinPermiso = $this->crearUsuarioLimpio();

        $auditoriasAntes = Auditoria::where('accion', 'acceso_denegado')->count();

        $this->actingAs($userSinPermiso)
            ->get(route('aulas.index'))
            ->assertForbidden();

        $auditoriasDespues = Auditoria::where('accion', 'acceso_denegado')->count();

        $this->assertGreaterThan($auditoriasAntes, $auditoriasDespues);

        $ultima = Auditoria::where('accion', 'acceso_denegado')->latest('id')->first();
        $this->assertNotNull($ultima);
        $this->assertEquals($userSinPermiso->id, $ultima->user_id);
        $permisosRequeridos = implode(',', (array) ($ultima->valores_nuevos['permiso_requerido'] ?? []));
        $this->assertStringContainsString('aulas.ver', $permisosRequeridos);
    }

    /**
     * 8. Portal representante Livewire:
     * Con usuario representante (representante@example.com con portal_prosecucion.registrar),
     * portal-representante.prosecucion.create responde 200 y el componente se monta sin 403.
     * Con un usuario sin portal_prosecucion.registrar, el componente se bloquea (403).
     */
    public function test_portal_representante_prosecucion_livewire_permisos()
    {
        $representante = User::factory()->create([
            'email' => 'representante@example.com',
            'email_verified_at' => now(),
            'debe_cambiar_password' => false,
            'activo' => true,
        ]);
        $representante->assignRole('Representante');

        // Acceso exitoso a la ruta
        $this->actingAs($representante)
            ->get(route('portal-representante.prosecucion.create'))
            ->assertOk();

        // Montar componente con Livewire sin 403
        \Livewire\Livewire::actingAs($representante)
            ->test(\App\Livewire\Admin\TransaccionInscripcion\InscripcionProsecucion::class)
            ->assertSuccessful();

        // Usuario sin permiso es bloqueado en Livewire con 403
        $usuarioSinPermiso = $this->crearUsuarioLimpio();
        \Livewire\Livewire::actingAs($usuarioSinPermiso)
            ->test(\App\Livewire\Admin\TransaccionInscripcion\InscripcionProsecucion::class)
            ->assertStatus(403);
    }

    /**
     * 9. Usuarios sin dashboard (Editor, User, sin roles):
     * Al visitar /home llegan a /sin-acceso sin bucle de redirección (máximo 1 redirección).
     */
    public function test_usuarios_sin_dashboard_llegan_a_sin_acceso_sin_bucle()
    {
        // Usuario con rol Editor
        $editor = $this->crearUsuarioConRol('Editor');
        $this->actingAs($editor)
            ->get(route('home'))
            ->assertRedirect(route('sin-acceso'));
        $this->actingAs($editor)
            ->get(route('sin-acceso'))
            ->assertOk();

        // Usuario con rol User
        $userRol = $this->crearUsuarioConRol('User');
        $this->actingAs($userRol)
            ->get(route('home'))
            ->assertRedirect(route('sin-acceso'));
        $this->actingAs($userRol)
            ->get(route('sin-acceso'))
            ->assertOk();

        // Usuario sin roles
        $sinRoles = $this->crearUsuarioLimpio();
        $this->actingAs($sinRoles)
            ->get(route('home'))
            ->assertRedirect(route('sin-acceso'));
        $this->actingAs($sinRoles)
            ->get(route('sin-acceso'))
            ->assertOk();
    }

    /**
     * 10. Auditoría y datos personales:
     * Una petición denegada a una ruta con ?numero_documento=12345678 no deja esa cifra
     * en ninguna columna de la tabla auditorias (url limpia sin query string).
     */
    public function test_auditoria_no_guarda_query_string_con_datos_personales()
    {
        $userSinPermiso = $this->crearUsuarioLimpio();

        $this->actingAs($userSinPermiso)
            ->get('/representante/verificar-numero_documento?numero_documento=12345678')
            ->assertForbidden();

        $ultima = Auditoria::latest('id')->first();
        $this->assertNotNull($ultima);
        $this->assertStringNotContainsString('12345678', $ultima->url);
        $this->assertStringNotContainsString('12345678', json_encode($ultima->toArray()));
    }

    /**
     * 11. Auditoría de 403 en Livewire:
     * Si un componente Livewire se bloquea por falta de permiso, se registra en auditorías.
     */
    public function test_auditoria_de_403_en_livewire()
    {
        $usuarioSinPermiso = $this->crearUsuarioLimpio();
        $auditoriasAntes = Auditoria::where('accion', 'acceso_denegado')->count();

        \Livewire\Livewire::actingAs($usuarioSinPermiso)
            ->test(\App\Livewire\Admin\TransaccionInscripcion\InscripcionProsecucion::class)
            ->assertStatus(403);

        $auditoriasDespues = Auditoria::where('accion', 'acceso_denegado')->count();
        $this->assertGreaterThan($auditoriasAntes, $auditoriasDespues);
    }

    /**
     * 12. God Mode para el rol Admin:
     * - El Admin pasa cualquier Gate::allows(), $user->can(), Policy o middleware permission sin 403.
     * - Usuarios no Admin son evaluados normalmente y reciben 403 cuando no tienen permisos.
     */
    public function test_admin_god_mode_accede_a_rutas_administrativas_y_salta_validaciones()
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $docente = $this->crearUsuarioConRol('Docente');
        $representante = $this->crearUsuarioConRol('Representante');

        // 1. Verificación directa de Gate::before: Admin tiene acceso a cualquier habilidad (existente o arbitraria)
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->allows('permiso.totalmente.inventado'));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->allows('alumnos.gestionar'));
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($admin)->allows('alumnos.editar'));
        $this->assertTrue($admin->can('alumnos.gestionar'));
        $this->assertTrue($admin->can('habilidad.inexistente'));

        // 2. Otros roles NO tienen God Mode
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($docente)->allows('permiso.totalmente.inventado'));
        $this->assertFalse(\Illuminate\Support\Facades\Gate::forUser($representante)->allows('permiso.totalmente.inventado'));

        // 3. Crear año escolar activo para rutas que usan middleware verificar.anio.escolar
        \App\Models\AnioEscolar::create([
            'inicio_anio_escolar' => now()->subMonth(),
            'cierre_anio_escolar' => now()->addMonths(10),
            'status' => 'Activo',
        ]);

        // 4. Admin accede a todas las rutas administrativas sin recibir 403
        $rutasAdmin = [
            route('admin.alumnos.index'),
            route('admin.alumnos.create'),
            route('aulas.index'),
            route('admin.docente.index'),
            route('admin.area_formacion.index'),
            route('admin.auditoria.index'),
            route('admin.usuarios.index'),
            route('admin.horario.index'),
            route('admin.seccion_aula.index'),
            route('admin.grado.index'),
            route('admin.anio_escolar.index'),
        ];

        foreach ($rutasAdmin as $url) {
            $response = $this->actingAs($admin)->get($url);
            $this->assertNotEquals(
                403,
                $response->status(),
                "El Admin recibió 403 Acceso Denegado inesperado en la ruta: {$url}"
            );
        }

        // 5. Los roles sin acceso administrativo siguen recibiendo 403 donde corresponde
        $this->actingAs($docente)->get(route('admin.alumnos.index'))->assertForbidden();
        $this->actingAs($docente)->get(route('admin.alumnos.create'))->assertForbidden();
        $this->actingAs($representante)->get(route('admin.alumnos.index'))->assertForbidden();
        $this->actingAs($representante)->get(route('admin.alumnos.create'))->assertForbidden();
    }
}

