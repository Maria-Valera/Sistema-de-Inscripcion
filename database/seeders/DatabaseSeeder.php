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
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('Iniciando seeders...');
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $this->call([
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
            RoleSeeder::class,
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


            /*InscripcionProsecucionSeeder::class,
            ProsecucionAreaSeeder::class,  */
            /*  DocenteAreaGrado::class, */
        ]);

        // Asignar rol al usuario Admin ya existente
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('Admin');
        }

        /* 
        |--------------------------------------------------------------------------
        | GENERACIÓN DINÁMICA DE USUARIOS PARA REPRESENTANTES
        |--------------------------------------------------------------------------
        | Recorremos todos los representantes que fueron registrados por los seeders.
        | Para cada uno, creamos una cuenta en la tabla 'users' utilizando su email
        | y le asignamos el rol 'Representante' para que puedan loguearse.
        */
        $representantes = \App\Models\Representante::with('persona')->get();
        
        foreach ($representantes as $rep) {
            // Verificamos que el representante tenga datos personales y una dirección de correo válida
            if ($rep->persona && $rep->persona->email) {
                // Buscamos o creamos el usuario correspondiente con contraseña por defecto '12345678'
                $user = User::firstOrCreate(
                    ['email' => $rep->persona->email],
                    [
                        'name' => $rep->persona->primer_nombre . ' ' . $rep->persona->primer_apellido,
                        'password' => bcrypt('12345678'), // Encriptación bcrypt de la contraseña por defecto
                    ]
                );
                
                // Asignamos el rol 'Representante' usando Spatie Laravel-Permission
                $user->assignRole('Representante');
            }
        }

        $this->command->info('¡Base de datos poblada con éxito!');
    }
}
