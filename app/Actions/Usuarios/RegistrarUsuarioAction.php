<?php

namespace App\Actions\Usuarios;

use App\Models\Auditoria;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegistrarUsuarioAction
{
    /**
     * Ejecuta el registro de un nuevo usuario validando existencia de Persona previa,
     * roles activos (Docente y/o Representante), y unicidad de cuenta.
     *
     * @param array $datos
     * @return User
     * @throws ValidationException
     */
    public function ejecutar(array $datos): User
    {
        $mensajeGenerico = 'No pudimos validar tus datos. Verifica la cédula o contacta a la institución.';

        try {
            // a) Buscar la Persona activa
            $persona = Persona::where('tipo_documento_id', $datos['tipo_documento_id'])
                ->where('numero_documento', $datos['numero_documento'])
                ->where('status', 1)
                ->first();

            // b) Si no existe -> lanzar ValidationException con mensaje genérico
            if (!$persona) {
                throw ValidationException::withMessages([
                    'numero_documento' => [$mensajeGenerico],
                ]);
            }

            // c) Verificar si la persona es Representante activo O Docente activo
            $esRepresentante = Representante::where('persona_id', $persona->id)
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->exists();

            $esDocente = Docente::where('persona_id', $persona->id)
                ->where('status', 1)
                ->exists();

            if (!$esRepresentante && !$esDocente) {
                throw ValidationException::withMessages([
                    'numero_documento' => [$mensajeGenerico],
                ]);
            }

            // d) Verificar que esa persona no tenga ya un usuario
            if (User::where('persona_id', $persona->id)->exists()) {
                throw ValidationException::withMessages([
                    'numero_documento' => [$mensajeGenerico],
                ]);
            }

            // e) Decidir el rol:
            // - Si SOLO es representante -> 'Representante'
            // - Si SOLO es docente -> 'Docente'
            // - Si es AMBOS -> 'Docente' por defecto (rol_principal), pero asignar ambos roles.
            // (Decisión provisional: si es ambos roles, rol_principal = Docente; en el futuro podría preguntarse al usuario)
            if ($esDocente && $esRepresentante) {
                $rolPrincipal = 'Docente';
                $rolesAAsignar = ['Docente', 'Representante'];
            } elseif ($esDocente) {
                $rolPrincipal = 'Docente';
                $rolesAAsignar = ['Docente'];
            } else {
                $rolPrincipal = 'Representante';
                $rolesAAsignar = ['Representante'];
            }

            // f) Crear el usuario
            $user = User::create([
                'name'                  => trim($persona->primer_nombre . ' ' . $persona->primer_apellido),
                'email'                 => $datos['email'],
                'password'              => Hash::make($datos['password']),
                'persona_id'            => $persona->id,
                'rol_principal'         => $rolPrincipal,
                'debe_cambiar_password' => false,
                'activo'                => true,
                'email_verified_at'     => null,
            ]);

            // g) Asignar roles con Spatie
            $user->assignRole($rolesAAsignar);

            // h) Auditar éxito
            Auditoria::registrar(
                'registro_exitoso',
                $user,
                null,
                null,
                'Registro de cuenta para cédula: ' . $persona->numero_documento
            );

            // Disparar evento Registered para envío automático de correo de verificación
            event(new \Illuminate\Auth\Events\Registered($user));

            // i) Devolver $user
            return $user;

        } catch (ValidationException $e) {
            // Auditar rechazo con motivo sin incluir contraseñas
            Auditoria::registrar(
                'registro_rechazado',
                null,
                null,
                null,
                'Intento de registro rechazado para cédula: ' . ($datos['numero_documento'] ?? 'N/A')
                . ' / tipo: ' . ($datos['tipo_documento_id'] ?? 'N/A')
            );

            throw $e;
        }
    }
}
