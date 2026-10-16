<?php

namespace Database\Seeders;

use App\Models\AnioEscolar;
use App\Models\Docente;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;
use App\Models\DetalleDocenteEstudio;
use App\Models\Alumno;
use App\Models\Representante;
use App\Models\RepresentanteLegal;
use App\Models\Inscripcion;
use App\Models\InscripcionNuevoIngreso;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Iniciando seeders...');

        $this->call([
            SeguridadSeeder::class, // Ejecuta PermisosSeeder, RoleSeeder y Administrador
            PaisSeeder::class,
            AnioEscolarSeeder::class,
            GradoSeeder::class,
            aulaSeeder::class,
            AreaFormacionSeeder::class,
            EstadoSeeder::class,
            MunicipioSeeder::class,
            LocalidadSeeder::class,
            EstudiosRealizadoSeeder::class,
            AreaEstudioRealizadoSeeder::class,
            GradoAreaFormacionSeeder::class,
            EtniaIndigenaSeeder::class,
            DiscapacidadSeeder::class,
            OcupacionSeeder::class,
            ExpresionLiterariaSeeder::class,
            BancoSeeder::class,
            PrefijoTelefonoSeeder::class,
            InstitucionProcedenciaSeeder::class,
            GeneroSeeder::class,
            LateralidadSeeder::class,
            OrdenNacimientoSeeder::class,
            TipoDocumentoSeeder::class,
            IndiceEdadSeeder::class,
            IndicePesoSeeder::class,
            IndiceEstaturaSeeder::class,
            SeccionSeeder::class,
            PersonaSeeder::class,
            TallaSeeder::class,
            AlumnoSeeder::class,
            DiscapacidadEstudianteSeeder::class,
            DocenteSeeder::class,
            DetalleDocenteEstudioSeeder::class,
            RepresentanteSeeder::class,
            RepresentanteLegalSeeder::class,
            InscripcionSeeder::class,
            InscripcionNuevoIngresoSeeder::class,
            DiasSemanaSeeder::class,
            BloqueHorarioSeeder::class,
            DocenteAreaGradoSeeder::class,
            SeccionAulaSeeder::class,


            /*InscripcionProsecucionSeeder::class,
            ProsecucionAreaSeeder::class,  */
        ]);

        /* 
        |--------------------------------------------------------------------------
        | GENERACIÓN DINÁMICA DE USUARIOS PARA REPRESENTANTES
        |--------------------------------------------------------------------------
        | Recorremos todos los representantes que fueron registrados por los seeders.
        | Para cada uno, creamos una cuenta en la tabla 'users' utilizando su email
        | y le asignamos el rol 'Representante' para que puedan loguearse.
        | Solo se ejecuta en entorno local.
        */
        $entorno = app()->environment();
        if ($entorno === 'local') {
            $representantes = \App\Models\Representante::with('persona')->get();
            $passwordDev = config('seguridad.password_desarrollo', '12345678');
            
            foreach ($representantes as $rep) {
                // Verificamos que el representante tenga datos personales y una dirección de correo válida
                if ($rep->persona && $rep->persona->email) {
                    $user = User::firstOrCreate(
                        ['email' => $rep->persona->email],
                        [
                            'name' => $rep->persona->primer_nombre . ' ' . $rep->persona->primer_apellido,
                            'password' => Hash::make($passwordDev),
                            'rol_principal' => 'Representante',
                            'activo' => true,
                            'debe_cambiar_password' => false,
                            'email_verified_at' => now(),
                        ]
                    );
                    
                    if (!$user->rol_principal || $user->rol_principal !== 'Representante') {
                        $user->update(['rol_principal' => 'Representante']);
                    }
                    if (!$user->activo) {
                        $user->update(['activo' => true]);
                    }
                    if ($user->debe_cambiar_password) {
                        $user->update(['debe_cambiar_password' => false]);
                    }
                    if (empty($user->email_verified_at)) {
                        $user->update(['email_verified_at' => now()]);
                    }
                    
                    // Asignamos el rol 'Representante' usando syncRoles
                    $user->syncRoles(['Representante']);
                }
            }
        } else {
            $this->command->info("[DatabaseSeeder] Entorno '{$entorno}' detectado. Saltando creación de usuarios de prueba.");
        }

        $this->command->info('¡Base de datos poblada con éxito!');
    }
}
