<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;
use App\Models\Docente;
use App\Models\Representante;
use Illuminate\Support\Facades\Hash;

class UsuariosPruebaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo se ejecuta en entorno local
        if (!app()->environment('local')) {
            $this->command->info('UsuariosPruebaSeeder: Solo se ejecuta en entorno local. Omitiendo.');
            return;
        }

        $password = '12345678';
        $this->command->info('Creando usuarios de prueba...');

        // IDs de catálogos (asumiendo que ya existen en la base de datos)
        $generoId = 1; // Masculino por defecto
        $tipoDocumentoId = 1; // Cédula
        $prefijoId = 1; // 0414
        $localidadId = 1; // Parroquia
        $estadoId = 1; // Estado
        $municipioId = 1; // Municipio
        $paisId = 242; // Venezuela
        $ocupacionId = 1; // Ocupación
        $anioEscolarId = 1; // Año escolar activo

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
            2, // Femenino
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
            ]
        );

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

        // Crear o actualizar Docente
        Docente::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'codigo' => 'PRUEBA' . rand(1000, 9999),
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
            ]
        );

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

        // Crear o actualizar Docente
        Docente::updateOrCreate(
            ['persona_id' => $persona->id],
            [
                'codigo' => 'PRUEBA' . rand(1000, 9999),
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
            ]
        );

        // Asignar ambos roles
        $user->syncRoles(['Docente', 'Representante']);

        $this->command->info("  - Docente y Representante: $email (Cédula: $cedula, Rol principal: Docente)");
    }
}
