<?php

namespace App\Actions\Usuarios;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class RegistrarUsuarioAction
{
    public function __construct(
        protected CrearUsuarioParaPersonaAction $crearUsuarioAction
    ) {}

    /**
     * Ejecuta el registro de un nuevo usuario delegando la creación del modelo
     * y las reglas de persona/rol a CrearUsuarioParaPersonaAction, manteniendo
     * el evento Registered para el envío del correo de verificación.
     *
     * @param array $datos
     * @return User
     * @throws ValidationException
     */
    public function ejecutar(array $datos): User
    {
        // Indicamos origen 'auto_registro' para que audite 'registro_exitoso' y use la contraseña provista
        $datos['origen'] = 'auto_registro';

        $resultado = $this->crearUsuarioAction->ejecutar($datos);
        $user = $resultado['user'];

        // Disparar evento Registered para envío automático de correo de verificación
        event(new \Illuminate\Auth\Events\Registered($user));

        return $user;
    }
}
