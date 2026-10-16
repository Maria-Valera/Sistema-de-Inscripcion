<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\Persona;
use App\Models\Docente;
use App\Models\Representante;
use App\Models\Genero;
use App\Models\TipoDocumento;
use App\Models\PrefijoTelefono;
use App\Models\Localidad;
use App\Models\Estado;
use App\Models\Municipio;
use App\Models\Pais;
use App\Models\Ocupacion;
use App\Models\AnioEscolar;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UsuariosPruebaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $entorno = app()->environment();

        // Solo se ejecuta en entorno local
        if ($entorno !== 'local') {
            $this->command->info("[UsuariosPruebaSeeder] Entorno '{$entorno}' detectado. Solo se ejecuta en entorno local. Omitiendo.");
            return;
        }

        $password = config('seguridad.password_desarrollo', '12345678');
        $this->command->info('Creando usuarios de prueba...');

        // Tomar el primer registro existente de cada catálogo
        $genero = Genero::first();
        if (!$genero) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'generos'");
        }
        $generoId = $genero->id;

        $generoFemenino = Genero::where('genero', 'LIKE', 'F%')->orWhere('genero', 'LIKE', 'Mujer%')->first() ?? $genero;
        $generoFemeninoId = $generoFemenino->id;

        $tipoDocumento = TipoDocumento::first();
        if (!$tipoDocumento) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'tipo_documentos'");
        }
        $tipoDocumentoId = $tipoDocumento->id;

        $prefijo = PrefijoTelefono::first();
        if (!$prefijo) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'prefijo_telefonos'");
        }
        $prefijoId = $prefijo->id;

        $localidad = Localidad::first();
        if (!$localidad) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'localidads'");
        }
        $localidadId = $localidad->id;

        $estado = Estado::first();
        if (!$estado) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'estados'");
        }
        $estadoId = $estado->id;

        $municipio = Municipio::first();
        if (!$municipio) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'municipios'");
        }
        $municipioId = $municipio->id;

        $pais = Pais::first();
        if (!$pais) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'pais'");
        }
        $paisId = $pais->id;

        $ocupacion = Ocupacion::first();
        if (!$ocupacion) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'ocupacions'");
        }
        $ocupacionId = $ocupacion->id;

        // Año escolar activo
        $anioEscolar = AnioEscolar::where('status', 'Activo')
            ->orWhere('status', 'Extendido')
            ->first() ?? AnioEscolar::first();

        if (!$anioEscolar) {
            throw new RuntimeException("Falta catálogo requerido en base de datos: 'anio_escolars'");
        }
        $anioEscolarId = $anioEscolar->id;

        // ===== USUARIO REPRESENTANTE =====
        $this->crearUsuarioRepresentante(
            'representante@example.com',
            'Juan',
            'Carlos',
            'Rodríguez',
            'López',
            '99999991',
            $password,
            $generoId,
            $tipoDocumentoId,
            $prefijoId,
            $localidadId,
            $estadoId,
            $municipioId,
            $paisId,
            $ocupacionId
        );

        // ===== USUARIO DOCENTE =====
        $this->crearUsuarioDocente(
            'docente@example.com',
            'María',
            'Elena',
            'García',
            'Pérez',
            '99999992',
            $password,
            $generoId,
            $tipoDocumentoId,
            $prefijoId,
            $localidadId,
            $anioEscolarId
        );

        // ===== USUARIO SUBDIRECTORA =====
        $this->crearUsuarioDocente(
            'subdirectora@example.com',
            'Ana',
            'Beatriz',
            'Martínez',
            'Soto',
            '99999993',
            $password,
            $generoFemeninoId,
            $tipoDocumentoId,
            $prefijoId,
            $localidadId,
            $anioEscolarId,
            'Subdirectora'
        );

        // ===== USUARIO DOCENTE Y REPRESENTANTE (MÚLTIPLES ROLES) =====
        $this->crearUsuarioDocenteRepresentante(
            'docente-representante@example.com',
            'Pedro',
            'José',
            'Hernández',
            'Torres',
            '99999994',
            $password,
            $generoId,
            $tipoDocumentoId,
            $prefijoId,
            $localidadId,
            $estadoId,
            $municipioId,
            $paisId,
            $ocupacionId,
            $anioEscolarId
        );

        $this->command->info('Usuarios de prueba creados exitosamente.');
    }

    /**
     * Crea un usuario con rol Representante
     */
    private function crearUsuarioRepresentante(
        string $email,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
        string $cedula,
        string $password,
        int $generoId,
        int $tipoDocumentoId,
        int $prefijoId,
        int $localidadId,
        int $estadoId,
        int $municipioId,
        int $paisId,
        int $ocupacionId
    ): void {
        // Crear o actualizar Persona
        $persona = Persona::updateOrCreate(
            ['numero_documento' => $cedula],
            [
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'tercer_nombre' => null,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'fecha_nacimiento' => '1985-01-01',
                'direccion' => 'Dirección de prueba',
                'email' => $email,
                'telefono' => '12345678',
                'status' => true,
                'genero_id' => $generoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'prefijo_id' => $prefijoId,
                'localidad_id' => $localidadId,
            ]
        );

        // Crear o actualizar Representante
        Representante::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'estado_id' => $estadoId,
                'municipio_id' => $municipioId,
                'parroquia_id' => $localidadId,
                'pais_id' => $paisId,
                'ocupacion_representante' => $ocupacionId,
                'convivenciaestudiante_representante' => 'Convive',
                'status' => true,
            ]
        );

        // Crear o actualizar User
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => "$primerNombre $primerApellido",
                'password' => Hash::make($password),
                'persona_id' => $persona->id,
                'rol_principal' => 'Representante',
                'debe_cambiar_password' => false,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        if (empty($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        // Asignar rol
        $user->syncRoles(['Representante']);

        $this->command->info("  - Representante: $email (Cédula: $cedula)");
    }

    /**
     * Crea un usuario con rol Docente
     */
    private function crearUsuarioDocente(
        string $email,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
        string $cedula,
        string $password,
        int $generoId,
        int $tipoDocumentoId,
        int $prefijoId,
        int $localidadId,
        int $anioEscolarId,
        string $rolPrincipal = 'Docente'
    ): void {
        // Crear o actualizar Persona
        $persona = Persona::updateOrCreate(
            ['numero_documento' => $cedula],
            [
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'tercer_nombre' => null,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'fecha_nacimiento' => '1985-01-01',
                'direccion' => 'Dirección de prueba',
                'email' => $email,
                'telefono' => '12345678',
                'status' => true,
                'genero_id' => $generoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'prefijo_id' => $prefijoId,
                'localidad_id' => $localidadId,
            ]
        );

        // Crear o actualizar Docente con código determinístico
        Docente::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'codigo' => 'PRUEBA-' . $cedula,
                'dependencia' => 'Liceo General Juan Guillermo Iribarren',
                'anio_escolar_id' => $anioEscolarId,
                'horas_academicas' => 40,
                'status' => true,
            ]
        );

        // Crear o actualizar User
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => "$primerNombre $primerApellido",
                'password' => Hash::make($password),
                'persona_id' => $persona->id,
                'rol_principal' => $rolPrincipal,
                'debe_cambiar_password' => false,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        if (empty($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        // Asignar rol
        $user->syncRoles([$rolPrincipal]);

        $this->command->info("  - $rolPrincipal: $email (Cédula: $cedula)");
    }

    /**
     * Crea un usuario con roles Docente y Representante
     */
    private function crearUsuarioDocenteRepresentante(
        string $email,
        string $primerNombre,
        string $segundoNombre,
        string $primerApellido,
        string $segundoApellido,
        string $cedula,
        string $password,
        int $generoId,
        int $tipoDocumentoId,
        int $prefijoId,
        int $localidadId,
        int $estadoId,
        int $municipioId,
        int $paisId,
        int $ocupacionId,
        int $anioEscolarId
    ): void {
        // Crear o actualizar Persona
        $persona = Persona::updateOrCreate(
            ['numero_documento' => $cedula],
            [
                'primer_nombre' => $primerNombre,
                'segundo_nombre' => $segundoNombre,
                'tercer_nombre' => null,
                'primer_apellido' => $primerApellido,
                'segundo_apellido' => $segundoApellido,
                'fecha_nacimiento' => '1985-01-01',
                'direccion' => 'Dirección de prueba',
                'email' => $email,
                'telefono' => '12345678',
                'status' => true,
                'genero_id' => $generoId,
                'tipo_documento_id' => $tipoDocumentoId,
                'prefijo_id' => $prefijoId,
                'localidad_id' => $localidadId,
            ]
        );

        // Crear o actualizar Docente con código determinístico
        Docente::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'codigo' => 'PRUEBA-' . $cedula,
                'dependencia' => 'Liceo General Juan Guillermo Iribarren',
                'anio_escolar_id' => $anioEscolarId,
                'horas_academicas' => 40,
                'status' => true,
            ]
        );

        // Crear o actualizar Representante
        Representante::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'estado_id' => $estadoId,
                'municipio_id' => $municipioId,
                'parroquia_id' => $localidadId,
                'pais_id' => $paisId,
                'ocupacion_representante' => $ocupacionId,
                'convivenciaestudiante_representante' => 'Convive',
                'status' => true,
            ]
        );

        // Crear o actualizar User
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => "$primerNombre $primerApellido",
                'password' => Hash::make($password),
                'persona_id' => $persona->id,
                'rol_principal' => 'Docente', // Rol principal es Docente
                'debe_cambiar_password' => false,
                'activo' => true,
                'email_verified_at' => now(),
            ]
        );

        if (empty($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);
        }

        // Asignar ambos roles
        $user->syncRoles(['Docente', 'Representante']);

        $this->command->info("  - Docente y Representante: $email (Cédula: $cedula, Rol principal: Docente)");
    }
}
