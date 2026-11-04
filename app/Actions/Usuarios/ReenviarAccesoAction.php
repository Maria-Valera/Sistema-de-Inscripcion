<?php

namespace App\Actions\Usuarios;

use App\Mail\AccesoUsuarioMailable;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReenviarAccesoAction
{
    /**
     * Genera una nueva contraseña temporal segura para el usuario,
     * actualiza debe_cambiar_password = true y email_verified_at = null,
     * audita la acción (sin incluir la contraseña) y envía el correo con las credenciales.
     *
     * @param User $user
     * @return string La contraseña temporal generada
     */
    public function ejecutar(User $user): string
    {
        // a) Generar una nueva contraseña temporal segura (12 caracteres)
        $passwordTemporal = Str::password(12, true, true, false, false);

        // b) Actualizar usuario
        $user->forceFill([
            'password'              => Hash::make($passwordTemporal),
            'debe_cambiar_password' => true,
            'email_verified_at'     => null,
        ])->save();

        // c) Auditar acceso_reenviado (sin incluir la contraseña en ningún campo)
        Auditoria::registrar(
            'acceso_reenviado',
            $user,
            null,
            [
                'debe_cambiar_password' => true,
                'email_verified_at'     => null,
            ],
            "Acceso reenviado con nueva contraseña temporal para el usuario {$user->email}"
        );

        // d) Enviar la nueva contraseña temporal por correo usando el Mailable
        Mail::to($user->email)->send(new AccesoUsuarioMailable($user, $passwordTemporal));

        return $passwordTemporal;
    }
}
