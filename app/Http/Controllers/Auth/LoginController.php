<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Límite de intentos: 5 por minuto por (email + IP).
     * Spatie no interviene aquí; es el throttle nativo de Laravel.
     */
    protected $maxAttempts = 5;
    protected $decayMinutes = 1;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Reglas de validación del intento de login.
     * OJO: aquí NO se valida complejidad de contraseña.
     * La regla Password::min(8)->letters()->numbers() va en el registro
     * y en el cambio de contraseña, no en el login.
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'El campo correo electrónico es obligatorio.',
            'email.email'       => 'El formato del correo electrónico no es válido.',
            'email.string'      => 'El correo electrónico debe ser una cadena de texto.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string'   => 'La contraseña debe ser una cadena de texto.',
        ]);
    }

    /**
     * Solo permitir login a usuarios activos.
     * Si activo = false, el intento falla como credenciales inválidas
     * (mensaje genérico para no enumerar cuentas).
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only('email', 'password'),
            ['activo' => true]
        );
    }

    /**
     * Registra el intento fallido en auditoría y devuelve mensaje genérico.
     * Nunca se guarda la contraseña intentada.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        Auditoria::registrar(
            'login_fallido',
            null,
            null,
            null,
            'Intento fallido para: ' . $request->input('email')
        );

        throw ValidationException::withMessages([
            'email' => ['El correo electrónico o la contraseña son incorrectos. Por favor, verifica tus datos e intenta nuevamente.'],
        ]);
    }

    /**
     * Tras login correcto: regenera sesión, actualiza ultimo_acceso_at y audita.
     */
    protected function authenticated(Request $request, $user)
    {
        // Regenerar sesión para evitar session fixation
        $request->session()->regenerate();
        
        $user->forceFill(['ultimo_acceso_at' => now()])->save();

        Auditoria::registrar(
            'login',
            $user,
            null,
            null,
            'Inicio de sesión correcto'
        );
    }

    /**
     * Al cerrar sesión: audita.
     */
    protected function loggedOut(Request $request)
    {
        Auditoria::registrar('logout', null, null, null, 'Cierre de sesión');
        return redirect('/');
    }

    /**
     * Redirección post-login según el rol principal (RF-37).
     * Depende de User::rutaDashboard() del Día 1.
     */
    protected function redirectTo()
    {
        return auth()->user()->rutaDashboard();
    }
}