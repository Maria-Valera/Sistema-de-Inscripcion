<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'email' => ['El correo electrónico o la contraseña son incorrectos. Por favor, verifica tus datos e intenta nuevamente.'],
        ]);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
        protected function validateLogin(Request $request)
        {
            $request->validate([
                'email'    => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ], [
                'email.required' => 'El campo correo electrónico es obligatorio.',
                'email.email'    => 'El formato del correo electrónico no es válido.',
                'email.string'   => 'El correo electrónico debe ser una cadena de texto.',
                'password.required' => 'El campo contraseña es obligatorio.',
                'password.string'   => 'La contraseña debe ser una cadena de texto.',
            ]);
        }
}
