<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    /**
     * Muestra el formulario de cambio de contraseña
     */
    public function showChangeForm()
    {
        return view('auth.passwords.change');
    }

    /**
     * Procesa el cambio de contraseña
     */
    public function change(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:16', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'El campo contraseña actual es obligatorio.',
            'password.required' => 'El campo nueva contraseña es obligatorio.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La nueva contraseña no puede tener más de 16 caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'password.letters' => 'La contraseña debe contener al menos una letra.',
            'password.numbers' => 'La contraseña debe contener al menos un número.',
        ]);

        $user = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        // Verificar que la nueva contraseña sea diferente a la actual
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'La nueva contraseña debe ser diferente a la actual.']);
        }

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->password),
            'debe_cambiar_password' => false,
        ]);

        return redirect()->route('home')->with('status', 'Tu contraseña ha sido cambiada exitosamente.');
    }
}
