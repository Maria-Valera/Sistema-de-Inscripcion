<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     * Redirección dinámica según el rol principal del usuario (coherente con LoginController).
     */
    protected function redirectTo()
    {
        return auth()->user() ? auth()->user()->rutaDashboard() : '/home';
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $this->setUserPassword($user, $password);
        
        $user->save();
        
        // Forzar cambio de contraseña en el próximo login
        $user->forceFill([
            'debe_cambiar_password' => true,
        ])->save();

        event(new \Illuminate\Auth\Events\PasswordReset($user));
        $this->guard()->login($user);

        // Registrar en auditoría
        Auditoria::registrar(
            'reset_password',
            $user,
            null,
            null,
            'Restablecimiento de contraseña mediante enlace'
        );
    }
}
