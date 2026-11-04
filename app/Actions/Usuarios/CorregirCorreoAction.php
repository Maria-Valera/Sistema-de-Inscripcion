<?php

namespace App\Actions\Usuarios;

use App\Models\Auditoria;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CorregirCorreoAction
{
    /**
     * Actualiza el correo electrónico de una Persona y de su User asociado si existe.
     * Si la persona tiene un User, se invalida la verificación estableciendo email_verified_at = null
     * para forzar una nueva verificación de email.
     *
     * @param Persona $persona
     * @param string $nuevoCorreo
     * @return Persona
     * @throws ValidationException
     */
    public function ejecutar(Persona $persona, string $nuevoCorreo): Persona
    {
        $nuevoCorreo = trim(strtolower($nuevoCorreo));

        // Validar formato del correo
        if (!filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['El formato del correo electrónico no es válido.'],
            ]);
        }

        // Si el usuario existe, validar unicidad en la tabla users excluyendo al usuario actual
        $userAsociado = $persona->user;
        $userIdExcluir = $userAsociado?->id;

        $correoEnUsoEnUsers = User::where('email', $nuevoCorreo)
            ->when($userIdExcluir, fn ($q) => $q->where('id', '!=', $userIdExcluir))
            ->exists();

        if ($correoEnUsoEnUsers) {
            throw ValidationException::withMessages([
                'email' => ['Este correo electrónico ya está en uso por otra cuenta.'],
            ]);
        }

        $correoViejo = $persona->email;

        // Si el correo no cambió, no realizamos modificaciones
        if ($correoViejo === $nuevoCorreo && (!$userAsociado || $userAsociado->email === $nuevoCorreo)) {
            return $persona;
        }

        // Actualizar correo en Persona
        $persona->update([
            'email' => $nuevoCorreo,
        ]);

        // Si la Persona tiene un User asociado, actualizar también en users e invalidar verificación
        if ($userAsociado) {
            $userAsociado->forceFill([
                'email'             => $nuevoCorreo,
                'email_verified_at' => null,
            ])->save();
        }

        // Auditar acción como 'correo_corregido'
        Auditoria::registrar(
            'correo_corregido',
            $userAsociado ?? $persona,
            ['email' => $correoViejo],
            ['email' => $nuevoCorreo],
            "Correo corregido de '{$correoViejo}' a '{$nuevoCorreo}' para la persona con cédula {$persona->numero_documento}"
        );

        return $persona;
    }
}
