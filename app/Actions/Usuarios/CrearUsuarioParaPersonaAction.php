<?php

namespace App\Actions\Usuarios;

use App\Models\Auditoria;
use App\Models\Docente;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrearUsuarioParaPersonaAction
{
    /**
     * Crea un usuario para una persona precargada en el sistema.
     * Si no se proporciona una contraseña, genera una contraseña temporal segura
     * y marca al usuario con debe_cambiar_password = true.
     *
     * @param array $datos [
     *     'tipo_documento_id' => int|string,
     *     'numero_documento'  => string,
     *     'email'             => string,
     *     'password'          => ?string (opcional),
     *     'rol_principal'     => ?string (opcional),
     *     'roles'             => ?array  (opcional),
     *     'origen'            => ?string (opcional, ej: 'auto_registro' o 'admin')
     * ]
     * @return array ['user' => User, 'password_temporal' => ?string]
     * @throws ValidationException
     */
    public function ejecutar(array $datos): array
    {
        $mensajeGenerico = 'No pudimos validar tus datos. Verifica la cédula o contacta a la institución.';

        try {
            // a) Buscar la Persona activa
            $persona = Persona::where('tipo_documento_id', $datos['tipo_documento_id'])
                ->where('numero_documento', $datos['numero_documento'])
                ->where('status', 1)
                ->first();

            // b) Si no existe -> lanzar ValidationException
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

            // e) Determinar roles y rol principal si no vienen explícitos
            if (!empty($datos['roles']) && !empty($datos['rol_principal'])) {
                $rolPrincipal = $datos['rol_principal'];
                $rolesAAsignar = $datos['roles'];
            } else {
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
            }

            // f) Definir contraseña (elegida por usuario o temporal segura)
            $passwordTemporal = null;
            $passwordPlana = $datos['password'] ?? null;

            if (empty($passwordPlana)) {
                // Generar contraseña temporal segura de 12 caracteres (letras mayúsculas/minúsculas y números)
                $passwordTemporal = Str::password(12, true, true, false, false);
                $passwordPlana = $passwordTemporal;
                $debeCambiarPassword = true;
            } else {
                // Si el usuario proporcionó su propia contraseña (ej: auto-registro)
                $debeCambiarPassword = false;
            }

            // g) Crear el usuario
            $user = User::create([
                'name'                  => trim($persona->primer_nombre . ' ' . $persona->primer_apellido),
                'email'                 => $datos['email'],
                'password'              => Hash::make($passwordPlana),
                'persona_id'            => $persona->id,
                'rol_principal'         => $rolPrincipal,
                'debe_cambiar_password' => $debeCambiarPassword,
                'activo'                => true,
                'email_verified_at'     => null,
            ]);

            // h) Asignar roles con Spatie
            $user->assignRole($rolesAAsignar);

            // i) Auditar según el origen (acceso_creado por defecto si es administrativo, o registro_exitoso si es auto_registro)
            $esAutoRegistro = ($datos['origen'] ?? null) === 'auto_registro';
            $accionAuditoria = $esAutoRegistro ? 'registro_exitoso' : 'acceso_creado';
            $descAuditoria = $esAutoRegistro
                ? 'Registro de cuenta para cédula: ' . $persona->numero_documento
                : 'Acceso creado con contraseña temporal para cédula: ' . $persona->numero_documento;

            Auditoria::registrar(
                $accionAuditoria,
                $user,
                null,
                null,
                $descAuditoria
            );

            return [
                'user'              => $user,
                'password_temporal' => $passwordTemporal,
            ];

        } catch (ValidationException $e) {
            // Auditar rechazo con motivo sin incluir contraseñas
            $esAutoRegistro = ($datos['origen'] ?? null) === 'auto_registro';
            $accionAuditoria = $esAutoRegistro ? 'registro_rechazado' : 'creacion_acceso_rechazada';

            Auditoria::registrar(
                $accionAuditoria,
                null,
                null,
                null,
                'Intento de creación de cuenta rechazado para cédula: ' . ($datos['numero_documento'] ?? 'N/A')
                . ' / tipo: ' . ($datos['tipo_documento_id'] ?? 'N/A')
            );

            throw $e;
        }
    }
}
