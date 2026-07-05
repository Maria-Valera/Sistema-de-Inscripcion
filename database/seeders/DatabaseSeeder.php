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

            
            /*InscripcionProsecucionSeeder::class,
            ProsecucionAreaSeeder::class,  */
            /*  DocenteAreaGrado::class, */
        ]);

        // Asignar rol al usuario Admin ya existente
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('Admin');
        }

        // Crear y asignar rol de Representante
        $repUser = User::firstOrCreate(
            ['email' => 'representante@example.com'],
            [
                'name' => 'Representante de Prueba',
                'password' => bcrypt('12345678'),
            ]
        );
        $repUser->assignRole('Representante');

        $this->command->info('¡Base de datos poblada con éxito!');
    }
}
