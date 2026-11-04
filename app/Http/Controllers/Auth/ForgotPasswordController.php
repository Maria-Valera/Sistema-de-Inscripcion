<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auditoria;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // We will send the password reset link to this user's e-mail address.
        // But first, we'll check if the email exists in the database.
        $status = Password::sendResetLink($request->only('email'));

        // Registrar en auditoría (sin revelar si el usuario existe)
        Auditoria::registrar(
            'reset_password_email',
            null,
            null,
            null,
            'Solicitud de restablecimiento de contraseña para: ' . $request->input('email')
        );

        // Mensaje genérico independientemente de si el correo existe o no
        return back()->with('status', __(
            'Si el correo electrónico está registrado en nuestro sistema, recibirás un enlace para restablecer tu contraseña.'
        ));
    }
}
