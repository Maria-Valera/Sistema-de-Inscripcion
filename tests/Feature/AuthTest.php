<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Auditoria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que contraseña temporal fuerza el cambio
     */
    public function test_contrasena_temporal_fuerza_cambio()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Temporal123'),
            'debe_cambiar_password' => true,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('password.change'));
    }

    /**
     * Test que contraseña débil es rechazada en cambio de contraseña
     */
    public function test_contrasena_debil_rechazada()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Actual123'),
            'debe_cambiar_password' => true,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.post'), [
                'current_password' => 'Actual123',
                'password' => '12345678', // Solo números
                'password_confirmation' => '12345678',
            ])
            ->assertSessionHasErrors(['password']);
    }

    /**
     * Test que contraseña sin letras es rechazada
     */
    public function test_contrasena_sin_letras_rechazada()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Actual123'),
            'debe_cambiar_password' => true,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.post'), [
                'current_password' => 'Actual123',
                'password' => '1234567890123456', // Solo números
                'password_confirmation' => '1234567890123456',
            ])
            ->assertSessionHasErrors(['password']);
    }

    /**
     * Test que contraseña sin números es rechazada
     */
    public function test_contrasena_sin_numeros_rechazada()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Actual123'),
            'debe_cambiar_password' => true,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.post'), [
                'current_password' => 'Actual123',
                'password' => 'abcdefgh', // Solo letras
                'password_confirmation' => 'abcdefgh',
            ])
            ->assertSessionHasErrors(['password']);
    }

    /**
     * Test que usuario inactivo no puede entrar
     */
    public function test_usuario_inactivo_no_entra()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => false,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123',
        ])
            ->assertSessionHasErrors(['email']);
    }

    /**
     * Test que el límite de intentos bloquea
     */
    public function test_limite_intentos_bloquea()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => true,
        ]);

        // Intentar login 5 veces con contraseña incorrecta
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'WrongPassword',
            ]);
        }

        // El sexto intento debería ser bloqueado por rate limiter
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123',
        ])
            ->assertSessionHasErrors(['email']);
    }

    /**
     * Test que la contraseña nunca aparece en auditorías
     */
    public function test_contrasena_no_aparece_en_auditorias()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => true,
        ]);

        // Intento fallido
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'WrongPassword',
        ]);

        // Verificar que la contraseña no está en la auditoría
        $auditoria = Auditoria::where('accion', 'login_fallido')->first();
        $this->assertNotNull($auditoria);
        $this->assertStringNotContainsString('WrongPassword', $auditoria->descripcion);
        $this->assertStringNotContainsString('Password123', $auditoria->descripcion);
        
        // Verificar que la contraseña no está en valores_nuevos
        if ($auditoria->valores_nuevos) {
            $valores = json_decode($auditoria->valores_nuevos, true);
            $this->assertArrayNotHasKey('password', $valores);
        }
    }

    /**
     * Test que el login exitoso registra auditoría
     */
    public function test_login_exitoso_registra_auditoria()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => true,
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $auditoria = Auditoria::where('accion', 'login')->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals($user->id, $auditoria->user_id);
    }

    /**
     * Test que el logout registra auditoría
     */
    public function test_logout_registra_auditoria()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->post(route('logout'));

        $auditoria = Auditoria::where('accion', 'logout')->first();
        $this->assertNotNull($auditoria);
    }

    /**
     * Test que el cambio de contraseña registra auditoría
     */
    public function test_cambio_contrasena_registra_auditoria()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Actual123'),
            'debe_cambiar_password' => true,
            'activo' => true,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.post'), [
                'current_password' => 'Actual123',
                'password' => 'Nueva123456',
                'password_confirmation' => 'Nueva123456',
            ]);

        $auditoria = Auditoria::where('accion', 'cambio_password')->first();
        $this->assertNotNull($auditoria);
        $this->assertEquals($user->id, $auditoria->user_id);
        
        // Verificar que la contraseña no está en la auditoría
        $this->assertStringNotContainsString('Nueva123456', $auditoria->descripcion);
        $this->assertStringNotContainsString('Actual123', $auditoria->descripcion);
    }

    /**
     * Test que ultimo_acceso_at se actualiza al hacer login
     */
    public function test_ultimo_acceso_actualizado_al_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Password123'),
            'activo' => true,
            'ultimo_acceso_at' => null,
        ]);

        $this->assertNull($user->ultimo_acceso_at);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123',
        ]);

        $user->refresh();
        $this->assertNotNull($user->ultimo_acceso_at);
    }
}
