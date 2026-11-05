<?php

namespace App\Actions\Usuarios;

use App\Mail\AccesoUsuarioMailable;
use App\Models\Auditoria;
use App\Models\Docente;
use App\Models\Genero;
use App\Models\Persona;
use App\Models\Representante;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrearUsuarioParaPersonaAction
{
    /**
     * Crea un usuario y, si es necesario, la persona base con status=1.
     * Genera una contraseña temporal segura si no se proporciona, o asigna la indicada.
     * Dependiendo del rol asignado y la existencia previa en docentes/representantes,
     * retorna señales de redirección para completar la ficha respectiva.
     *
     * @param array $datos [
     *     'tipo_documento_id' => int|string,
     *     'numero_documento'  => string,
     *     'primer_nombre'     => ?string (opcional/requerido si persona nueva),
     *     'primer_apellido'   => ?string (opcional/requerido si persona nueva),
     *     'email'             => string,
     *     'password'          => ?string (opcional),
     *     'rol'               => ?string (Admin, Subdirectora, Docente, Representante),
     *     'rol_principal'     => ?string (opcional),
     *     'roles'             => ?array  (opcional),
     *     'origen'            => ?string (opcional: 'auto_registro' o 'admin')
     * ]
     * @return array [
     *     'user'                         => User,
     *     'password_temporal'            => ?string,
     *     'persona'                      => Persona,
     *     'redirigir_a_docente'          => bool,
     *     'redirigir_a_representante'    => bool,
     * ]
     * @throws ValidationException
     */
    public function ejecutar(array $datos): array
    {
        $mensajeGenerico = 'No pudimos validar tus datos. Verifica la cédula o contacta a la institución.';
        $esAutoRegistro = ($datos['origen'] ?? null) === 'auto_registro';

        try {
            // A) Buscar la Persona por tipo_documento_id + numero_documento
            $persona = Persona::where('tipo_documento_id', $datos['tipo_documento_id'])
                ->where('numero_documento', $datos['numero_documento'])
                ->first();

            // Si es auto-registro y no existe o está inactiva -> error
            if ($esAutoRegistro) {
                if (!$persona || !$persona->status) {
                    throw ValidationException::withMessages([
                        'numero_documento' => [$mensajeGenerico],
                    ]);
                }
            } else {
                // Flujo administrativo: Si no existe, crearla con los datos provistos
                if (!$persona) {
                    $generoDefecto = Genero::firstOrCreate(
                        ['genero' => 'No especificado'],
                        ['status' => 1]
                    );

                    $persona = Persona::create([
                        'tipo_documento_id' => $datos['tipo_documento_id'],
                        'numero_documento'  => $datos['numero_documento'],
                        'primer_nombre'     => $datos['primer_nombre'] ?? 'Usuario',
                        'primer_apellido'   => $datos['primer_apellido'] ?? 'Sistema',
                        'email'             => $datos['email'],
                        'fecha_nacimiento'  => '2000-01-01',
                        'genero_id'         => $generoDefecto->id,
                        'status'            => 1,
                    ]);
                }
            }

            // B) Verificar si la Persona ya tiene un User asociado
            if (User::where('persona_id', $persona->id)->exists()) {
                $errorMsg = $esAutoRegistro
                    ? $mensajeGenerico
                    : 'La persona con esta cédula ya tiene un usuario registrado en el sistema.';
                throw ValidationException::withMessages([
                    'numero_documento' => [$errorMsg],
                ]);
            }

            // Determinar si ya tiene registro en docentes o representantes
            $tieneDocente = Docente::where('persona_id', $persona->id)
                ->where('status', 1)
                ->exists();

            $tieneRepresentante = Representante::where('persona_id', $persona->id)
                ->whereNull('deleted_at')
                ->where('status', 1)
                ->exists();

            // Si es auto-registro, debe ser Docente o Representante precargado
            if ($esAutoRegistro && !$tieneDocente && !$tieneRepresentante) {
                throw ValidationException::withMessages([
                    'numero_documento' => [$mensajeGenerico],
                ]);
            }

            // C) Determinar roles y señales de redirección
            $rolesAAsignar = [];
            $rolPrincipal = $datos['rol_principal'] ?? null;
            $redirigirDocente = false;
            $redirigirRepresentante = false;

            if (!empty($datos['roles'])) {
                $rolesAAsignar = (array) $datos['roles'];
            } elseif (!empty($datos['rol'])) {
                $rolesAAsignar = [$datos['rol']];
            } else {
                // Auto-registro o sin rol explícito
                if ($tieneDocente && $tieneRepresentante) {
                    $rolesAAsignar = ['Docente', 'Representante'];
                } elseif ($tieneDocente) {
                    $rolesAAsignar = ['Docente'];
                } else {
                    $rolesAAsignar = ['Representante'];
                }
            }

            // Validar / Asignar rol principal asegurando que esté incluido dentro de los roles asignados
            if (empty($rolPrincipal) || !in_array($rolPrincipal, $rolesAAsignar)) {
                $rolPrincipal = $rolesAAsignar[0];
            }

            // Comprobar si requiere ficha adicional para Docente o Representante
            if (in_array('Docente', $rolesAAsignar) && !$tieneDocente) {
                $redirigirDocente = true;
            }
            if (in_array('Representante', $rolesAAsignar) && !$tieneRepresentante) {
                $redirigirRepresentante = true;
            }

            // D) Definir contraseña (elegida por usuario o temporal segura)
            $passwordTemporal = null;
            $passwordPlana = $datos['password'] ?? null;

            if (empty($passwordPlana)) {
                // Generar contraseña temporal segura de 12 caracteres
                $passwordTemporal = Str::password(12, true, true, false, false);
                $passwordPlana = $passwordTemporal;
                $debeCambiarPassword = true;
            } else {
                // Si el usuario proporcionó su propia contraseña (ej: auto-registro)
                $debeCambiarPassword = false;
            }

            // E) Crear el usuario
            $nombreUsuario = trim(($persona->primer_nombre ?? '') . ' ' . ($persona->primer_apellido ?? ''));
            if (empty($nombreUsuario)) {
                $nombreUsuario = $datos['email'];
            }

            $user = User::create([
                'name'                  => $nombreUsuario,
                'email'                 => $datos['email'],
                'password'              => Hash::make($passwordPlana),
                'persona_id'            => $persona->id,
                'rol_principal'         => $rolPrincipal,
                'debe_cambiar_password' => $debeCambiarPassword,
                'activo'                => true,
                'email_verified_at'     => null,
            ]);

            // F) Asignar roles con Spatie usando syncRoles
            $user->syncRoles($rolesAAsignar);

            // G) Enviar correo con credenciales temporales si fue generado administrativamente
            if ($passwordTemporal && !$esAutoRegistro) {
                try {
                    Mail::to($user->email)->send(new AccesoUsuarioMailable($user, $passwordTemporal));
                } catch (\Throwable $e) {
                    // Prevenir caída si hay problemas de red/smtp
                }
            }

            // H) Auditar la acción
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
                'user'                      => $user,
                'password_temporal'         => $passwordTemporal,
                'persona'                   => $persona,
                'redirigir_a_docente'       => $redirigirDocente,
                'redirigir_a_representante' => $redirigirRepresentante,
            ];

        } catch (ValidationException $e) {
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
